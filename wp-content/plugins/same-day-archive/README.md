# Same Day Archive (Previous Years)

Lists entries whose publication month and day match a given date (defaults to "today" in the site timezone) from previous years only. Results are ordered by date (newest first by default) and the full content is displayed for each entry.

- Shortcode: `[same_day_archive]`
- PHP functions for theme templates: `ori_sameday_archive()` and `ori_sameday_archive_echo()`

## Why?

Great for "On this day" or anniversary-style archives that resurface past content published on the same month and day in earlier years.

## Installation

1. Copy the `same-day-archive` folder into your WordPress `wp-content/plugins/` directory.
2. Activate "Same Day Archive (Previous Years)" from the Plugins screen in wp-admin.

## Usage

### Shortcode (use in post/page content or widget)

Basic:

```
[same_day_archive]
```

This shows all published posts from previous years whose month and day match "today" (based on the site's timezone), ordered by date descending, and prints the full content for each.

With options:

```
[same_day_archive month="8" day="21" post_type="post,page" limit="20" order="DESC" orderby="date"]
```

Attributes:
- `month` (1–12): Optional. Defaults to the current month in the site timezone.
- `day` (1–31): Optional. Defaults to the current day in the site timezone.
- `post_type` (string or comma-separated list): Which post types to include. Default `post`. Examples: `post,page`, `my_custom_type`.
- `limit` (integer): How many items to show. `-1` shows all. Default `-1`.
- `order` (`ASC` or `DESC`): Sort order. Default `DESC` (newest first).
- `orderby` (string): Field to order by. Default `date`.

Notes:
- Only entries from years strictly before the current year are included.
- The full content is rendered using the standard `the_content` filters (so shortcodes and embeds inside posts will work).

### PHP functions (use in theme PHP files)

Return the HTML string:

```php
<?php echo ori_sameday_archive([
  'month' => 8,
  'day' => 21,
  'post_type' => ['post','page'],
  'limit' => -1,
  'order' => 'DESC',
  'orderby' => 'date',
]); ?>
```

Echo directly:

```php
<?php ori_sameday_archive_echo([
  // args are the same as above; you can omit all to default to today
]); ?>
```

### Filters (developers)

- `ori_sameday_query_args` — Filter the final WP_Query arguments before execution.
  ```php
  add_filter('ori_sameday_query_args', function($query_args, $input_args){
      // Example: also respect a meta query
      // $query_args['meta_query'][] = [ 'key' => 'featured', 'value' => '1' ];
      return $query_args;
  }, 10, 2);
  ```

- `ori_sameday_no_results_text` — Customize the text shown when no entries are found.
  ```php
  add_filter('ori_sameday_no_results_text', function($text){
      return 'No matching entries from previous years.';
  });
  ```

## Markup & CSS

The plugin outputs minimal, classed markup so you can style it in your theme:

- Wrapper: `.sda-archive`
- Each item: `.sda-item`
- Title link: `.sda-title`
- Meta wrapper: `.sda-meta`
- Date: `.sda-date`
- Content: `.sda-content`
- Empty state: `.sda-empty`

No CSS is included; style it within your theme as desired.

## Changelog

- 1.0.0 — Initial release.
