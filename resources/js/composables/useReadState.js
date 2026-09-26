import { computed } from "vue";
import { usePersisted } from "./usePersisted";

const PREFIX = "nova-card-rss-news:read:";
const MAX_TRACKED = 400;

/**
 * Remembers which items this browser has already seen, so the card can dim
 * read entries and badge how many are new since the last visit.
 */
export function useReadState(storageKey, enabled = true) {
    const seen = usePersisted(`${PREFIX}${storageKey}`, []);
    const bookmarks = usePersisted(`${PREFIX}${storageKey}:saved`, []);

    const seenSet = computed(() => new Set(seen.value));

    function isRead(item) {
        return enabled && seenSet.value.has(item.id);
    }

    function isSaved(item) {
        return bookmarks.value.includes(item.id);
    }

    function markRead(item) {
        if (!enabled || seenSet.value.has(item.id)) {
            return;
        }

        // Keep the list bounded: feeds churn, ancient ids are dead weight.
        seen.value = [item.id, ...seen.value].slice(0, MAX_TRACKED);
    }

    function markAllRead(items) {
        if (!enabled) {
            return;
        }

        const ids = items.map((item) => item.id);

        seen.value = [...new Set([...ids, ...seen.value])].slice(0, MAX_TRACKED);
    }

    function toggleSaved(item) {
        bookmarks.value = isSaved(item)
            ? bookmarks.value.filter((id) => id !== item.id)
            : [item.id, ...bookmarks.value];
    }

    function unreadCount(items) {
        return enabled
            ? items.filter((item) => !seenSet.value.has(item.id)).length
            : 0;
    }

    return {
        isRead,
        isSaved,
        markRead,
        markAllRead,
        toggleSaved,
        unreadCount,
    };
}
