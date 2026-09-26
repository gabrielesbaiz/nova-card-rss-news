import { onUnmounted, ref } from "vue";
import { novaLocale } from "./useTranslations";

/**
 * One ticker for the whole page. Every card used to run its own 30s interval;
 * they now share this single clock, started with the first subscriber and
 * stopped with the last.
 */
const now = ref(Date.now());
let handle = null;
let subscribers = 0;

const TICK_MS = 30000;

function subscribe() {
    subscribers += 1;

    if (handle === null) {
        handle = setInterval(() => {
            now.value = Date.now();
        }, TICK_MS);
    }
}

function unsubscribe() {
    subscribers = Math.max(0, subscribers - 1);

    if (subscribers === 0 && handle !== null) {
        clearInterval(handle);
        handle = null;
    }
}

const UNITS = [
    ["year", 31536000],
    ["month", 2592000],
    ["week", 604800],
    ["day", 86400],
    ["hour", 3600],
    ["minute", 60],
];

export function useRelativeTime() {
    subscribe();
    onUnmounted(unsubscribe);

    const locale = novaLocale();

    const relative = new Intl.RelativeTimeFormat(locale, {
        numeric: "auto",
        style: "short",
    });

    const absolute = new Intl.DateTimeFormat(locale, {
        dateStyle: "medium",
        timeStyle: "short",
    });

    /**
     * "5 min ago" in the viewer's locale, recomputed whenever the clock ticks.
     */
    function formatRelative(input) {
        if (!input) {
            return "";
        }

        const timestamp = new Date(input).getTime();

        if (Number.isNaN(timestamp)) {
            return "";
        }

        const seconds = Math.round((timestamp - now.value) / 1000);
        const magnitude = Math.abs(seconds);

        if (magnitude < 45) {
            return relative.format(0, "second");
        }

        for (const [unit, size] of UNITS) {
            if (magnitude >= size) {
                return relative.format(Math.round(seconds / size), unit);
            }
        }

        return relative.format(Math.round(seconds / 60), "minute");
    }

    function formatAbsolute(input) {
        if (!input) {
            return "";
        }

        const date = new Date(input);

        return Number.isNaN(date.getTime()) ? "" : absolute.format(date);
    }

    return { now, formatRelative, formatAbsolute };
}
