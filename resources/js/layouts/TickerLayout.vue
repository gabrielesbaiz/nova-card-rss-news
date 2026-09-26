<template>
    <div class="rss-news-ticker" @mouseenter="paused = true" @mouseleave="paused = false">
        <div class="rss-news-ticker-track" :class="{ 'is-paused': paused }">
            <a
                v-for="item in loop"
                :key="`${item.id}-${item.repeat}`"
                :href="item.link"
                target="_blank"
                rel="noopener"
                class="rss-news-ticker-item"
                @click="$emit('read', item)"
            >
                <span class="rss-news-ticker-dot" aria-hidden="true"></span>
                <span class="rss-news-ticker-title">{{ item.title }}</span>
                <span class="rss-news-ticker-time">{{
                    formatRelative(item.published_at)
                }}</span>
            </a>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useRelativeTime } from "../composables/useRelativeTime";

const props = defineProps({
    items: { type: Array, default: () => [] },
});

defineEmits(["read", "save"]);

const { formatRelative } = useRelativeTime();
const paused = ref(false);

// The track is duplicated so the marquee wraps without a visible seam.
const loop = computed(() => [
    ...props.items.map((item) => ({ ...item, repeat: 0 })),
    ...props.items.map((item) => ({ ...item, repeat: 1 })),
]);
</script>
