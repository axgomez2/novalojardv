<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentService
{
    protected string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models';
    protected string $model;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Gera/traduz a descrição de um disco em PT-BR a partir do contexto fornecido.
     */
    public function generateVinylDescription(array $context): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Gemini API key não configurada (GEMINI_API_KEY).');
        }

        $title = $context['title'] ?? '';
        $artists = $context['artists'] ?? '';
        $year = $context['year'] ?? '';
        $country = $context['country'] ?? '';
        $label = $context['label'] ?? '';
        $genres = $context['genres'] ?? '';
        $styles = $context['styles'] ?? '';
        $notes = trim((string) ($context['notes'] ?? ''));

        $prompt = <<<PROMPT
Você é um especialista em música e curador de loja de discos de vinil brasileira.
Sua tarefa é escrever uma descrição comercial em PORTUGUÊS DO BRASIL para um disco de vinil que será vendido em uma loja online.

Dados do disco:
- Título: {$title}
- Artista(s): {$artists}
- Ano: {$year}
- País: {$country}
- Gravadora: {$label}
- Gêneros: {$genres}
- Estilos: {$styles}

Notas originais do Discogs (em inglês, podem estar vazias):
{$notes}

Instruções:
- Escreva entre 3 e 6 frases (máx. 600 caracteres).
- Tom: editorial, envolvente, voltado a colecionadores e DJs.
- Se houver notas em inglês, traduza e enriqueça com contexto relevante (cena, importância, faixas marcantes), sem inventar fatos específicos não verificáveis.
- Se NÃO houver notas, escreva uma descrição com base no contexto (artista, gênero, época, gravadora).
- NÃO use bullet points, emojis ou markdown. Texto corrido.
- NÃO repita o título no início da frase.
- Retorne APENAS o texto da descrição, sem aspas e sem prefixos como "Descrição:".
PROMPT;

        $payload = [
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 400,
            ],
        ];

        $attempts = 0;
        $maxAttempts = 3;

        while (true) {
            $attempts++;
            try {
                $response = Http::timeout(60)->connectTimeout(15)
                    ->post("{$this->endpoint}/{$this->model}:generateContent?key={$this->apiKey}", $payload);

                if ($response->successful()) {
                    $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');
                    return trim((string) $text);
                }

                $status = $response->status();
                Log::warning('Gemini API error', ['status' => $status, 'body' => $response->body(), 'attempt' => $attempts]);

                // Retry em 429 (rate limit) e 503 (overloaded)
                if (in_array($status, [429, 503]) && $attempts < $maxAttempts) {
                    sleep($attempts * 2); // backoff: 2s, 4s
                    continue;
                }

                if ($status === 429) {
                    throw new \RuntimeException('Limite de requisições da IA atingido. Aguarde 1 minuto e tente novamente, ou troque o modelo no .env (GEMINI_MODEL=gemini-1.5-flash).');
                }

                if ($status === 401 || $status === 403) {
                    throw new \RuntimeException('Chave do Gemini inválida ou sem permissão. Verifique GEMINI_API_KEY no .env.');
                }

                throw new \RuntimeException("Falha ao gerar descrição (HTTP {$status}).");
            } catch (\RuntimeException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('AiContentService::generateVinylDescription', ['error' => $e->getMessage()]);
                throw new \RuntimeException('Erro ao chamar IA: ' . $e->getMessage());
            }
        }
    }
}
