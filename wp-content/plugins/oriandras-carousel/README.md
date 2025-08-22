# Oriandras – Content Carousel

Shortcode: `[oriandras-carousel]`

Attributes:
- width: CSS width (default `100%`)
- height: CSS height (default `320px`)
- items: number of posts to include (default `6`)
- dots: `true|false` show navigation dots (default `true`)
- arrows: `true|false` show side arrows (default `true`)
- show_title: `true|false` show the post/page title overlay (default `true`)
- post_type: comma-separated post types (default: all public types)
- orderby: WP_Query orderby (default `date`)
- order: `ASC|DESC` (default `DESC`)

Examples:

1. Latest 5 items across all types, 400px tall with dots and arrows

```
[oriandras-carousel items="5" height="400px" dots="true" arrows="true"]
```

2. Only posts and pages, 1200px wide x 300px tall, without titles

```
[oriandras-carousel post_type="post,page" width="1200px" height="300px" show_title="false"]
```

3. Hide dots and arrows (manual swipe/keyboard only)

```
[oriandras-carousel dots="false" arrows="false"]
```

Notes:
- Only items that have a featured image (cover) are shown. Items without a cover are skipped and older content is included to fill the requested number of items.
- Accessibility: WCAG-friendly markup and behavior — slides get proper labels (e.g., "Slide X of Y"), inactive slides are aria-hidden and untabbable, live region announces slide changes, controls have aria-controls, and keyboard navigation with Arrow keys works when the carousel has focus.
- Colors: Dots and arrow controls use the theme accent color (CSS var `--ori-accent`) with safe fallbacks.
- No external dependencies.
