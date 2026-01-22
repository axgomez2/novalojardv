<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Login com email e senha
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = ClientUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Sua conta está desativada. Entre em contato com o suporte.',
            ], 403);
        }

        // Revogar tokens antigos
        $user->tokens()->delete();

        // Criar novo token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Verificar se email não está verificado
        $requiresVerification = !$user->hasVerifiedEmail();

        return response()->json([
            'message' => $requiresVerification 
                ? 'Login realizado! Verifique seu e-mail para acessar todas as funcionalidades.' 
                : 'Login realizado com sucesso!',
            'token' => $token,
            'user' => $this->formatUser($user),
            'requires_verification' => $requiresVerification,
        ]);
    }

    /**
     * Registro de novo usuário
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:client_users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:client_users,cpf',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = ClientUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone ? preg_replace('/\D/', '', $request->phone) : null,
            'cpf' => $request->cpf ? preg_replace('/\D/', '', $request->cpf) : null,
            'role' => ClientUser::ROLE_CLIENT,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        // Enviar código de verificação
        $this->sendVerificationCode($user);

        return response()->json([
            'message' => 'Cadastro realizado! Enviamos um código de verificação para seu e-mail.',
            'token' => $token,
            'user' => $this->formatUser($user),
            'requires_verification' => true,
        ], 201);
    }

    /**
     * Enviar código de verificação por email
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Seu e-mail já está verificado.',
            ]);
        }

        $this->sendVerificationCode($user);

        return response()->json([
            'message' => 'Código de verificação enviado para ' . $user->email,
        ]);
    }

    /**
     * Verificar código de email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'O código é obrigatório.',
            'code.size' => 'O código deve ter 6 dígitos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Seu e-mail já está verificado.',
                'user' => $this->formatUser($user),
            ]);
        }

        $cacheKey = 'email_verification_' . $user->id;
        $storedCode = Cache::get($cacheKey);

        if (!$storedCode || $storedCode !== $request->code) {
            return response()->json([
                'message' => 'Código inválido ou expirado.',
            ], 422);
        }

        $user->markEmailAsVerified();
        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'E-mail verificado com sucesso!',
            'user' => $this->formatUser($user->fresh()),
        ]);
    }

    /**
     * Gerar e enviar código de verificação
     */
    private function sendVerificationCode(ClientUser $user): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'email_verification_' . $user->id;
        
        // Armazenar código por 30 minutos
        Cache::put($cacheKey, $code, now()->addMinutes(30));

        // Enviar email
        Mail::send('emails.verify-email', [
            'user' => $user,
            'code' => $code,
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('Verifique seu e-mail - Vinil Store');
        });
    }

    /**
     * Logout - revogar token atual
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso!',
        ]);
    }

    /**
     * Obter dados do usuário autenticado
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * Atualizar perfil do usuário
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|min:3|max:255',
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:client_users,cpf,' . $user->id,
            'birth_date' => 'nullable|date|before:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only(['name', 'phone', 'cpf', 'birth_date']);
        
        if (isset($data['phone'])) {
            $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        }
        if (isset($data['cpf'])) {
            $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'user' => $this->formatUser($user->fresh()),
        ]);
    }

    /**
     * Alterar senha
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'A senha atual é obrigatória.',
            'password.required' => 'A nova senha é obrigatória.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'A senha atual está incorreta.',
            ], 422);
        }

        $user->update(['password' => $request->password]);

        // Revogar todos os tokens exceto o atual
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'Senha alterada com sucesso!',
        ]);
    }

    /**
     * Redirecionar para Google OAuth
     */
    public function redirectToGoogle(): JsonResponse
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'url' => $url,
        ]);
    }

    /**
     * Callback do Google OAuth
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // Buscar usuário existente por google_id ou email
            $user = ClientUser::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // Atualizar google_id se necessário
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }

                if (!$user->is_active) {
                    return response()->json([
                        'message' => 'Sua conta está desativada. Entre em contato com o suporte.',
                    ], 403);
                }
            } else {
                // Criar novo usuário
                $user = ClientUser::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'role' => ClientUser::ROLE_CLIENT,
                    'is_active' => true,
                ]);
            }

            // Revogar tokens antigos
            $user->tokens()->delete();

            $token = $user->createToken('auth-token')->plainTextToken;

            // Verificar se email não está verificado (usuário existente que não verificou)
            $requiresVerification = !$user->hasVerifiedEmail();

            // Se é usuário existente sem verificação, enviar código
            if ($requiresVerification) {
                $this->sendVerificationCode($user);
            }

            return response()->json([
                'message' => $requiresVerification 
                    ? 'Login realizado! Verifique seu e-mail para acessar todas as funcionalidades.'
                    : 'Login com Google realizado com sucesso!',
                'token' => $token,
                'user' => $this->formatUser($user),
                'requires_verification' => $requiresVerification,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao autenticar com Google.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Formatar dados do usuário para resposta
     */
    private function formatUser(ClientUser $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->formatted_phone,
            'cpf' => $user->formatted_cpf,
            'birth_date' => $user->birth_date?->format('Y-m-d'),
            'has_google' => !is_null($user->google_id),
            'email_verified' => $user->hasVerifiedEmail(),
            'created_at' => $user->created_at->toISOString(),
        ];
    }
}
