<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class SiteSettingsController extends Controller
{
    /**
     * Exibir página de configurações
     */
    public function index()
    {
        // Inicializar configurações padrão se não existirem
        $this->initDefaults();

        // Retornar como array key => value
        $settings = SiteSetting::pluck('value', 'key')->toArray();

        return view('admin.settings.site.index', compact('settings'));
    }

    /**
     * Inicializar configurações padrão
     */
    protected function initDefaults(): void
    {
        $defaults = [
            // Geral
            ['key' => 'site_name', 'value' => '', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_description', 'value' => '', 'type' => 'textarea', 'group' => 'general'],
            ['key' => 'site_email', 'value' => '', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_whatsapp', 'value' => '', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_address', 'value' => '', 'type' => 'textarea', 'group' => 'general'],
            ['key' => 'site_hours', 'value' => '', 'type' => 'text', 'group' => 'general'],
            
            // Logo
            ['key' => 'logo', 'value' => '', 'type' => 'image', 'group' => 'logo'],
            ['key' => 'logo_white', 'value' => '', 'type' => 'image', 'group' => 'logo'],
            ['key' => 'favicon_source', 'value' => '', 'type' => 'image', 'group' => 'logo'],
            
            // SEO
            ['key' => 'seo_title', 'value' => '', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'seo_description', 'value' => '', 'type' => 'textarea', 'group' => 'seo'],
            ['key' => 'seo_keywords', 'value' => '', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'seo_og_image', 'value' => '', 'type' => 'image', 'group' => 'seo'],
            ['key' => 'google_analytics_id', 'value' => '', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'google_tag_manager_id', 'value' => '', 'type' => 'text', 'group' => 'seo'],
            
            // Footer
            ['key' => 'footer_about', 'value' => '', 'type' => 'textarea', 'group' => 'footer'],
            ['key' => 'footer_copyright', 'value' => '', 'type' => 'text', 'group' => 'footer'],
            
            // Social
            ['key' => 'social_instagram', 'value' => '', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_facebook', 'value' => '', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_youtube', 'value' => '', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_tiktok', 'value' => '', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_soundcloud', 'value' => '', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_mixcloud', 'value' => '', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_discogs', 'value' => '', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_spotify', 'value' => '', 'type' => 'text', 'group' => 'social'],
        ];

        foreach ($defaults as $default) {
            SiteSetting::firstOrCreate(
                ['key' => $default['key']],
                $default
            );
        }
    }

    /**
     * Salvar configurações
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            
            if ($setting) {
                // Se for um campo de imagem, processar upload
                if ($setting->type === 'image' && $request->hasFile($key)) {
                    $file = $request->file($key);
                    
                    // Deletar imagem antiga se existir
                    if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                        Storage::disk('public')->delete($setting->value);
                    }
                    
                    // Salvar nova imagem
                    $path = $file->store('settings', 'public');
                    $setting->value = $path;
                    $setting->save();
                    
                    // Se for favicon_source, gerar favicons
                    if ($key === 'favicon_source') {
                        $this->generateFavicons($path);
                    }
                } elseif ($setting->type !== 'image') {
                    // Para outros tipos, salvar valor diretamente
                    $setting->value = $value;
                    $setting->save();
                }
            }
        }

        // Limpar cache
        SiteSetting::clearCache();

        return redirect()->route('admin.settings.site.index')
            ->with('success', 'Configurações salvas com sucesso!');
    }

    /**
     * Remover imagem de uma configuração
     */
    public function removeImage(Request $request, string $key)
    {
        $setting = SiteSetting::where('key', $key)->first();

        if ($setting && $setting->type === 'image' && $setting->value) {
            // Deletar arquivo
            if (Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }

            // Se for favicon, deletar favicons gerados
            if ($key === 'favicon_source') {
                $this->deleteFavicons();
            }

            $setting->value = null;
            $setting->save();

            SiteSetting::clearCache();
        }

        return redirect()->route('admin.settings.site.index')
            ->with('success', 'Imagem removida com sucesso!');
    }

    /**
     * Gerar favicons a partir da imagem fonte
     */
    protected function generateFavicons(string $sourcePath): void
    {
        $sizes = [
            'favicon-16x16.png' => 16,
            'favicon-32x32.png' => 32,
            'favicon-48x48.png' => 48,
            'apple-touch-icon.png' => 180,
            'android-chrome-192x192.png' => 192,
            'android-chrome-512x512.png' => 512,
            'mstile-150x150.png' => 150,
        ];

        $faviconDir = 'favicons';
        
        // Criar diretório se não existir
        if (!Storage::disk('public')->exists($faviconDir)) {
            Storage::disk('public')->makeDirectory($faviconDir);
        }

        // Carregar imagem fonte
        $sourceFullPath = Storage::disk('public')->path($sourcePath);
        
        foreach ($sizes as $filename => $size) {
            try {
                $image = Image::read($sourceFullPath);
                $image->cover($size, $size);
                
                $outputPath = Storage::disk('public')->path("{$faviconDir}/{$filename}");
                $image->save($outputPath);
            } catch (\Exception $e) {
                \Log::error("Erro ao gerar favicon {$filename}: " . $e->getMessage());
            }
        }

        // Gerar favicon.ico (multi-size)
        try {
            $image = Image::read($sourceFullPath);
            $image->cover(32, 32);
            $icoPath = Storage::disk('public')->path("{$faviconDir}/favicon.ico");
            $image->save($icoPath);
        } catch (\Exception $e) {
            \Log::error("Erro ao gerar favicon.ico: " . $e->getMessage());
        }

        // Gerar webmanifest
        $manifest = [
            'name' => SiteSetting::get('site_name', 'Loja de Discos'),
            'short_name' => SiteSetting::get('site_name', 'Loja'),
            'icons' => [
                [
                    'src' => '/storage/favicons/android-chrome-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/storage/favicons/android-chrome-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
            'theme_color' => '#1c1917',
            'background_color' => '#ffffff',
            'display' => 'standalone',
        ];

        Storage::disk('public')->put(
            "{$faviconDir}/site.webmanifest",
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        // Gerar browserconfig.xml para Windows
        $browserconfig = '<?xml version="1.0" encoding="utf-8"?>
<browserconfig>
    <msapplication>
        <tile>
            <square150x150logo src="/storage/favicons/mstile-150x150.png"/>
            <TileColor>#1c1917</TileColor>
        </tile>
    </msapplication>
</browserconfig>';

        Storage::disk('public')->put("{$faviconDir}/browserconfig.xml", $browserconfig);
    }

    /**
     * Deletar favicons gerados
     */
    protected function deleteFavicons(): void
    {
        $faviconDir = 'favicons';
        
        if (Storage::disk('public')->exists($faviconDir)) {
            Storage::disk('public')->deleteDirectory($faviconDir);
        }
    }
}
