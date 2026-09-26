/**
 * Nova exposes __() globally once booted; fall back to the raw key (with
 * replacements applied) so components stay usable in isolation.
 */
export function __(key, replacements = {}) {
    if (typeof Nova !== "undefined" && typeof Nova.__ === "function") {
        return Nova.__(key, replacements);
    }

    return Object.entries(replacements).reduce(
        (line, [token, value]) => line.replace(`:${token}`, value),
        key,
    );
}

/**
 * Pick the right side of a "singular|plural" translation.
 */
export function choice(key, count) {
    const line = __(key, { count });
    const [singular, plural] = line.split("|");

    return count === 1 ? singular : (plural ?? singular);
}

export function novaLocale() {
    if (typeof Nova !== "undefined" && typeof Nova.config === "function") {
        return Nova.config("locale")?.replace("_", "-") ?? undefined;
    }

    return typeof document !== "undefined"
        ? (document.documentElement.lang || undefined)
        : undefined;
}
