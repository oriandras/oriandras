# Oriandras – Featured Video

Use a video (YouTube or Media Library) as a replacement for the featured image.

## Features
- Meta box on posts/pages to set a YouTube URL or pick a video from the Media Library.
- If set, the video is output wherever the theme renders the featured image (via WordPress filters).
- If a theme checks `has_post_thumbnail()` before rendering, the plugin returns `true` when a featured video is present so it still displays.
- Shortcode: `[ori_featured_video]` to render the video manually in content/templates.

## Usage
1. Activate the plugin in wp-admin → Plugins.
2. Edit a post or page and find the "Featured Video" meta box in the sidebar.
3. Enter a YouTube URL or click "Select video" to choose a media library video. If both are set, YouTube is used.
4. View the post on the front-end. The video will appear in place of the featured image.

## Notes
- For Media Library videos, the plugin will try to use the post's featured image as the video's poster (thumbnail) if available.
- Basic, minimal CSS is injected to ensure embeds are responsive by default.
