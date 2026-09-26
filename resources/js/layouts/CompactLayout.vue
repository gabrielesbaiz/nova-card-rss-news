<template>
    <ul class="rss-news-list is-compact">
        <FeedItemRow
            v-for="(item, index) in items"
            :key="item.id"
            :item="item"
            :delay="index * 40"
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
</template>

<script setup>
import FeedItemRow from "../parts/FeedItemRow.vue";

defineProps({
    items: { type: Array, default: () => [] },
    isRead: { type: Function, default: () => false },
    isSaved: { type: Function, default: () => false },
    saveable: { type: Boolean, default: false },
    showImages: { type: Boolean, default: false },
    showSource: { type: Boolean, default: false },
    term: { type: String, default: "" },
});

defineEmits(["read", "save"]);
</script>
