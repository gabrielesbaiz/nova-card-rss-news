<template>
    <div class="nova-tw">
        <Card class="flex flex-col h-full">
            <div class="px-6 py-4">
                <h3 class="mb-4 font-bold text-gray-500">{{ feedTitle }}</h3>
                <div class="mb-1 max-h-180px overflow-hidden overflow-y-auto">
                    <div
                        v-for="news in feedNews"
                        :key="news.link"
                        class="rounded overflow-hidden"
                    >
                        <div class="mb-4">
                            <a
                                class="cursor-pointer text-primary dim no-underline font-bold"
                                target="_blank"
                                :href="news.link"
                            >
                                {{ news.title }}
                            </a>
                            <p class="pt-1 text-sm">
                                {{ news.description }}
                            </p>
                            <p
                                class="mt-3 text-center text-grey-darker text-xs"
                            >
                                {{ formatDate(news.pubDate) }}
                            </p>
                            <hr />
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    </div>
</template>

<script>
export default {
    props: ["card"],

    data() {
        return {
            feedTitle: "Loading...",
            feedNews: [],
        };
    },

    mounted() {
        this.fetchRssFeed();
    },

    methods: {
        fetchRssFeed() {
            const sourceKey = this.card.source_key || "motor1"; // Default source if not provided

            Nova.request()
                .get(
                    `/nova-vendor/nova-card-rss-news/news?source_key=${sourceKey}`
                )
                .then((response) => {
                    if (response.data && response.data.items) {
                        this.feedTitle = response.data.title;
                        this.feedNews = response.data.items.slice(0, 10);
                    } else {
                        this.feedTitle = "RSS Feed Not Found";
                    }
                })
                .catch(() => {
                    this.feedTitle = "Error Loading Feed";
                });
        },

        formatDate(date) {
            const d = new Date(date);
            const day = d.getDate();
            const month = (d.getMonth() + 1).toString().padStart(2, "0");
            const year = d.getFullYear();
            const hours = d.getHours();
            const minutes = d.getMinutes().toString().padStart(2, "0");

            return `${day}/${month}/${year} ${hours}:${minutes}`;
        },
    },
};
</script>
