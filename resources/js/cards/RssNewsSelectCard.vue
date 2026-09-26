<template>
    <FeedCardShell :card="card" :feed="feed" :storage-key="storageKey">
        <template #title>
            <SourceSelect
                v-model="selected"
                @reload="loadSources"
                :categories="categories"
                :disabled="feed.loading.value"
                :fallback-label="feed.title.value"
            />
        </template>
    </FeedCardShell>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import FeedCardShell from "../parts/FeedCardShell.vue";
import SourceSelect from "../parts/SourceSelect.vue";
import { useFeed } from "../composables/useFeed";
import { usePersisted } from "../composables/usePersisted";

const props = defineProps({ card: { type: Object, required: true } });

const fallback = props.card.source_key ?? null;

// Keyed by component AND default source so two pickers on one dashboard keep
// independent selections (the collision fixed in 2.3.2).
const storageKey = computed(
    () => `${props.card.component}:${fallback ?? "default"}`,
);

const remembered = usePersisted(
    `nova-card-rss-news:source:${storageKey.value}`,
    fallback,
);

const selected = ref(
    props.card.remember === false ? fallback : (remembered.value ?? fallback),
);

const categories = ref([]);

const feed = useFeed("feed", () => ({
    source: selected.value,
    limit: props.card.limit,
}));

watch(selected, (value) => {
    if (props.card.remember !== false) {
        remembered.value = value;
    }

    feed.load();
});

async function loadSources() {
    try {
        const params = (props.card.categories ?? [])
            .map((category) => `categories[]=${encodeURIComponent(category)}`)
            .join("&");

        const response = await Nova.request().get(
            `/nova-vendor/nova-card-rss-news/sources${params ? `?${params}` : ""}`,
        );

        categories.value = response.data?.categories ?? [];
    } catch (e) {
        categories.value = [];
    }
}

onMounted(() => {
    loadSources();
    feed.load();
});
</script>
