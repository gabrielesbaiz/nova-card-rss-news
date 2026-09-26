<template>
    <FeedCardShell
        :card="cardWithHeading"
        :feed="feed"
        :storage-key="storageKey"
        show-source
    />
</template>

<script setup>
import { computed, onMounted } from "vue";
import FeedCardShell from "../parts/FeedCardShell.vue";
import { useFeed } from "../composables/useFeed";
import { __ } from "../composables/useTranslations";

const props = defineProps({ card: { type: Object, required: true } });

const feed = useFeed("stream", () => ({
    sources: props.card.sources ?? [],
    categories: props.card.categories ?? [],
    limit: props.card.limit,
}));

const cardWithHeading = computed(() => ({
    ...props.card,
    heading: props.card.heading ?? __("All sources"),
}));

const storageKey = computed(
    () =>
        `${props.card.component}:${(props.card.sources ?? []).join(",")}:${(
            props.card.categories ?? []
        ).join(",")}`,
);

onMounted(() => feed.load());
</script>
