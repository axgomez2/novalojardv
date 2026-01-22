<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientAddressController extends Controller
{
    /**
     * Listar todos os endereços do usuário autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = ClientAddress::where('client_user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($address) => $this->formatAddress($address));

        return response()->json([
            'data' => $addresses,
            'count' => $addresses->count()
        ]);
    }

    /**
     * Exibir um endereço específico
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $address = ClientAddress::where('client_user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => $this->formatAddress($address)
        ]);
    }

    /**
     * Criar novo endereço
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'nullable|string|max:255',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'zip_code' => 'required|string|max:9',
            'reference' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        $validated['client_user_id'] = $request->user()->id;
        
        // Normalizar CEP (remover caracteres não numéricos)
        $validated['zip_code'] = preg_replace('/\D/', '', $validated['zip_code']);

        $address = ClientAddress::create($validated);

        // Se marcado como padrão, atualizar outros endereços
        if ($address->is_default) {
            $address->setAsDefault();
        }

        return response()->json([
            'message' => 'Endereço cadastrado com sucesso',
            'data' => $this->formatAddress($address)
        ], 201);
    }

    /**
     * Atualizar endereço existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $address = ClientAddress::where('client_user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'nullable|string|max:255',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'zip_code' => 'required|string|max:9',
            'reference' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        // Normalizar CEP
        $validated['zip_code'] = preg_replace('/\D/', '', $validated['zip_code']);

        $address->update($validated);

        // Se marcado como padrão, atualizar outros endereços
        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        return response()->json([
            'message' => 'Endereço atualizado com sucesso',
            'data' => $this->formatAddress($address->fresh())
        ]);
    }

    /**
     * Excluir endereço
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $address = ClientAddress::where('client_user_id', $request->user()->id)
            ->findOrFail($id);

        $wasDefault = $address->is_default;
        $address->delete();

        // Se era o endereço padrão, definir outro como padrão
        if ($wasDefault) {
            $newDefault = ClientAddress::where('client_user_id', $request->user()->id)
                ->orderByDesc('created_at')
                ->first();
            
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'message' => 'Endereço excluído com sucesso'
        ]);
    }

    /**
     * Definir endereço como padrão
     */
    public function setDefault(Request $request, int $id): JsonResponse
    {
        $address = ClientAddress::where('client_user_id', $request->user()->id)
            ->findOrFail($id);

        $address->setAsDefault();

        return response()->json([
            'message' => 'Endereço definido como padrão',
            'data' => $this->formatAddress($address->fresh())
        ]);
    }

    /**
     * Buscar endereço por CEP (via API externa)
     */
    public function searchByCep(Request $request): JsonResponse
    {
        $request->validate([
            'cep' => 'required|string|min:8|max:9'
        ]);

        $cep = preg_replace('/\D/', '', $request->cep);

        if (strlen($cep) !== 8) {
            return response()->json([
                'message' => 'CEP inválido'
            ], 422);
        }

        try {
            $response = file_get_contents("https://viacep.com.br/ws/{$cep}/json/");
            $data = json_decode($response, true);

            if (isset($data['erro']) && $data['erro']) {
                return response()->json([
                    'message' => 'CEP não encontrado'
                ], 404);
            }

            return response()->json([
                'data' => [
                    'zip_code' => $data['cep'] ?? '',
                    'street' => $data['logradouro'] ?? '',
                    'complement' => $data['complemento'] ?? '',
                    'neighborhood' => $data['bairro'] ?? '',
                    'city' => $data['localidade'] ?? '',
                    'state' => $data['uf'] ?? '',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar CEP. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Formatar endereço para resposta da API
     */
    private function formatAddress(ClientAddress $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'street' => $address->street,
            'number' => $address->number,
            'complement' => $address->complement,
            'neighborhood' => $address->neighborhood,
            'city' => $address->city,
            'state' => $address->state,
            'zip_code' => $address->zip_code,
            'formatted_zip_code' => $address->formatted_zip_code,
            'reference' => $address->reference,
            'is_default' => $address->is_default,
            'full_address' => $address->full_address,
            'short_address' => $address->short_address,
            'created_at' => $address->created_at->toISOString(),
            'updated_at' => $address->updated_at->toISOString(),
        ];
    }
}
