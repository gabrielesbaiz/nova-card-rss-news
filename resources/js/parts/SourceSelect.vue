<template>
    <div class="rss-news-source-select-wrap">
        <select
            class="rss-news-source-select"
            :value="modelValue"
            :disabled="disabled || !categories.length"
            :aria-label="__('All sources')"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <optgroup
                v-for="category in categories"
                :key="category.key"
                :label="category.label"
            >
                <option
                    v-for="source in category.sources"
                    :key="source.name"
                    :value="source.name"
                >
                    {{ source.title }}
                </option>
            </optgroup>
            <option v-if="!categories.length" :value="modelValue">
                {{ fallbackLabel }}
            </option>
        </select>
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            class="rss-news-source-chevron"
            aria-hidden="true"
        >
            <path
                fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                clip-rule="evenodd"
            />
        </svg>
    </div>
</template>

<script setup>
import { __ } from "../composables/useTranslations";

defineProps({
    modelValue: { type: String, default: "" },
    categories: { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
    fallbackLabel: { type: String, default: "" },
});

defineEmits(["update:modelValue"]);
</script>
