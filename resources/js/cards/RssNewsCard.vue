<template>
    <FeedCardShell :card="card" :feed="feed" :storage-key="storageKey" />
</template>

<script setup>
import { computed, onMounted } from "vue";
import FeedCardShell from "../parts/FeedCardShell.vue";
import { useFeed } from "../composables/useFeed";

const props = defineProps({ card: { type: Object, required: true } });

const feed = useFeed("feed", () => ({
    source: props.card.source_key,
    feed: props.card.feed,
    limit: props.card.limit,
}));

const storageKey = computed(
    () => `${props.card.component}:${props.card.source_key ?? "inline"}`,
);

onMounted(() => feed.load());
</script>
