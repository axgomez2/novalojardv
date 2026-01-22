<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    featuredProducts: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Início" />
    
    <PublicLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                        Descubra o Mundo dos Vinis
                    </h1>
                    <p class="text-xl md:text-2xl text-indigo-100 mb-8 max-w-2xl mx-auto">
                        Encontre raridades, clássicos e lançamentos. A melhor seleção de discos de vinil do Brasil.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/produtos" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-indigo-50 transition-colors">
                            Ver Catálogo
                        </Link>
                        <Link href="/cadastro" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition-colors">
                            Criar Conta
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Qualidade Garantida</h3>
                        <p class="text-gray-600">Todos os discos são verificados e classificados por especialistas.</p>
                    </div>
                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Envio Seguro</h3>
                        <p class="text-gray-600">Embalagem especial para proteger seus discos durante o transporte.</p>
                    </div>
                    <div class="text-center p-6">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Wishlist</h3>
                        <p class="text-gray-600">Salve seus discos favoritos e seja notificado quando chegarem.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 bg-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Pronto para começar sua coleção?
                </h2>
                <p class="text-gray-600 mb-8 max-w-xl mx-auto">
                    Crie sua conta gratuitamente e tenha acesso a ofertas exclusivas, wishlist e muito mais.
                </p>
                <Link href="/cadastro" class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                    Cadastre-se Grátis
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>
