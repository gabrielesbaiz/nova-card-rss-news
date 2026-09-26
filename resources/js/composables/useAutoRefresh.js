import { onMounted, onUnmounted, ref } from "vue";

/**
 * Re-fetch on an interval, but never while the tab is hidden or while the
 * pointer is over the card (nothing is more annoying than a list that
 * reshuffles mid-read).
 */
export function useAutoRefresh(seconds, callback) {
    const paused = ref(false);
    let handle = null;

    function tick() {
        if (paused.value || document.hidden) {
            return;
        }

        callback();
    }

    function start() {
        if (!seconds || seconds <= 0 || handle !== null) {
            return;
        }

        handle = setInterval(tick, seconds * 1000);
    }

    function stop() {
        if (handle !== null) {
            clearInterval(handle);
            handle = null;
        }
    }

    function onVisibilityChange() {
        document.hidden ? stop() : start();
    }

    onMounted(() => {
        start();
        document.addEventListener("visibilitychange", onVisibilityChange);
    });

    onUnmounted(() => {
        stop();
        document.removeEventListener("visibilitychange", onVisibilityChange);
    });

    return {
        paused,
        pause: () => (paused.value = true),
        resume: () => (paused.value = false),
    };
}
