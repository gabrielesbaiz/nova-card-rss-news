<template>
    <div class="nova-tw">
        <Card class="flex flex-col h-full">
            <div class="px-6 py-4">
                <h3 class="mb-4 font-bold text-gray-500">Motor1.com news</h3>
                <div class="mb-1 max-h-180px overflow-hidden overflow-y-auto">
                    <div
                        v-for="news in feedNews"
                        v-bind:key="news.id"
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

    data: () => {
        return {
            feedNews: [],
        };
    },

    mounted() {
        Nova.request()
            .get("/nova-vendor/nova-card-rss-news/news")
            .then((response) => {
                this.feedNews = JSON.parse(response.data).channel.item.slice(
                    0,
                    10
                );
            });
    },

    methods: {
        formatDate: function (date) {
            var date = new Date(date);
            var day = date.getDate();
            var month = date.getMonth() + 1;
            if (month < 10) {
                month = "0" + month;
            }
            var year = date.getFullYear();
            var hours = date.getHours();
            var minutes = date.getMinutes();
            if (minutes < 10) {
                minutes = "0" + minutes;
            }

            return day + "/" + month + "/" + year + " " + hours + ":" + minutes;
        },
    },
};
</script>
