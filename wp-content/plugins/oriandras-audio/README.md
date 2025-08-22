# Oriandras – Audio & Podcast Playlist

Shortcode: `[oriandras-audio]`

Features:
- Uses audio from the Media Library or external URLs
- Playlist creation via custom post type “Audio Tracks” (admin add/edit)
- Categories and Tags enabled for tracks
- Title, description, URL for each track
- Optional Call-To-Action (CTA) button per track
- WCAG-friendly markup and keyboard support
- Controls: start, stop, pause, next/previous
- Start from a specific time (e.g., start="90" or start="1:30") or per track from admin

Attributes:
- ids: comma-separated track IDs to include (optional)
- category: category slug(s) to filter by (optional)
- tag: tag slug(s) to filter by (optional)
- items: number of tracks to show (default 10)
- start: global start position for all tracks (e.g., 90, 1:30, 00:01:30) (optional)
- autoplay: true|false (default false)
- show_cta: true|false show the CTA area (default true)
- class: extra CSS class(es)
- layout: playlist|card choose between the classic playlist or card widgets (default: playlist)

Examples:
1. Latest 5 tracks from all categories
```
[oriandras-audio items="5"]
```

2. Specific tracks in order, start at 90 seconds
```
[oriandras-audio ids="123,150,182" start="90"]
```

3. Filter by category and tag, with autoplay
```
[oriandras-audio category="podcast" tag="season-1" autoplay="true"]
```

4. Render as cards
```
[oriandras-audio items="6" layout="card"]
```

Notes:
- To create a playlist, add “Audio Tracks” in the admin. Provide either an external Audio URL or choose one from the Media Library.
- Each track can set its own start time. The shortcode `start` acts as a fallback.
- Accessibility: Track list is keyboard navigable (Arrow Up/Down, Enter/Space to play). Controls have labels, active track is marked with aria-current, and updates are announced in a polite live region.
- No external dependencies.
