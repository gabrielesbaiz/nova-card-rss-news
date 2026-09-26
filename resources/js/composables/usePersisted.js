import { ref, watch } from "vue";

/**
 * A ref mirrored into localStorage, tolerant of private-mode failures.
 */
export function usePersisted(key, fallback) {
    const read = () => {
        try {
            const raw = window.localStorage.getItem(key);

            return raw === null ? fallback : JSON.parse(raw);
        } catch (e) {
            return fallback;
        }
    };

    const state = ref(read());

    watch(
        state,
        (value) => {
            try {
                window.localStorage.setItem(key, JSON.stringify(value));
            } catch (e) {
                /* storage full or unavailable — keep working in memory */
            }
        },
        { deep: true },
    );

    return state;
}
