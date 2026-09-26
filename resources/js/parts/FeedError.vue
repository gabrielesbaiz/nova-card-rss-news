<template>
    <div class="rss-news-error" role="alert">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            class="w-8 h-8 mx-auto mb-2"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
            />
        </svg>
        <p class="rss-news-error-message">{{ message }}</p>
        <button type="button" class="rss-news-retry" @click="$emit('retry')">
            {{ __("Retry") }}
        </button>
    </div>
</template>

<script setup>
import { __ } from "../composables/useTranslations";

defineProps({ message: { type: String, required: true } });
defineEmits(["retry"]);
</script>
