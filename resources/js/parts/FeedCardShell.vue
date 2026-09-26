<template>
    <div class="nova-tw rss-news-card-wrapper">
        <Card
            class="rss-news-card flex flex-col h-full overflow-hidden"
            @mouseenter="autoRefresh.pause"
            @mouseleave="autoRefresh.resume"
        >
            <!-- Header -->
            <div class="rss-news-header flex items-center gap-3 px-5 pt-5 pb-3">
                <FeedIcon
                    :title="feed.title.value"
                    :site-url="feed.siteUrl.value"
                    :mode="card.favicons || 'none'"
                />

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span
                            v-if="!feed.loading.value && !feed.error.value"
                            class="rss-news-live-dot"
                            aria-hidden="true"
                        ></span>

                        <!-- The select card drops its picker in here. -->
                        <slot
                            name="title"
                            :title="card.heading || feed.title.value"
                        >
                            <h3 class="rss-news-title truncate">
                                {{ card.heading || feed.title.value }}
                            </h3>
                        </slot>

                        <span v-if="unread > 0" class="rss-news-new-badge">
                            {{ __(":count new", { count: unread }) }}
                        </span>
                    </div>

                    <p class="rss-news-subtitle">
                        {{ choice(":count item|:count items", visible.length) }}
                        <template v-if="updatedLabel">
                            · {{ updatedLabel }}
                        </template>
                        <template v-if="feed.stale.value">
                            · {{ __("Showing cached copy") }}
                        </template>
                    </p>
                </div>

                <button
                    v-if="card.search"
                    type="button"
                    class="rss-news-icon-button"
                    :class="{ 'is-active': searching }"
                    :aria-pressed="searching"
                    :aria-label="__('Search news…')"
                    @click="toggleSearch"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="w-4 h-4"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>

                <button
                    type="button"
                    class="rss-news-icon-button rss-news-refresh"
                    :class="{ 'is-spinning': feed.loading.value }"
                    :disabled="feed.loading.value"
                    :aria-label="__('Refresh')"
                    :title="__('Refresh')"
                    @click="feed.refresh"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="w-4 h-4"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>

            <SearchBox v-if="searching" v-model="term" />

            <!-- Body -->
            <div
                class="rss-news-body flex-1 px-5 pb-4 overflow-y-auto"
                aria-live="polite"
                :aria-busy="feed.loading.value"
            >
                <FeedSkeleton
                    v-if="feed.loading.value && !feed.items.value.length"
                />

                <FeedError
                    v-else-if="feed.error.value && !feed.items.value.length"
                    :message="feed.error.value"
                    @retry="feed.load"
                />

                <FeedEmpty
                    v-else-if="!visible.length"
                    :message="
                        term ? __('No results') : __('No news available')
                    "
                />

                <component
                    v-else
                    :is="layoutComponent"
                    :items="visible"
                    :is-read="readState.isRead"
                    :is-saved="readState.isSaved"
                    :saveable="Boolean(card.read_state)"
                    :show-images="Boolean(card.images)"
                    :show-source="showSource"
                    :term="term"
                    @read="readState.markRead"
                    @save="readState.toggleSaved"
                />
            </div>
        </Card>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import FeedEmpty from "./FeedEmpty.vue";
import FeedError from "./FeedError.vue";
import FeedIcon from "./FeedIcon.vue";
import FeedSkeleton from "./FeedSkeleton.vue";
import SearchBox from "./SearchBox.vue";
import CompactLayout from "../layouts/CompactLayout.vue";
import GridLayout from "../layouts/GridLayout.vue";
import HeroLayout from "../layouts/HeroLayout.vue";
import TickerLayout from "../layouts/TickerLayout.vue";
import { useAutoRefresh } from "../composables/useAutoRefresh";
import { useReadState } from "../composables/useReadState";
import { useRelativeTime } from "../composables/useRelativeTime";
import { __, choice } from "../composables/useTranslations";
import { matches } from "../support/highlight";

const props = defineProps({
    card: { type: Object, required: true },
    feed: { type: Object, required: true },
    storageKey: { type: String, required: true },
    showSource: { type: Boolean, default: false },
});

const LAYOUTS = {
    hero: HeroLayout,
    compact: CompactLayout,
    grid: GridLayout,
    ticker: TickerLayout,
};

const term = ref("");
const searching = ref(false);

const readState = useReadState(props.storageKey, Boolean(props.card.read_state));
const { formatRelative } = useRelativeTime();
const autoRefresh = useAutoRefresh(props.card.auto_refresh, () =>
    props.feed.load({ silent: true }),
);

const layoutComponent = computed(
    () => LAYOUTS[props.card.layout] ?? HeroLayout,
);

const visible = computed(() =>
    props.feed.items.value.filter((item) => matches(item, term.value)),
);

const unread = computed(() => readState.unreadCount(props.feed.items.value));

const updatedLabel = computed(() => {
    const value = props.feed.fetchedAt.value;

    return value ? __("updated :time", { time: formatRelative(value) }) : null;
});

function toggleSearch() {
    searching.value = !searching.value;

    if (!searching.value) {
        term.value = "";
    }
}

// A new set of items is a new reading session: drop the filter.
watch(
    () => props.feed.feedUrl.value,
    () => (term.value = ""),
);
</script>
