<template>
    <div class="rss-news-favicon-wrap shrink-0">
        <img
            v-if="favicon.url.value"
            :src="favicon.url.value"
            :alt="title"
            class="rss-news-favicon"
            @error="favicon.onError"
        />
        <div v-else class="rss-news-favicon-fallback" aria-hidden="true">
            {{ favicon.initials.value }}
        </div>
    </div>
</template>

<script setup>
import { toRef } from "vue";
import { useFavicon } from "../composables/useFavicon";

const props = defineProps({
    title: { type: String, default: "" },
    siteUrl: { type: String, default: null },
    mode: { type: String, default: "google" },
});

const favicon = useFavicon(
    toRef(props, "siteUrl"),
    toRef(props, "title"),
    props.mode,
);
</script>
