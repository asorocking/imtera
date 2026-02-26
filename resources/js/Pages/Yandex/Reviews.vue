<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    reviews: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    rating: {
        type: String,
        default: null,
    },
    totalReviews: {
        type: Number,
        default: 0,
    },
    orgName: {
        type: String,
        default: null,
    },
    hasSetting: {
        type: Boolean,
        default: false,
    },
    sort: {
        type: String,
        default: 'newest',
    },
});

const stars = (n) => Math.round(Number(n)) || 0;

const ratingNum = computed(() => (props.rating ? parseFloat(props.rating) : 0));
const fullStars = computed(() => Math.floor(ratingNum.value));
const partialPercent = computed(() => {
    const frac = ratingNum.value - fullStars.value;
    return Math.round(frac * 100);
});

function formatReviewDate(value) {
    if (!value) return '—';
    const d = new Date(value);
    return Number.isNaN(d.getTime())
        ? '—'
        : d.toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Отзывы" />
    <AppLayout>
        <div class="flex flex-col">
            <div class="flex items-center gap-2 text-gray-600 mb-4">
                <svg class="w-5 h-5 text-red-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                <span>Яндекс Карты</span>
            </div>

            <div v-if="!hasSetting" class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-800 mb-6">
                <p class="font-medium">Сначала укажите ссылку на организацию в Яндекс.Картах.</p>
                <Link :href="route('settings.yandex')" class="text-amber-700 underline mt-1 inline-block">
                    Перейти в настройки →
                </Link>
            </div>

            <template v-else>
                <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                    {{ $page.props.flash.error }}
                </div>

                <div class="flex gap-6 flex-col md:flex-row md:items-start">
                    <div class="flex-1 min-w-0">
                        <ul class="space-y-4">
                            <li
                                v-for="review in reviews.data"
                                :key="review.id"
                                class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm"
                            >
                                <div class="flex justify-between items-start gap-2 mb-2">
                                    <span class="text-sm text-gray-500">
                                        {{ formatReviewDate(review.review_date) }}
                                    </span>
                                    <span v-if="review.branch_name" class="text-sm text-gray-500 flex items-center gap-1">
                                        {{ review.branch_name }}
                                        <svg class="w-4 h-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <p class="font-medium text-gray-900">
                                    {{ review.author_name || 'Гость' }}
                                    <span v-if="review.author_phone" class="text-gray-500 font-normal">{{ review.author_phone }}</span>
                                </p>
                                <p class="mt-2 text-gray-700 whitespace-pre-wrap">{{ review.text }}</p>
                                <div v-if="review.rating" class="mt-2 flex gap-0.5">
                                    <template v-for="i in 5" :key="i">
                                        <svg
                                            :class="i <= stars(review.rating) ? 'text-yellow-400' : 'text-gray-200'"
                                            class="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </template>
                                </div>
                            </li>
                        </ul>

                        <div v-if="reviews.data.length === 0 && hasSetting" class="text-center py-12 text-gray-500">
                            Отзывов пока нет. Проверьте ссылку в настройках.
                        </div>

                        <div v-if="reviews.prev_page_url || reviews.next_page_url" class="mt-6 flex items-center justify-center gap-2">
                            <Link
                                v-if="reviews.prev_page_url"
                                :href="reviews.prev_page_url"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50"
                            >
                                ← Назад
                            </Link>
                            <span class="text-sm text-gray-500">
                                Страница {{ reviews.current_page }} из {{ reviews.last_page }}
                            </span>
                            <Link
                                v-if="reviews.next_page_url"
                                :href="reviews.next_page_url"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Вперёд →
                            </Link>
                        </div>
                    </div>

                    <aside v-if="hasSetting && (rating || totalReviews)" class="lg:w-64 shrink-0">
                        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                            <div v-if="rating" class="flex items-center gap-2 mb-1">
                                <span class="text-3xl font-semibold text-gray-900">{{ rating }}</span>
                                <div class="flex gap-0.5 items-center">
                                    <template v-for="i in 5" :key="i">
                                        <span v-if="i <= fullStars" class="text-yellow-400">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </span>
                                        <span v-else-if="i === fullStars + 1 && partialPercent > 0" class="relative inline-block w-6 h-6">
                                            <svg class="w-6 h-6 text-gray-200 absolute inset-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-yellow-400 absolute inset-0 overflow-hidden" :style="{ width: partialPercent + '%' }">
                                                <svg class="w-6 h-6 absolute left-0 top-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </span>
                                        </span>
                                        <span v-else class="text-gray-200">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <p v-if="totalReviews" class="text-gray-600 text-sm">
                                Всего отзывов: {{ totalReviews.toLocaleString('ru-RU') }}
                            </p>
                        </div>
                    </aside>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
