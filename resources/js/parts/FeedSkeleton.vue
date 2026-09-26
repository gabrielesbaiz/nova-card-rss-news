<template>
    <div v-for="i in count" :key="`skeleton-${i}`" class="rss-news-skeleton">
        <div class="rss-news-skeleton-line w-3/4"></div>
        <div class="rss-news-skeleton-line w-full mt-2"></div>
        <div class="rss-news-skeleton-line w-1/3 mt-2"></div>
    </div>
</template>

<script setup>
defineProps({ count: { type: Number, default: 3 } });
</script>
