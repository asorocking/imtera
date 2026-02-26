<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    yandexUrl: {
        type: String,
        default: 'https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/',
    },
});

const form = useForm({
    yandex_url: props.yandexUrl || '',
});

const submit = () => {
    form.post(route('settings.yandex.store'));
};
</script>

<template>
    <Head title="Подключить Яндекс" />
    <AppLayout>
        <div class="max-w-2xl">
            <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 text-green-800 rounded-lg text-sm">
                {{ $page.props.flash.success }}
            </div>
            <h1 class="text-2xl font-semibold text-gray-900 mb-2">Подключить Яндекс</h1>
            <p class="text-gray-600 mb-4">
                Укажите ссылку на Яндекс, пример
            </p>
            <p class="text-sm text-gray-500 mb-2 break-all">
                https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/
            </p>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <TextInput
                        v-model="form.yandex_url"
                        type="url"
                        class="block w-full"
                        placeholder="https://yandex.ru/maps/org/..."
                        required
                    />
                    <InputError class="mt-1" :message="form.errors.yandex_url" />
                </div>
                <PrimaryButton
                    type="submit"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    Сохранить
                </PrimaryButton>
            </form>
        </div>
    </AppLayout>
</template>
