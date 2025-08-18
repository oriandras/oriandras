# Oriandras WordPress

Local WordPress install with a custom theme using Tailwind CSS.

## Theme: oriandras

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
