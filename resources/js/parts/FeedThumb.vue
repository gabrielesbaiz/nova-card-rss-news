<template>
    <div
        v-if="item.image_url && !failed"
        class="rss-news-thumb"
        :class="{ 'is-small': small }"
    >
        <img
            :src="item.image_url"
            :alt="''"
            loading="lazy"
            decoding="async"
            @error="failed = true"
        />
    </div>
</template>

<script setup>
import { ref } from "vue";

defineProps({
    item: { type: Object, required: true },
    small: { type: Boolean, default: false },
});

const failed = ref(false);
</script>
