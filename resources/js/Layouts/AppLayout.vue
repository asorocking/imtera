<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const showingMobileMenu = ref(false);

const isActive = (name) => route().current() === name;
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar -->
        <aside class="w-56 min-h-screen bg-white border-r border-gray-200 flex flex-col fixed left-0 top-0 z-30">
            <div class="p-4 border-b border-gray-100">
                <Link :href="route('reviews.index')" class="flex items-center gap-2 text-gray-800 font-semibold no-underline">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <span>Daily Grow</span>
                </Link>
                <p class="mt-2 text-sm text-gray-500 truncate" :title="page.props.auth?.user?.name">
                    {{ page.props.auth?.user?.name || 'Название аккаунта' }}
                </p>
            </div>
            <nav class="flex-1 p-3 space-y-0.5">
                <Link
                    :href="route('reviews.index')"
                    :class="[
                        'flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition',
                        isActive('reviews.index')
                            ? 'bg-blue-50 text-blue-700'
                            : 'text-gray-600 hover:bg-gray-100'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    Отзывы
                </Link>
                <Link
                    :href="route('settings.yandex')"
                    :class="[
                        'flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition',
                        isActive('settings.yandex')
                            ? 'bg-blue-50 text-blue-700'
                            : 'text-gray-600 hover:bg-gray-100'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Настройка
                </Link>
            </nav>
        </aside>

        <!-- Main -->
        <div class="flex-1 pl-56 flex flex-col min-h-screen">
            <header class="h-14 bg-white border-b border-gray-200 flex items-center justify-end px-6 shrink-0">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition"
                    title="Выйти"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" />
                    </svg>
                </Link>
            </header>
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
