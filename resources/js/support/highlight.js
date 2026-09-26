const ESCAPES = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
};

export function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, (char) => ESCAPES[char]);
}

/**
 * Escape first, then wrap matches — feed titles are remote content, so the
 * only markup that may reach v-html is the <mark> we add ourselves.
 */
export function highlight(value, term) {
    const escaped = escapeHtml(value);
    const needle = String(term ?? "").trim();

    if (needle.length < 2) {
        return escaped;
    }

    const pattern = new RegExp(
        `(${needle.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")})`,
        "gi",
    );

    return escaped.replace(pattern, "<mark>$1</mark>");
}

export function matches(item, term) {
    const needle = String(term ?? "")
        .trim()
        .toLowerCase();

    if (needle === "") {
        return true;
    }

    return (
        `${item.title ?? ""} ${item.summary ?? ""} ${item.source_title ?? ""}`
            .toLowerCase()
            .indexOf(needle) !== -1
    );
}
