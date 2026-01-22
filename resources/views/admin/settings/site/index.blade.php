<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configurações do Site
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.settings.site.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Tabs -->
                <div x-data="{ activeTab: 'general' }" class="space-y-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button type="button" @click="activeTab = 'general'"
                                :class="activeTab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Geral
                            </button>
                            <button type="button" @click="activeTab = 'logo'"
                                :class="activeTab === 'logo' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Logo & Favicon
                            </button>
                            <button type="button" @click="activeTab = 'seo'"
                                :class="activeTab === 'seo' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                SEO & Analytics
                            </button>
                            <button type="button" @click="activeTab = 'footer'"
                                :class="activeTab === 'footer' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                Footer
                            </button>
                            <button type="button" @click="activeTab = 'social'"
                                :class="activeTab === 'social' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                                Redes Sociais
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Geral -->
                    <div x-show="activeTab === 'general'" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Informações Gerais</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-gray-700">Nome do Site</label>
                                    <input type="text" name="site_name" id="site_name" value="{{ $settings['site_name'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="site_description" class="block text-sm font-medium text-gray-700">Descrição do Site</label>
                                    <textarea name="site_description" id="site_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $settings['site_description'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label for="site_email" class="block text-sm font-medium text-gray-700">E-mail de Contato</label>
                                    <input type="email" name="site_email" id="site_email" value="{{ $settings['site_email'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="site_phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                                    <input type="text" name="site_phone" id="site_phone" value="{{ $settings['site_phone'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="site_whatsapp" class="block text-sm font-medium text-gray-700">WhatsApp</label>
                                    <input type="text" name="site_whatsapp" id="site_whatsapp" value="{{ $settings['site_whatsapp'] ?? '' }}" placeholder="5511999999999" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <p class="mt-1 text-sm text-gray-500">Formato: código do país + DDD + número (sem espaços ou caracteres)</p>
                                </div>
                                <div>
                                    <label for="site_address" class="block text-sm font-medium text-gray-700">Endereço</label>
                                    <textarea name="site_address" id="site_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $settings['site_address'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label for="site_hours" class="block text-sm font-medium text-gray-700">Horário de Funcionamento</label>
                                    <input type="text" name="site_hours" id="site_hours" value="{{ $settings['site_hours'] ?? '' }}" placeholder="Seg-Sex: 9h-18h | Sáb: 9h-13h" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Logo & Favicon -->
                    <div x-show="activeTab === 'logo'" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Logo & Favicon</h3>
                            <div class="space-y-6">
                                <!-- Logo Principal -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Logo Principal</label>
                                    <p class="text-sm text-gray-500 mb-2">Logo para fundo claro (recomendado: PNG com fundo transparente)</p>
                                    @if(!empty($settings['logo']))
                                        <div class="mb-3 p-4 bg-gray-100 rounded-lg inline-block">
                                            <img src="{{ Storage::url($settings['logo']) }}" alt="Logo" class="max-h-20">
                                            <a href="{{ route('admin.settings.site.remove-image', 'logo') }}" class="mt-2 inline-flex items-center text-sm text-red-600 hover:text-red-800" onclick="return confirm('Remover logo?')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Remover
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" name="logo" id="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>

                                <!-- Logo Branco -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Logo Branco</label>
                                    <p class="text-sm text-gray-500 mb-2">Logo para fundo escuro (recomendado: PNG com fundo transparente)</p>
                                    @if(!empty($settings['logo_white']))
                                        <div class="mb-3 p-4 bg-gray-800 rounded-lg inline-block">
                                            <img src="{{ Storage::url($settings['logo_white']) }}" alt="Logo Branco" class="max-h-20">
                                            <a href="{{ route('admin.settings.site.remove-image', 'logo_white') }}" class="mt-2 inline-flex items-center text-sm text-red-400 hover:text-red-300" onclick="return confirm('Remover logo branco?')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Remover
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" name="logo_white" id="logo_white" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>

                                <!-- Favicon -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Favicon</label>
                                    <p class="text-sm text-gray-500 mb-2">Envie uma imagem quadrada (recomendado: 512x512px). Os favicons serão gerados automaticamente.</p>
                                    @if(!empty($settings['favicon_source']))
                                        <div class="mb-3">
                                            <div class="p-4 bg-gray-100 rounded-lg inline-block">
                                                <img src="{{ Storage::url($settings['favicon_source']) }}" alt="Favicon Source" class="max-h-20">
                                                <a href="{{ route('admin.settings.site.remove-image', 'favicon_source') }}" class="mt-2 inline-flex items-center text-sm text-red-600 hover:text-red-800" onclick="return confirm('Remover favicon?')">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Remover
                                                </a>
                                            </div>
                                            @if(Storage::disk('public')->exists('favicons/favicon-32x32.png'))
                                                <div class="mt-4 p-4 bg-green-50 rounded-lg">
                                                    <p class="text-sm font-medium text-green-800 mb-2">Favicons Gerados:</p>
                                                    <div class="flex items-end gap-4">
                                                        <div class="text-center">
                                                            <img src="{{ Storage::url('favicons/favicon-16x16.png') }}?v={{ time() }}" alt="16x16" class="border border-gray-300">
                                                            <span class="text-xs text-gray-500 block mt-1">16x16</span>
                                                        </div>
                                                        <div class="text-center">
                                                            <img src="{{ Storage::url('favicons/favicon-32x32.png') }}?v={{ time() }}" alt="32x32" class="border border-gray-300">
                                                            <span class="text-xs text-gray-500 block mt-1">32x32</span>
                                                        </div>
                                                        <div class="text-center">
                                                            <img src="{{ Storage::url('favicons/apple-touch-icon.png') }}?v={{ time() }}" alt="Apple" class="border border-gray-300 max-h-12">
                                                            <span class="text-xs text-gray-500 block mt-1">Apple 180x180</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    <input type="file" name="favicon_source" id="favicon_source" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab SEO -->
                    <div x-show="activeTab === 'seo'" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">SEO & Analytics</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="seo_title" class="block text-sm font-medium text-gray-700">Título SEO</label>
                                    <input type="text" name="seo_title" id="seo_title" value="{{ $settings['seo_title'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <p class="mt-1 text-sm text-gray-500">Título exibido nos resultados de busca (máx. 60 caracteres)</p>
                                </div>
                                <div>
                                    <label for="seo_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                                    <textarea name="seo_description" id="seo_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $settings['seo_description'] ?? '' }}</textarea>
                                    <p class="mt-1 text-sm text-gray-500">Descrição exibida nos resultados de busca (máx. 160 caracteres)</p>
                                </div>
                                <div>
                                    <label for="seo_keywords" class="block text-sm font-medium text-gray-700">Palavras-chave</label>
                                    <input type="text" name="seo_keywords" id="seo_keywords" value="{{ $settings['seo_keywords'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <p class="mt-1 text-sm text-gray-500">Separadas por vírgula</p>
                                </div>
                                
                                <!-- OG Image -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Imagem Open Graph</label>
                                    <p class="text-sm text-gray-500 mb-2">Imagem exibida ao compartilhar nas redes sociais (recomendado: 1200x630px)</p>
                                    @if(!empty($settings['seo_og_image']))
                                        <div class="mb-3 p-4 bg-gray-100 rounded-lg inline-block">
                                            <img src="{{ Storage::url($settings['seo_og_image']) }}" alt="OG Image" class="max-h-32">
                                            <a href="{{ route('admin.settings.site.remove-image', 'seo_og_image') }}" class="mt-2 inline-flex items-center text-sm text-red-600 hover:text-red-800" onclick="return confirm('Remover imagem?')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Remover
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" name="seo_og_image" id="seo_og_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>

                                <hr class="my-6">
                                <h4 class="text-md font-medium text-gray-900">Google Analytics & Tag Manager</h4>
                                
                                <div>
                                    <label for="google_analytics_id" class="block text-sm font-medium text-gray-700">Google Analytics ID</label>
                                    <input type="text" name="google_analytics_id" id="google_analytics_id" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="G-XXXXXXXXXX" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="google_tag_manager_id" class="block text-sm font-medium text-gray-700">Google Tag Manager ID</label>
                                    <input type="text" name="google_tag_manager_id" id="google_tag_manager_id" value="{{ $settings['google_tag_manager_id'] ?? '' }}" placeholder="GTM-XXXXXXX" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Footer -->
                    <div x-show="activeTab === 'footer'" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Informações do Footer</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="footer_about" class="block text-sm font-medium text-gray-700">Texto Sobre</label>
                                    <textarea name="footer_about" id="footer_about" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $settings['footer_about'] ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label for="footer_copyright" class="block text-sm font-medium text-gray-700">Texto de Copyright</label>
                                    <input type="text" name="footer_copyright" id="footer_copyright" value="{{ $settings['footer_copyright'] ?? '' }}" placeholder="© 2026 Nome da Loja. Todos os direitos reservados." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Redes Sociais -->
                    <div x-show="activeTab === 'social'" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Redes Sociais</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="social_instagram" class="block text-sm font-medium text-gray-700">
                                        <svg class="w-5 h-5 inline mr-1 text-pink-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                        Instagram
                                    </label>
                                    <input type="url" name="social_instagram" id="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="social_facebook" class="block text-sm font-medium text-gray-700">
                                        <svg class="w-5 h-5 inline mr-1 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        Facebook
                                    </label>
                                    <input type="url" name="social_facebook" id="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="social_youtube" class="block text-sm font-medium text-gray-700">
                                        <svg class="w-5 h-5 inline mr-1 text-red-600" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                        YouTube
                                    </label>
                                    <input type="url" name="social_youtube" id="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="social_tiktok" class="block text-sm font-medium text-gray-700">
                                        <svg class="w-5 h-5 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                        TikTok
                                    </label>
                                    <input type="url" name="social_tiktok" id="social_tiktok" value="{{ $settings['social_tiktok'] ?? '' }}" placeholder="https://tiktok.com/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="social_soundcloud" class="block text-sm font-medium text-gray-700">
                                        <svg class="w-5 h-5 inline mr-1 text-orange-500" fill="currentColor" viewBox="0 0 24 24"><path d="M1.175 12.225c-.051 0-.094.046-.101.1l-.233 2.154.233 2.105c.007.058.05.098.101.098.05 0 .09-.04.099-.098l.255-2.105-.27-2.154c-.009-.06-.052-.1-.084-.1zm-.899 1.05c-.05 0-.09.04-.099.1l-.181 1.1.181 1.05c.01.057.049.097.099.097.05 0 .09-.04.099-.097l.209-1.05-.209-1.1c-.01-.06-.049-.1-.099-.1zm1.8-.9c-.06 0-.102.045-.109.1l-.209 1.95.209 1.9c.007.057.049.1.109.1.058 0 .1-.043.107-.1l.235-1.9-.235-1.95c-.007-.055-.049-.1-.107-.1zm.899-.45c-.063 0-.105.043-.112.1l-.184 2.4.184 2.35c.007.057.049.1.112.1.061 0 .103-.043.111-.1l.209-2.35-.209-2.4c-.008-.057-.05-.1-.111-.1zm.9-.451c-.063 0-.105.043-.112.101l-.184 2.85.184 2.75c.007.057.049.1.112.1.061 0 .103-.043.111-.1l.209-2.75-.209-2.85c-.008-.058-.05-.101-.111-.101zm.9-.45c-.063 0-.105.043-.112.1l-.184 3.3.184 3.15c.007.058.049.1.112.1.061 0 .103-.042.111-.1l.209-3.15-.209-3.3c-.008-.057-.05-.1-.111-.1zm.9-.45c-.063 0-.105.043-.112.1l-.184 3.75.184 3.6c.007.057.049.1.112.1.061 0 .103-.043.111-.1l.209-3.6-.209-3.75c-.008-.057-.05-.1-.111-.1zm.9-.45c-.063 0-.105.043-.112.1l-.184 4.2.184 4.05c.007.057.049.1.112.1.061 0 .103-.043.111-.1l.209-4.05-.209-4.2c-.008-.057-.05-.1-.111-.1zm.9-.45c-.063 0-.105.043-.112.1l-.184 4.65.184 4.5c.007.057.049.1.112.1.061 0 .103-.043.111-.1l.209-4.5-.209-4.65c-.008-.057-.05-.1-.111-.1zm.9-.45c-.063 0-.105.043-.112.1l-.184 5.1.184 4.95c.007.057.049.1.112.1.061 0 .103-.043.111-.1l.209-4.95-.209-5.1c-.008-.057-.05-.1-.111-.1zm5.062-.075c-.197 0-.391.021-.578.063-.178-2.022-1.89-3.612-3.969-3.612-.521 0-1.028.104-1.5.291-.178.071-.225.142-.227.281v9.404c.002.144.114.262.256.274h6.018c1.382 0 2.503-1.121 2.503-2.503s-1.121-2.198-2.503-2.198z"/></svg>
                                        SoundCloud
                                    </label>
                                    <input type="url" name="social_soundcloud" id="social_soundcloud" value="{{ $settings['social_soundcloud'] ?? '' }}" placeholder="https://soundcloud.com/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="social_mixcloud" class="block text-sm font-medium text-gray-700">
                                        <svg class="w-5 h-5 inline mr-1 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M2.462 8.596l1.372 6.49h.319l1.372-6.49h1.49l1.372 6.49h.319l1.372-6.49h1.49l-2.042 8.404H6.064l-1.372-6.49h-.319l-1.372 6.49H1.539L-.503 8.596h1.49zm9.462 0v8.404h-1.49V8.596h1.49zm2.98 0l2.042 4.202 2.042-4.202h1.49l-2.787 5.404v3h-1.49v-3l-2.787-5.404h1.49z"/></svg>
                                        Mixcloud
                                    </label>
                                    <input type="url" name="social_mixcloud" id="social_mixcloud" value="{{ $settings['social_mixcloud'] ?? '' }}" placeholder="https://mixcloud.com/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="social_discogs" class="block text-sm font-medium text-gray-700">
                                        <svg class="w-5 h-5 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm0 2.824a9.176 9.176 0 110 18.352 9.176 9.176 0 010-18.352zm0 3.294a5.882 5.882 0 100 11.764 5.882 5.882 0 000-11.764zm0 2.824a3.059 3.059 0 110 6.117 3.059 3.059 0 010-6.117z"/></svg>
                                        Discogs
                                    </label>
                                    <input type="url" name="social_discogs" id="social_discogs" value="{{ $settings['social_discogs'] ?? '' }}" placeholder="https://discogs.com/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="social_spotify" class="block text-sm font-medium text-gray-700">
                                        <svg class="w-5 h-5 inline mr-1 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                        Spotify
                                    </label>
                                    <input type="url" name="social_spotify" id="social_spotify" value="{{ $settings['social_spotify'] ?? '' }}" placeholder="https://open.spotify.com/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botão Salvar -->
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Salvar Configurações
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
