import { computed, onUnmounted, ref } from "vue";
import { __ } from "./useTranslations";

const BASE = "/nova-vendor/nova-card-rss-news";

/**
 * The single fetch implementation behind every card.
 *
 * `params` is a getter so a card can change source, limit or layout without
 * re-creating the composable. In-flight requests are aborted when a newer one
 * starts, which is what used to let a slow response overwrite a newer feed.
 */
export function useFeed(endpoint, params) {
    const items = ref([]);
    const title = ref(__("Loading…"));
    const siteUrl = ref(null);
    const feedUrl = ref(null);
    const fetchedAt = ref(null);
    const stale = ref(false);
    const loading = ref(true);
    const error = ref(null);

    let controller = null;

    const isEmpty = computed(
        () => !loading.value && !error.value && items.value.length === 0,
    );

    function query(fresh) {
        const search = new URLSearchParams();
        const current = params() ?? {};

        Object.entries(current).forEach(([key, value]) => {
            if (value === null || value === undefined || value === "") {
                return;
            }

            if (Array.isArray(value)) {
                value.forEach((entry) => search.append(`${key}[]`, entry));

                return;
            }

            search.append(key, value);
        });

        if (fresh) {
            search.append("fresh", "1");
        }

        return search.toString();
    }

    async function load({ fresh = false, silent = false } = {}) {
        controller?.abort();
        controller = new AbortController();

        if (!silent) {
            loading.value = true;
        }

        error.value = null;

        try {
            const response = await Nova.request().get(
                `${BASE}/${endpoint}?${query(fresh)}`,
                { signal: controller.signal },
            );

            const data = response.data ?? {};

            items.value = data.items ?? [];
            title.value = data.title ?? __("Feed unavailable");
            siteUrl.value = data.url ?? null;
            feedUrl.value = data.feed_url ?? null;
            fetchedAt.value = data.fetched_at ?? new Date().toISOString();
            stale.value = Boolean(data.stale);
        } catch (e) {
            // An aborted request was superseded on purpose: leave state alone.
            if (e?.code === "ERR_CANCELED" || e?.name === "CanceledError") {
                return;
            }

            const status = e?.response?.status;

            error.value =
                status === 429
                    ? __("Too many refreshes. Try again shortly.")
                    : (e?.response?.data?.message ?? __("Feed unavailable"));

            if (!silent) {
                items.value = [];
                title.value = __("Feed unavailable");
            }
        } finally {
            loading.value = false;
        }
    }

    onUnmounted(() => controller?.abort());

    return {
        items,
        title,
        siteUrl,
        feedUrl,
        fetchedAt,
        stale,
        loading,
        error,
        isEmpty,
        load,
        refresh: () => load({ fresh: true }),
    };
}
