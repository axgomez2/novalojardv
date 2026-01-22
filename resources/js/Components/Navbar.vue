<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const cart = computed(() => page.props.cart || { items: [], count: 0 });

// Estados
const mobileMenuOpen = ref(false);
const searchOpen = ref(false);
const userDropdownOpen = ref(false);
const searchQuery = ref('');

// Iniciais do usuário
const userInitials = computed(() => {
    if (!user.value?.name) return '';
    const names = user.value.name.split(' ');
    if (names.length >= 2) {
        return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase();
    }
    return names[0].charAt(0).toUpperCase();
});

// Fechar dropdown ao clicar fora
const closeDropdowns = (e) => {
    if (!e.target.closest('.user-dropdown')) {
        userDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns);
});

// Fechar menu mobile ao navegar
const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

// Busca
const handleSearch = () => {
    if (searchQuery.value.trim()) {
        // Navegar para página de busca
        window.location.href = `/busca?q=${encodeURIComponent(searchQuery.value)}`;
    }
};
</script>

<template>
    <header class="sticky top-0 z-50">
        <!-- DESKTOP NAVBAR -->
        <nav class="hidden lg:block bg-stone-900">
            <!-- Parte Superior: Logo, Busca, Login/Cadastro ou Área do Cliente + Carrinho -->
            <div class="border-b border-stone-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Logo -->
                        <Link href="/" class="flex items-center space-x-3 flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-stone-900" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" fill="currentColor"/>
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-white">Vinil Store</span>
                        </Link>

                        <!-- Campo de Busca -->
                        <div class="flex-1 max-w-xl mx-8">
                            <form @submit.prevent="handleSearch" class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Buscar discos, artistas, gêneros..."
                                    class="w-full bg-stone-800 border border-stone-700 rounded-lg pl-4 pr-12 py-2.5 text-white placeholder-stone-400 focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-400 transition-colors"
                                />
                                <button
                                    type="submit"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-stone-400 hover:text-yellow-400 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <!-- Área do Usuário + Carrinho -->
                        <div class="flex items-center space-x-4">
                            <template v-if="user">
                                <!-- Dropdown Área do Cliente -->
                                <div class="relative user-dropdown">
                                    <button
                                        @click="userDropdownOpen = !userDropdownOpen"
                                        class="flex items-center space-x-2 text-white hover:text-yellow-400 transition-colors"
                                    >
                                        <div class="w-9 h-9 bg-yellow-400 rounded-full flex items-center justify-center">
                                            <span class="text-stone-900 font-semibold text-sm">{{ userInitials }}</span>
                                        </div>
                                        <span class="font-medium hidden xl:inline">{{ user.name.split(' ')[0] }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <Transition
                                        enter-active-class="transition ease-out duration-100"
                                        enter-from-class="transform opacity-0 scale-95"
                                        enter-to-class="transform opacity-100 scale-100"
                                        leave-active-class="transition ease-in duration-75"
                                        leave-from-class="transform opacity-100 scale-100"
                                        leave-to-class="transform opacity-0 scale-95"
                                    >
                                        <div
                                            v-show="userDropdownOpen"
                                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50"
                                        >
                                            <div class="px-4 py-2 border-b border-gray-100">
                                                <p class="font-medium text-gray-900">{{ user.name }}</p>
                                                <p class="text-sm text-gray-500">{{ user.email }}</p>
                                            </div>
                                            <Link href="/minha-conta" class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Minha Conta
                                            </Link>
                                            <Link href="/minha-conta/pedidos" class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                Meus Pedidos
                                            </Link>
                                            <Link href="/minha-conta/wishlist" class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                                Lista de Desejos
                                            </Link>
                                            <Link href="/minha-conta/enderecos" class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                Endereços
                                            </Link>
                                            <hr class="my-2 border-gray-100">
                                            <Link
                                                href="/logout"
                                                method="post"
                                                as="button"
                                                class="flex items-center w-full px-4 py-2.5 text-red-600 hover:bg-red-50 transition-colors"
                                            >
                                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                                Sair
                                            </Link>
                                        </div>
                                    </Transition>
                                </div>

                                <!-- Carrinho -->
                                <Link href="/carrinho" class="relative p-2 text-white hover:text-yellow-400 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span
                                        v-if="cart.count > 0"
                                        class="absolute -top-1 -right-1 w-5 h-5 bg-yellow-400 text-stone-900 text-xs font-bold rounded-full flex items-center justify-center"
                                    >
                                        {{ cart.count > 9 ? '9+' : cart.count }}
                                    </span>
                                </Link>
                            </template>

                            <template v-else>
                                <!-- Login e Cadastro -->
                                <Link
                                    href="/login"
                                    class="text-white hover:text-yellow-400 transition-colors font-medium"
                                >
                                    Entrar
                                </Link>
                                <Link
                                    href="/cadastro"
                                    class="bg-yellow-400 text-stone-900 px-5 py-2 rounded-lg hover:bg-yellow-300 transition-colors font-semibold"
                                >
                                    Cadastrar
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parte Inferior: Links de Navegação -->
            <div class="bg-stone-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center space-x-8 h-12">
                        <Link
                            href="/"
                            class="text-white hover:text-yellow-400 transition-colors font-medium text-sm uppercase tracking-wide"
                            :class="{ 'text-yellow-400': $page.url === '/' }"
                        >
                            Home
                        </Link>
                        <Link
                            href="/ofertas"
                            class="text-white hover:text-yellow-400 transition-colors font-medium text-sm uppercase tracking-wide"
                            :class="{ 'text-yellow-400': $page.url.startsWith('/ofertas') }"
                        >
                            Ofertas
                        </Link>
                        <Link
                            href="/discos"
                            class="text-white hover:text-yellow-400 transition-colors font-medium text-sm uppercase tracking-wide"
                            :class="{ 'text-yellow-400': $page.url.startsWith('/discos') }"
                        >
                            Discos de Vinil
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- MOBILE NAVBAR -->
        <nav class="lg:hidden bg-stone-900">
            <div class="px-4 h-14 flex items-center justify-between">
                <!-- Menu Hamburger -->
                <button
                    @click="mobileMenuOpen = true"
                    class="p-2 text-white hover:text-yellow-400 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Logo Central -->
                <Link href="/" class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-stone-900" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
                            <circle cx="12" cy="12" r="3" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white">Vinil Store</span>
                </Link>

                <!-- Ações à Direita -->
                <div class="flex items-center space-x-1">
                    <!-- Botão de Busca -->
                    <button
                        @click="searchOpen = !searchOpen"
                        class="p-2 text-white hover:text-yellow-400 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    <!-- Usuário ou Login -->
                    <template v-if="user">
                        <Link href="/carrinho" class="relative p-2 text-white hover:text-yellow-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span
                                v-if="cart.count > 0"
                                class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-yellow-400 text-stone-900 text-[10px] font-bold rounded-full flex items-center justify-center"
                            >
                                {{ cart.count > 9 ? '9+' : cart.count }}
                            </span>
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="p-2 text-white hover:text-yellow-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Barra de Busca Mobile (expansível) -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-show="searchOpen" class="px-4 pb-3 bg-stone-900 border-t border-stone-800">
                    <form @submit.prevent="handleSearch" class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar discos..."
                            class="w-full bg-stone-800 border border-stone-700 rounded-lg pl-4 pr-10 py-2.5 text-white placeholder-stone-400 focus:outline-none focus:border-yellow-400"
                            autofocus
                        />
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </Transition>
        </nav>

        <!-- MOBILE DRAWER -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-show="mobileMenuOpen"
                    class="fixed inset-0 z-50 lg:hidden"
                >
                    <!-- Overlay -->
                    <div
                        class="absolute inset-0 bg-black/60"
                        @click="mobileMenuOpen = false"
                    ></div>

                    <!-- Drawer -->
                    <Transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="-translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="translate-x-0"
                        leave-to-class="-translate-x-full"
                    >
                        <div
                            v-show="mobileMenuOpen"
                            class="absolute inset-y-0 left-0 w-80 max-w-[85vw] bg-stone-900 shadow-2xl flex flex-col"
                        >
                            <!-- Header do Drawer -->
                            <div class="flex items-center justify-between p-4 border-b border-stone-800">
                                <Link href="/" @click="closeMobileMenu" class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-stone-900" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
                                            <circle cx="12" cy="12" r="3" fill="currentColor"/>
                                        </svg>
                                    </div>
                                    <span class="text-lg font-bold text-white">Vinil Store</span>
                                </Link>
                                <button
                                    @click="mobileMenuOpen = false"
                                    class="p-2 text-stone-400 hover:text-white transition-colors"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Links de Navegação -->
                            <nav class="flex-1 overflow-y-auto py-4">
                                <div class="px-4 space-y-1">
                                    <Link
                                        href="/"
                                        @click="closeMobileMenu"
                                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-stone-800 hover:text-yellow-400 transition-colors font-medium"
                                        :class="{ 'bg-stone-800 text-yellow-400': $page.url === '/' }"
                                    >
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                        Home
                                    </Link>
                                    <Link
                                        href="/ofertas"
                                        @click="closeMobileMenu"
                                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-stone-800 hover:text-yellow-400 transition-colors font-medium"
                                        :class="{ 'bg-stone-800 text-yellow-400': $page.url.startsWith('/ofertas') }"
                                    >
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        Ofertas
                                    </Link>
                                    <Link
                                        href="/discos"
                                        @click="closeMobileMenu"
                                        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-stone-800 hover:text-yellow-400 transition-colors font-medium"
                                        :class="{ 'bg-stone-800 text-yellow-400': $page.url.startsWith('/discos') }"
                                    >
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke-width="2" fill="none"/>
                                            <circle cx="12" cy="12" r="3" stroke-width="2" fill="none"/>
                                        </svg>
                                        Discos de Vinil
                                    </Link>
                                </div>
                            </nav>

                            <!-- Área do Usuário (parte inferior) -->
                            <div class="border-t border-stone-800 p-4">
                                <template v-if="user">
                                    <div class="flex items-center space-x-3 mb-4 px-2">
                                        <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-stone-900 font-bold text-lg">{{ userInitials }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-white truncate">{{ user.name }}</p>
                                            <p class="text-sm text-stone-400 truncate">{{ user.email }}</p>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <Link
                                            href="/minha-conta"
                                            @click="closeMobileMenu"
                                            class="flex items-center px-4 py-2.5 rounded-lg text-stone-300 hover:bg-stone-800 hover:text-yellow-400 transition-colors text-sm"
                                        >
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Minha Conta
                                        </Link>
                                        <Link
                                            href="/minha-conta/pedidos"
                                            @click="closeMobileMenu"
                                            class="flex items-center px-4 py-2.5 rounded-lg text-stone-300 hover:bg-stone-800 hover:text-yellow-400 transition-colors text-sm"
                                        >
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                            </svg>
                                            Meus Pedidos
                                        </Link>
                                        <Link
                                            href="/carrinho"
                                            @click="closeMobileMenu"
                                            class="flex items-center px-4 py-2.5 rounded-lg text-stone-300 hover:bg-stone-800 hover:text-yellow-400 transition-colors text-sm"
                                        >
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Carrinho
                                            <span v-if="cart.count > 0" class="ml-auto bg-yellow-400 text-stone-900 text-xs font-bold px-2 py-0.5 rounded-full">
                                                {{ cart.count }}
                                            </span>
                                        </Link>
                                        <Link
                                            href="/logout"
                                            method="post"
                                            as="button"
                                            @click="closeMobileMenu"
                                            class="flex items-center w-full px-4 py-2.5 rounded-lg text-red-400 hover:bg-red-900/20 transition-colors text-sm"
                                        >
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Sair
                                        </Link>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="space-y-2">
                                        <Link
                                            href="/login"
                                            @click="closeMobileMenu"
                                            class="flex items-center justify-center w-full py-3 border border-stone-600 rounded-lg text-white hover:border-yellow-400 hover:text-yellow-400 transition-colors font-medium"
                                        >
                                            Entrar
                                        </Link>
                                        <Link
                                            href="/cadastro"
                                            @click="closeMobileMenu"
                                            class="flex items-center justify-center w-full py-3 bg-yellow-400 rounded-lg text-stone-900 hover:bg-yellow-300 transition-colors font-semibold"
                                        >
                                            Cadastrar
                                        </Link>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </header>
</template>
