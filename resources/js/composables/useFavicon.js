import { computed, ref } from "vue";

/**
 * Site icon for the feed. 'google' routes through Google's S2 service, which
 * tells Google which feeds the dashboard reads — so it is opt-in, and every
 * other mode falls back to initials drawn locally.
 */
export function useFavicon(siteUrl, title, mode = "none") {
    const failed = ref(false);

    const url = computed(() => {
        if (mode !== "google" || failed.value || !siteUrl.value) {
            return null;
        }

        try {
            const host = new URL(siteUrl.value).hostname;

            return `https://www.google.com/s2/favicons?domain=${host}&sz=64`;
        } catch (e) {
            return null;
        }
    });

    const initials = computed(() =>
        (title.value || "?")
            .replace(/news/gi, "")
            .trim()
            .split(/\s+/)
            .map((word) => word[0])
            .filter(Boolean)
            .slice(0, 2)
            .join("")
            .toUpperCase(),
    );

    return { url, initials, onError: () => (failed.value = true) };
}
