# Oriandras WordPress

Local WordPress install with a custom theme using Tailwind CSS.

## Theme: LookOut

Location: `wp-content/themes/oriandras`

### Frontend (Tailwind) workflow

Prerequisites: Node.js 18+ and npm

Commands (run inside the theme directory):

- Install deps:
  - `cd wp-content/themes/oriandras`
  - `npm install`
- Start development build (watch):
  - `npm run dev`
- Production build (minified):
  - `npm run build`

The compiled CSS is written to `wp-content/themes/oriandras/dist/app.css` and is automatically enqueued by the theme.

### Tailwind UI
You can paste Tailwind UI components into your PHP templates (e.g., header.php, index.php). The config includes official plugins (typography, forms, aspect-ratio) commonly used by Tailwind UI.


### Theme description
LookOut is a Tailwind CSS powered WordPress theme with customizable color palette, mobile-friendly navigation, and editor enhancements for modern blogging.

### New functions in the theme
- Customizer color controls for main, accent, body/header/nav/footer background and text colors. The theme outputs CSS variables into the head so you can quickly theme the site without editing CSS.
- Header Visibility metabox on posts and pages that allows hiding the header block (title, author, dates) per-entry.
- Smart asset enqueuing with cache-busting for `/dist/app.css` and the mobile navigation script.
- Admin info notice if required plugins are inactive (see below).

### Required plugins
The LookOut theme expects the following plugins to be active. If they are not active, an informational notice will appear in wp-admin for users who can activate plugins.

1. Same Day Archive (Previous Years)
   - Folder: `wp-content/plugins/same-day-archive`
   - Main file: `same-day-archive.php`
   - Provides the `[same_day_archive]` shortcode and PHP helpers to display “On this day” archives.
2. Oriandras – Stale Content Alert
   - Folder: `wp-content/plugins/oriandras-stale-content-alert`
   - Main file: `oriandras-stale-content-alert.php`
   - Shows an informational banner on old content (configurable threshold).
3. Oriandras – Featured Video
   - Folder: `wp-content/plugins/oriandras-featured-video`
   - Main file: `oriandras-featured-video.php`
   - Replaces featured image with a YouTube or Media Library video when configured.

Activation:
- Go to wp-admin → Plugins and activate these plugins. The theme will show an info notice if any is inactive.

