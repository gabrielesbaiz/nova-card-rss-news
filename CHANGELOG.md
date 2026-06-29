# Changelog

All notable changes to `nova-card-rss-news` will be documented in this file.

## 2.0.0 - 2026-06-29

Full UI/UX redesign of the card.

- Branded radial-gradient background driven by Nova's `--colors-primary-500` CSS variable (auto-adapts to host app's primary colour).
- Header strip: source favicon (Google S2), feed title, pulsing live indicator, manual refresh button.
- First news item rendered as a "hero" with badge, shine-on-hover sweep, lift + glow on hover.
- Remaining items in a compact list with hover-translate chevron, primary-tinted hover background, and 2-line title clamp.
- Italian relative time labels ("5 min fa", "2 h fa", "3 g fa") with absolute date in tooltip; updates every 30s.
- Skeleton shimmer loader while fetching; empty-state icon + message.
- Stagger fade-in animation on items.
- Custom thin primary-tinted scrollbar.
