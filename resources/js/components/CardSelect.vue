<template>
    <div class="nova-tw rss-news-card-wrapper">
        <Card class="rss-news-card flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <div
                class="rss-news-header flex items-center gap-3 px-5 pt-5 pb-3"
            >
                <div class="rss-news-favicon-wrap shrink-0">
                    <img
                        v-if="faviconUrl && !faviconFailed"
                        :src="faviconUrl"
                        :alt="feedTitle"
                        class="rss-news-favicon"
                        @error="faviconFailed = true"
                    />
                    <div
                        v-else
                        class="rss-news-favicon-fallback"
                    >
                        {{ initials }}
                    </div>
                </div>

                <div class="flex-1 min-w-0 rss-news-source-picker">
                    <div class="flex items-center gap-2">
                        <span class="rss-news-live-dot" v-if="!loading"></span>
                        <div class="rss-news-source-select-wrap">
                            <select
                                class="rss-news-source-select"
                                :value="selectedSource"
                                @change="onSourceChange($event.target.value)"
                                :disabled="loading || !categories.length"
                            >
                                <optgroup
                                    v-for="cat in categories"
                                    :key="cat.key"
                                    :label="cat.label"
                                >
                                    <option
                                        v-for="src in cat.sources"
                                        :key="src.name"
                                        :value="src.name"
                                    >
                                        {{ src.title }}
                                    </option>
                                </optgroup>
                                <option
                                    v-if="!categories.length"
                                    :value="selectedSource"
                                >
                                    {{ feedTitle }}
                                </option>
                            </select>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="rss-news-source-chevron"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </div>
                    <p class="rss-news-subtitle">
                        {{ feedNews.length }}
                        {{ feedNews.length === 1 ? "notizia" : "notizie" }}
                        <template v-if="lastUpdatedRelative">
                            · agg. {{ lastUpdatedRelative }}
                        </template>
                    </p>
                </div>

                <button
                    type="button"
                    class="rss-news-refresh"
                    :class="{ 'is-spinning': loading }"
                    @click="fetchRssFeed(true)"
                    title="Aggiorna"
                    :disabled="loading"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="w-4 h-4"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="rss-news-body flex-1 px-5 pb-4 overflow-y-auto">
                <template v-if="loading && !feedNews.length">
                    <div
                        v-for="i in 3"
                        :key="`skel-${i}`"
                        class="rss-news-skeleton"
                    >
                        <div class="rss-news-skeleton-line w-3/4"></div>
                        <div class="rss-news-skeleton-line w-full mt-2"></div>
                        <div class="rss-news-skeleton-line w-1/3 mt-2"></div>
                    </div>
                </template>

                <div
                    v-else-if="!feedNews.length && !loading"
                    class="rss-news-empty"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        class="w-10 h-10 mx-auto mb-2 opacity-40"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z"
                        />
                    </svg>
                    <p>Nessuna notizia disponibile</p>
                </div>

                <template v-else>
                    <a
                        v-if="heroItem"
                        :href="heroItem.link"
                        target="_blank"
                        rel="noopener"
                        class="rss-news-hero"
                    >
                        <div class="rss-news-hero-badge">In evidenza</div>
                        <h4 class="rss-news-hero-title">
                            {{ heroItem.title }}
                        </h4>
                        <p
                            v-if="heroItem.description"
                            class="rss-news-hero-desc"
                        >
                            {{ heroItem.description }}
                        </p>
                        <div class="rss-news-hero-meta">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="w-3 h-3"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .2.08.39.22.53l3 3a.75.75 0 101.06-1.06L10.75 9.69V5z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span :title="formatAbsolute(heroItem.pubDate)">
                                {{ formatRelative(heroItem.pubDate) }}
                            </span>
                        </div>
                    </a>

                    <ul class="rss-news-list" v-if="listItems.length">
                        <li
                            v-for="(news, idx) in listItems"
                            :key="news.link"
                            class="rss-news-item"
                            :style="{
                                animationDelay: `${(idx + 1) * 60}ms`,
                            }"
                        >
                            <a
                                :href="news.link"
                                target="_blank"
                                rel="noopener"
                                class="rss-news-item-link"
                            >
                                <div class="rss-news-item-content">
                                    <p class="rss-news-item-title">
                                        {{ news.title }}
                                    </p>
                                    <span
                                        class="rss-news-item-time"
                                        :title="formatAbsolute(news.pubDate)"
                                    >
                                        {{ formatRelative(news.pubDate) }}
                                    </span>
                                </div>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    class="rss-news-item-chevron"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </template>
            </div>
        </Card>
    </div>
</template>

<script>
const STORAGE_PREFIX = "nova-card-rss-news-select:source:";

export default {
    props: ["card"],

    data() {
        return {
            categories: [],
            selectedSource: this.card.source_key || "motor1",
            feedTitle: "Caricamento…",
            feedUrl: null,
            feedNews: [],
            loading: true,
            faviconFailed: false,
            lastUpdatedAt: null,
            tickHandle: null,
            now: Date.now(),
        };
    },

    computed: {
        heroItem() {
            return this.feedNews[0] || null;
        },
        listItems() {
            return this.feedNews.slice(1);
        },
        storageKey() {
            return `${STORAGE_PREFIX}${this.card.component || "default"}`;
        },
        faviconUrl() {
            if (!this.feedUrl) {
                return null;
            }
            try {
                const host = new URL(this.feedUrl).hostname;
                return `https://www.google.com/s2/favicons?domain=${host}&sz=64`;
            } catch (e) {
                return null;
            }
        },
        initials() {
            return (this.feedTitle || "?")
                .replace(/news/gi, "")
                .trim()
                .split(/\s+/)
                .map((w) => w[0])
                .filter(Boolean)
                .slice(0, 2)
                .join("")
                .toUpperCase();
        },
        lastUpdatedRelative() {
            if (!this.lastUpdatedAt) {
                return null;
            }
            return this.formatRelative(this.lastUpdatedAt);
        },
    },

    mounted() {
        const persisted = this.readPersistedSource();
        if (persisted) {
            this.selectedSource = persisted;
        }
        this.fetchSources();
        this.fetchRssFeed();
        this.tickHandle = setInterval(() => {
            this.now = Date.now();
        }, 30000);
    },

    beforeUnmount() {
        if (this.tickHandle) {
            clearInterval(this.tickHandle);
        }
    },

    methods: {
        readPersistedSource() {
            try {
                return window.localStorage.getItem(this.storageKey);
            } catch (e) {
                return null;
            }
        },

        persistSource(key) {
            try {
                window.localStorage.setItem(this.storageKey, key);
            } catch (e) {
                /* ignore */
            }
        },

        fetchSources() {
            Nova.request()
                .get("/nova-vendor/nova-card-rss-news/sources")
                .then((response) => {
                    this.categories = response.data.categories || [];
                })
                .catch(() => {
                    this.categories = [];
                });
        },

        onSourceChange(value) {
            if (!value || value === this.selectedSource) {
                return;
            }
            this.selectedSource = value;
            this.persistSource(value);
            this.fetchRssFeed();
        },

        fetchRssFeed(forceRefresh = false) {
            const limit = this.card.limit || 10;
            const sourceKey = this.selectedSource;

            this.loading = true;
            this.faviconFailed = false;
            this.feedNews = [];

            const url = `/nova-vendor/nova-card-rss-news/news?source_key=${sourceKey}${
                forceRefresh ? `&_=${Date.now()}` : ""
            }`;

            Nova.request()
                .get(url)
                .then((response) => {
                    if (response.data && response.data.feed) {
                        this.feedTitle = response.data.title;
                        this.feedUrl = response.data.url || null;
                        this.feedNews = (response.data.feed || []).slice(
                            0,
                            limit
                        );
                        this.lastUpdatedAt = Date.now();
                    } else {
                        this.feedTitle = "Feed non disponibile";
                        this.feedNews = [];
                    }
                })
                .catch(() => {
                    this.feedTitle = "Feed non disponibile";
                    this.feedNews = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        formatRelative(input) {
            if (!input) {
                return "";
            }
            const ts = new Date(input).getTime();
            if (Number.isNaN(ts)) {
                return "";
            }
            const diff = Math.max(0, this.now - ts);
            const sec = Math.floor(diff / 1000);
            if (sec < 45) {
                return "ora";
            }
            const min = Math.floor(sec / 60);
            if (min < 60) {
                return `${min} min fa`;
            }
            const hr = Math.floor(min / 60);
            if (hr < 24) {
                return `${hr} h fa`;
            }
            const day = Math.floor(hr / 24);
            if (day < 7) {
                return `${day} g fa`;
            }
            const wk = Math.floor(day / 7);
            if (wk < 5) {
                return `${wk} sett fa`;
            }
            const mo = Math.floor(day / 30);
            if (mo < 12) {
                return `${mo} mesi fa`;
            }
            const yr = Math.floor(day / 365);
            return `${yr} a fa`;
        },

        formatAbsolute(input) {
            if (!input) {
                return "";
            }
            const d = new Date(input);
            if (Number.isNaN(d.getTime())) {
                return "";
            }
            const pad = (n) => n.toString().padStart(2, "0");
            return `${pad(d.getDate())}/${pad(
                d.getMonth() + 1
            )}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
        },
    },
};
</script>
