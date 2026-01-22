<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Obter valor de uma configuração
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("site_setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            'image' => $setting->value ? Storage::url($setting->value) : $default,
            default => $setting->value ?? $default,
        };
    }

    /**
     * Definir valor de uma configuração
     */
    public static function set(string $key, $value, array $attributes = []): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            array_merge(['value' => $value], $attributes)
        );

        Cache::forget("site_setting_{$key}");
        Cache::forget('site_settings_all');

        return $setting;
    }

    /**
     * Obter todas as configurações de um grupo
     */
    public static function getGroup(string $group): array
    {
        $settings = Cache::remember("site_settings_group_{$group}", 3600, function () use ($group) {
            return static::where('group', $group)->get();
        });

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                'image' => $setting->value ? Storage::url($setting->value) : null,
                default => $setting->value,
            };
        }

        return $result;
    }

    /**
     * Obter todas as configurações para o frontend
     */
    public static function getAllForFrontend(): array
    {
        return Cache::remember('site_settings_frontend', 3600, function () {
            $settings = static::all();
            
            $result = [];
            foreach ($settings as $setting) {
                $value = match ($setting->type) {
                    'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                    'json' => json_decode($setting->value, true),
                    'image' => $setting->value ? Storage::url($setting->value) : null,
                    default => $setting->value,
                };
                
                $result[$setting->key] = $value;
            }

            return $result;
        });
    }

    /**
     * Limpar cache de configurações
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("site_setting_{$setting->key}");
        }
        Cache::forget('site_settings_all');
        Cache::forget('site_settings_frontend');
        
        $groups = static::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("site_settings_group_{$group}");
        }
    }

    /**
     * Inicializar configurações padrão
     */
    public static function initDefaults(): void
    {
        $defaults = [
            // Logo e Identidade
            ['key' => 'site_name', 'value' => 'Loja de Discos', 'type' => 'text', 'group' => 'general', 'label' => 'Nome do Site'],
            ['key' => 'site_tagline', 'value' => 'Sua loja de vinis', 'type' => 'text', 'group' => 'general', 'label' => 'Slogan'],
            ['key' => 'logo', 'value' => null, 'type' => 'image', 'group' => 'logo', 'label' => 'Logo Principal'],
            ['key' => 'logo_white', 'value' => null, 'type' => 'image', 'group' => 'logo', 'label' => 'Logo Branco (para fundos escuros)'],
            ['key' => 'favicon_source', 'value' => null, 'type' => 'image', 'group' => 'logo', 'label' => 'Imagem para Favicon'],
            
            // SEO
            ['key' => 'seo_title', 'value' => 'Loja de Discos - Vinis Novos e Usados', 'type' => 'text', 'group' => 'seo', 'label' => 'Título SEO'],
            ['key' => 'seo_description', 'value' => 'Encontre os melhores discos de vinil, novos e usados. Eletrônica, House, Techno, Disco e muito mais.', 'type' => 'textarea', 'group' => 'seo', 'label' => 'Descrição SEO'],
            ['key' => 'seo_keywords', 'value' => 'discos, vinil, vinyl, loja de discos, eletronica, house, techno, disco, dj', 'type' => 'textarea', 'group' => 'seo', 'label' => 'Palavras-chave'],
            ['key' => 'seo_og_image', 'value' => null, 'type' => 'image', 'group' => 'seo', 'label' => 'Imagem Open Graph'],
            ['key' => 'google_analytics_id', 'value' => '', 'type' => 'text', 'group' => 'seo', 'label' => 'Google Analytics ID'],
            ['key' => 'google_tag_manager_id', 'value' => '', 'type' => 'text', 'group' => 'seo', 'label' => 'Google Tag Manager ID'],
            
            // Footer - Contato
            ['key' => 'footer_address', 'value' => '', 'type' => 'textarea', 'group' => 'footer', 'label' => 'Endereço'],
            ['key' => 'footer_phone', 'value' => '', 'type' => 'text', 'group' => 'footer', 'label' => 'Telefone'],
            ['key' => 'footer_whatsapp', 'value' => '', 'type' => 'text', 'group' => 'footer', 'label' => 'WhatsApp'],
            ['key' => 'footer_email', 'value' => '', 'type' => 'text', 'group' => 'footer', 'label' => 'E-mail'],
            ['key' => 'footer_hours', 'value' => '', 'type' => 'textarea', 'group' => 'footer', 'label' => 'Horário de Funcionamento'],
            ['key' => 'footer_about', 'value' => '', 'type' => 'textarea', 'group' => 'footer', 'label' => 'Sobre (texto curto)'],
            ['key' => 'footer_copyright', 'value' => '© 2026 Loja de Discos. Todos os direitos reservados.', 'type' => 'text', 'group' => 'footer', 'label' => 'Copyright'],
            
            // Redes Sociais
            ['key' => 'social_instagram', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'Instagram URL'],
            ['key' => 'social_facebook', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'Facebook URL'],
            ['key' => 'social_youtube', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'YouTube URL'],
            ['key' => 'social_tiktok', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'TikTok URL'],
            ['key' => 'social_soundcloud', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'SoundCloud URL'],
            ['key' => 'social_mixcloud', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'Mixcloud URL'],
            ['key' => 'social_discogs', 'value' => '', 'type' => 'text', 'group' => 'social', 'label' => 'Discogs URL'],
        ];

        foreach ($defaults as $setting) {
            static::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
