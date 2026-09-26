<template>
    <div class="rss-news-grid">
        <a
            v-for="(item, index) in items"
            :key="item.id"
            :href="item.link"
            target="_blank"
            rel="noopener"
            class="rss-news-grid-card"
            :class="{ 'is-read': isRead(item) }"
            :style="{ animationDelay: `${index * 45}ms` }"
            @click="$emit('read', item)"
        >
            <FeedThumb v-if="showImages" :item="item" />
            <div class="rss-news-grid-body">
                <p
                    class="rss-news-grid-title"
                    v-html="highlight(item.title, term)"
                ></p>
                <p v-if="item.summary" class="rss-news-grid-desc">
                    {{ item.summary }}
                </p>
                <div class="rss-news-grid-meta">
                    <span
                        v-if="showSource && item.source_title"
                        class="rss-news-source-badge"
                        >{{ item.source_title }}</span
                    >
                    <span :title="formatAbsolute(item.published_at)">
                        {{ formatRelative(item.published_at) }}
                    </span>
                </div>
            </div>
        </a>
    </div>
</template>

<script setup>
import FeedThumb from "../parts/FeedThumb.vue";
import { useRelativeTime } from "../composables/useRelativeTime";
import { highlight } from "../support/highlight";

defineProps({
    items: { type: Array, default: () => [] },
    isRead: { type: Function, default: () => false },
    showImages: { type: Boolean, default: true },
    showSource: { type: Boolean, default: false },
    term: { type: String, default: "" },
});

defineEmits(["read", "save"]);

const { formatRelative, formatAbsolute } = useRelativeTime();
</script>
