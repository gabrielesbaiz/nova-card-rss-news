<template>
    <div>
        <a
            v-if="hero"
            :href="hero.link"
            target="_blank"
            rel="noopener"
            class="rss-news-hero"
            :class="{ 'is-read': isRead(hero) }"
            @click="$emit('read', hero)"
        >
            <FeedThumb v-if="showImages" :item="hero" />
            <div class="rss-news-hero-badge">{{ __("Featured") }}</div>
            <h4 class="rss-news-hero-title" v-html="heroTitle"></h4>
            <p v-if="hero.summary" class="rss-news-hero-desc">
                {{ hero.summary }}
            </p>
            <div class="rss-news-hero-meta">
                <span
                    v-if="showSource && hero.source_title"
                    class="rss-news-source-badge"
                    >{{ hero.source_title }}</span
                >
                <span :title="formatAbsolute(hero.published_at)">
                    {{ formatRelative(hero.published_at) }}
                </span>
            </div>
        </a>

        <ul v-if="rest.length" class="rss-news-list">
            <FeedItemRow
                v-for="(item, index) in rest"
                :key="item.id"
                :item="item"
                :delay="(index + 1) * 60"
                :read="isRead(item)"
                :saved="isSaved(item)"
                :saveable="saveable"
                :show-image="showImages"
                :show-source="showSource"
                :term="term"
                @read="$emit('read', $event)"
                @save="$emit('save', $event)"
            />
        </ul>
    </div>
</template>

<script setup>
import { computed } from "vue";
import FeedItemRow from "../parts/FeedItemRow.vue";
import FeedThumb from "../parts/FeedThumb.vue";
import { useRelativeTime } from "../composables/useRelativeTime";
import { highlight } from "../support/highlight";
import { __ } from "../composables/useTranslations";

const props = defineProps({
    items: { type: Array, default: () => [] },
    isRead: { type: Function, default: () => false },
    isSaved: { type: Function, default: () => false },
    saveable: { type: Boolean, default: false },
    showImages: { type: Boolean, default: false },
    showSource: { type: Boolean, default: false },
    term: { type: String, default: "" },
});

defineEmits(["read", "save"]);

const { formatRelative, formatAbsolute } = useRelativeTime();

const hero = computed(() => props.items[0] ?? null);
const rest = computed(() => props.items.slice(1));
const heroTitle = computed(() =>
    highlight(hero.value?.title ?? "", props.term),
);
</script>
