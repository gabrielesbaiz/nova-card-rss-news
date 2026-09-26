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
 * Pick a singular or plural key, then substitute :count locally.
 *
 * Nova's __() leaves ":count" alone when the key carries a "singular|plural"
 * pipe, so the card rendered ":count items". Two flat keys avoid the pipe, and
 * the replacement happens here whether or not a translation was found.
 */
export function choice(count, singularKey, pluralKey) {
    const line = __(count === 1 ? singularKey : pluralKey, { count });

    return line.replace(/:count/g, count);
}

export function novaLocale() {
    if (typeof Nova !== "undefined" && typeof Nova.config === "function") {
        return Nova.config("locale")?.replace("_", "-") ?? undefined;
    }

    return typeof document !== "undefined"
        ? (document.documentElement.lang || undefined)
        : undefined;
}
