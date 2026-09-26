<template>
    <li class="rss-news-item" :style="{ animationDelay: `${delay}ms` }">
        <a
            :href="item.link"
            target="_blank"
            rel="noopener"
            class="rss-news-item-link"
            :class="{ 'is-read': read }"
            @click="$emit('read', item)"
        >
            <FeedThumb v-if="showImage && item.image_url" :item="item" small />

            <div class="rss-news-item-content">
                <p class="rss-news-item-title" v-html="highlighted"></p>
                <span class="rss-news-item-meta">
                    <span
                        v-if="item.source_title && showSource"
                        class="rss-news-source-badge"
                        >{{ item.source_title }}</span
                    >
                    <span
                        class="rss-news-item-time"
                        :title="formatAbsolute(item.published_at)"
                    >
                        {{ formatRelative(item.published_at) }}
                    </span>
                </span>
            </div>

            <button
                v-if="saveable"
                type="button"
                class="rss-news-save"
                :class="{ 'is-saved': saved }"
                :aria-pressed="saved"
                :title="__('Mark all as read')"
                @click.prevent.stop="$emit('save', item)"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    :fill="saved ? 'currentColor' : 'none'"
                    stroke="currentColor"
                    stroke-width="1.5"
                    class="w-4 h-4"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 3.5h10a1 1 0 011 1v12l-6-3.5-6 3.5v-12a1 1 0 011-1z"
                    />
                </svg>
            </button>

            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="rss-news-item-chevron"
                aria-hidden="true"
            >
                <path
                    fill-rule="evenodd"
                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                    clip-rule="evenodd"
                />
            </svg>
        </a>
    </li>
</template>

<script setup>
import { computed } from "vue";
import FeedThumb from "./FeedThumb.vue";
import { useRelativeTime } from "../composables/useRelativeTime";
import { highlight } from "../support/highlight";
import { __ } from "../composables/useTranslations";

const props = defineProps({
    item: { type: Object, required: true },
    delay: { type: Number, default: 0 },
    read: { type: Boolean, default: false },
    saved: { type: Boolean, default: false },
    saveable: { type: Boolean, default: false },
    showImage: { type: Boolean, default: false },
    showSource: { type: Boolean, default: false },
    term: { type: String, default: "" },
});

defineEmits(["read", "save"]);

const { formatRelative, formatAbsolute } = useRelativeTime();

const highlighted = computed(() => highlight(props.item.title, props.term));
</script>
