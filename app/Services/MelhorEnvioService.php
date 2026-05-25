<?php

namespace App\Services;

use App\Models\ShippingCarrier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MelhorEnvioService
{
    protected string $baseUrl;
    protected ?string $token;
    protected array $senderAddress;
    protected bool $sandbox;

    public function __construct()
    {
        $settings = $this->loadSettings();

        $this->sandbox = $settings['sandbox_mode'];
        $this->baseUrl = $this->sandbox
            ? 'https://sandbox.melhorenvio.com.br/api/v2'
            : 'https://melhorenvio.com.br/api/v2';

        $this->token = $settings['melhor_envio_token'];

        $this->senderAddress = [
            'postal_code' => $settings['sender_postal_code'],
        ];
    }

    /**
     * Carrega configurações priorizando a tabela `shipping_settings` (admin)
     * e caindo para `.env` / config quando o registro não existir.
     */
    protected function loadSettings(): array
    {
        $rows = [];
        try {
            $rows = DB::table('shipping_settings')->pluck('value', 'key')->toArray();
        } catch (\Throwable $e) {
            // tabela pode não existir em ambientes sem migration; usa só config
        }

        $token = $rows['melhor_envio_token'] ?? null;
        if (!$token) {
            $token = config('services.melhor_envio.token');
        }

        $senderCep = $rows['sender_postal_code'] ?? null;
        if (!$senderCep) {
            $senderCep = config('services.melhor_envio.sender_postal_code', '01310100');
        }

        if (array_key_exists('sandbox_mode', $rows)) {
            $sandbox = filter_var($rows['sandbox_mode'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $sandbox = (bool) config('services.melhor_envio.sandbox', true);
        }

        return [
            'melhor_envio_token' => $token,
            'sender_postal_code' => $senderCep,
            'sandbox_mode' => $sandbox,
        ];
    }

    /**
     * Calcular frete para um CEP de destino
     */
    public function calculateShipping(string $destinationPostalCode, array $products): array
    {
        if (!$this->token) {
            return ['error' => 'Token do Melhor Envio não configurado'];
        }

        $cacheKey = 'shipping_' . md5($destinationPostalCode . json_encode($products));
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($destinationPostalCode, $products) {
            try {
                $response = Http::withToken($this->token)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'Loja de Discos (contato@lojadediscos.com.br)',
                    ])
                    ->post($this->baseUrl . '/me/shipment/calculate', [
                        'from' => [
                            'postal_code' => preg_replace('/\D/', '', $this->senderAddress['postal_code']),
                        ],
                        'to' => [
                            'postal_code' => preg_replace('/\D/', '', $destinationPostalCode),
                        ],
                        'products' => $products,
                        'options' => [
                            'receipt' => false,
                            'own_hand' => false,
                        ],
                        'services' => $this->getActiveServiceIds(),
                    ]);

                if ($response->failed()) {
                    \Log::error('Melhor Envio API Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'url' => $this->baseUrl . '/me/shipment/calculate',
                    ]);
                    
                    // Se for erro de autenticação, dar mensagem mais clara
                    if ($response->status() === 401) {
                        return ['error' => 'Token do Melhor Envio inválido ou expirado. Gere um novo token no painel sandbox.'];
                    }
                    
                    return ['error' => 'Erro ao calcular frete: ' . $response->body()];
                }

                $quotes = $response->json();
                
                return $this->processQuotes($quotes);
            } catch (\Exception $e) {
                return ['error' => 'Erro ao conectar com Melhor Envio: ' . $e->getMessage()];
            }
        });
    }

    /**
     * Processar cotações e aplicar configurações de transportadoras
     */
    protected function processQuotes(array $quotes): array
    {
        $carriers = ShippingCarrier::active()->get()->keyBy('melhor_envio_id');
        $hasCarriers = $carriers->isNotEmpty();
        $processed = [];

        foreach ($quotes as $quote) {
            if (isset($quote['error'])) {
                continue;
            }

            $carrierId = (string) $quote['id'];
            $carrier = $carriers->get($carrierId);

            // Se tem transportadoras cadastradas, só mostrar as ativas
            // Se não tem nenhuma cadastrada, mostrar todas (modo fallback)
            if ($hasCarriers && !$carrier) {
                continue;
            }

            $baseCost = (float) $quote['price'];
            $baseDays = (int) $quote['delivery_time'];

            // Aplicar ajustes se tiver transportadora configurada
            $finalCost = $carrier ? $carrier->calculateFinalCost($baseCost) : $baseCost;
            $finalDays = $carrier ? $carrier->calculateFinalDays($baseDays) : $baseDays;

            $processed[] = [
                'id' => $carrierId,
                'name' => $quote['name'],
                'company' => $quote['company']['name'] ?? ($carrier->company ?? 'Transportadora'),
                'logo' => $carrier->logo ?? $quote['company']['picture'] ?? null,
                'price' => $finalCost,
                'original_price' => $baseCost,
                'delivery_time' => $finalDays,
                'original_delivery_time' => $baseDays,
                'formatted_price' => 'R$ ' . number_format($finalCost, 2, ',', '.'),
            ];
        }

        usort($processed, fn($a, $b) => $a['price'] <=> $b['price']);

        return ['quotes' => $processed];
    }

    /**
     * Obter IDs dos serviços ativos
     */
    protected function getActiveServiceIds(): string
    {
        $carriers = ShippingCarrier::active()->pluck('melhor_envio_id')->toArray();
        
        if (empty($carriers)) {
            return '1,2,3,4,17';
        }

        return implode(',', $carriers);
    }

    /**
     * Listar todas as transportadoras disponíveis no Melhor Envio
     */
    public function listAvailableCarriers(): array
    {
        if (!$this->token) {
            return ['error' => 'Token do Melhor Envio não configurado'];
        }

        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Loja de Discos (contato@lojadediscos.com.br)',
                ])
                ->get($this->baseUrl . '/me/shipment/services');

            if ($response->failed()) {
                return ['error' => 'Erro ao listar transportadoras'];
            }

            return $response->json();
        } catch (\Exception $e) {
            return ['error' => 'Erro ao conectar com Melhor Envio: ' . $e->getMessage()];
        }
    }

    /**
     * Criar etiqueta de envio
     */
    public function createShipmentLabel(array $orderData): array
    {
        if (!$this->token) {
            return ['error' => 'Token do Melhor Envio não configurado'];
        }

        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Loja de Discos (contato@lojadediscos.com.br)',
                ])
                ->post($this->baseUrl . '/me/cart', $orderData);

            if ($response->failed()) {
                return ['error' => 'Erro ao criar etiqueta: ' . $response->body()];
            }

            return $response->json();
        } catch (\Exception $e) {
            return ['error' => 'Erro ao conectar com Melhor Envio: ' . $e->getMessage()];
        }
    }

    /**
     * Calcular dimensões e peso para produtos de vinil
     */
    public static function calculateProductDimensions(array $items): array
    {
        $products = [];

        foreach ($items as $item) {
            $format = $item['format'] ?? 'LP';
            $quantity = $item['quantity'] ?? 1;

            $dimensions = match($format) {
                'LP', '12"' => ['width' => 32, 'height' => 32, 'length' => 1, 'weight' => 0.3],
                '10"' => ['width' => 27, 'height' => 27, 'length' => 1, 'weight' => 0.25],
                '7"' => ['width' => 19, 'height' => 19, 'length' => 0.5, 'weight' => 0.1],
                default => ['width' => 32, 'height' => 32, 'length' => 1, 'weight' => 0.3],
            };

            for ($i = 0; $i < $quantity; $i++) {
                $products[] = [
                    'id' => $item['id'] ?? uniqid(),
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height'],
                    'length' => $dimensions['length'],
                    'weight' => $dimensions['weight'],
                    'insurance_value' => $item['price'] ?? 50,
                    'quantity' => 1,
                ];
            }
        }

        return $products;
    }
}
