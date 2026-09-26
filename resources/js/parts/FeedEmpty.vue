<template>
    <div class="rss-news-empty">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            class="w-10 h-10 mx-auto mb-2 opacity-40"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z"
            />
        </svg>
        <p>{{ message }}</p>
    </div>
</template>

<script setup>
import { __ } from "../composables/useTranslations";

defineProps({
    message: { type: String, default: () => __("No news available") },
});
</script>
