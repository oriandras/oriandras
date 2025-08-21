<?php
/**
 * Custom Navigation Walker for Oriandras theme.
 *
 * Provides enhanced markup for WordPress menus with:
 * - Utility CSS classes (Tailwind) for layout and styling.
 * - ARIA attributes for accessibility (dropdown disclosure semantics).
 * - Data attributes to indicate hierarchical state (e.g., has-grandchildren) used
 *   by CSS/JS to render normal dropdowns or mega-menu variants.
 *
 * Intended to be used as the walker argument to wp_nav_menu() when rendering the
 * primary navigation.
 *
 * Example:
 * wp_nav_menu([
 *     'theme_location' => 'primary',
 *     'walker'         => new Oriandras_Nav_Walker(),
 * ]);
 *
 * @package Oriandras\Theme
 * @since 1.0.0
 */

/**
 * Oriandras Nav Walker extending core Walker_Nav_Menu.
 *
 * This class overrides several Walker methods to inject:
 * - has_children bookkeeping (WordPress convention via $args->has_children)
 * - depth-specific class names (e.g., group, relative for top-level items)
 * - submenu container classes and proper role attributes
 * - additional indicators for items with grandchildren
 *
 * No business logic beyond markup and attribute shaping is performed here.
 *
 * @see Walker_Nav_Menu
 */
class Oriandras_Nav_Walker extends Walker_Nav_Menu {
    /**
     * Map of element IDs that have grandchildren.
     *
     * When a depth-0 element has at least one child that itself has children,
     * we mark the top-level element ID in this map. This allows start_el() to
     * emit a data-has-grandchildren attribute and class for styling (e.g., mega menus).
     *
     * @var array<int,bool> associative array keyed by menu item ID with boolean true
     */
    protected $elements_with_grandchildren = [];

    /**
     * Flag indicating that the next depth-0 start_lvl call belongs to a
     * top-level item that has grandchildren (mega-menu). Used to optionally
     * inject a widget column as the first child of the mega submenu.
     *
     * @var bool
     */
    protected $inject_widget_for_next_submenu = false;

    /**
     * Populate has_children flag and detect grandchildren for top-level items.
     *
     * WordPress walkers commonly set $args[0]->has_children to communicate to
     * start_el() whether the current element has children. Here we also scan
     * one level deeper to determine if a top-level item has grandchildren so we
     * can add a data-has-grandchildren attribute for styling/behavior (e.g., mega-menu).
     *
     * @param mixed                $element            The current menu element (object). Usually a WP_Post representing a nav_menu_item.
     * @param array<int,array>     $children_elements  Children grouped by parent item ID.
     * @param int                  $max_depth          Max depth of traversal.
     * @param int                  $depth              Current depth.
     * @param array<int,object>    $args               An array with the first item being the arguments object passed to wp_nav_menu().
     * @param string               $output             Output buffer passed by reference.
     *
     * @return void
     */
    public function display_element($element, &$children_elements, $max_depth, $depth = 0, $args = [], &$output = '') {
        if (!$element) return;

        // Set has_children flag on args (WordPress convention for walkers)
        $id_field = $this->db_fields['id'];
        $element_id = $element->{$id_field};
        $has_children = !empty($children_elements[$element_id]);
        if (!empty($args[0])) {
            $args[0]->has_children = $has_children;
        }

        // If this element has children, check if any of those children have their own children
        if ($has_children) {
            foreach ((array) $children_elements[$element_id] as $child_el) {
                $child_id = $child_el->{$id_field};
                if (!empty($children_elements[$child_id])) {
                    // mark current element as having grandchildren
                    $this->elements_with_grandchildren[$element_id] = true;
                    break;
                }
            }
        }

        parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
    }

    /**
     * Start the list before the elements are added.
     *
     * Renders the opening <ul> for a submenu with Tailwind classes and
     * assigns role="menu" for accessibility. Top-level submenus get a min width
     * and spacing; deeper levels reuse the padding and shared classes.
     *
     * @param string   $output Used to append additional content. Passed by reference.
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     *
     * @return void
     */
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $submenu_classes = ['sub-menu', 'absolute', 'z-40', 'mt-2', 'rounded-lg', 'bg-white', 'shadow-lg', 'ring-1', 'ring-black/5', 'hidden', 'group-focus-within:block'];
        $inject_widget = false;
        if ($depth === 0) {
            // first dropdown level defaults to normal dropdown; mega-menu handled via parent li data attribute
            $submenu_classes[] = 'min-w-[12rem]';
            $submenu_classes[] = 'py-2';
            if ($this->inject_widget_for_next_submenu && function_exists('is_active_sidebar') && is_active_sidebar('mega-menu')) {
                // Ensure grid layout is available even without JS
                $submenu_classes[] = 'grid';
                $submenu_classes[] = 'grid-cols-2';
                $submenu_classes[] = 'md:grid-cols-3';
                $inject_widget = true;
            }
        } else {
            $submenu_classes[] = 'py-2';
        }
        $class_str = implode(' ', $submenu_classes);
        $output .= "\n{$indent}<ul class=\"{$class_str}\" role=\"menu\">\n";

        // Inject widget area as first column in mega-menu when active
        if ($depth === 0) {
            if ($inject_widget) {
                $output .= "{$indent}\t<li class=\"mega-widget col-span-1\">";
                // Capture dynamic_sidebar output
                ob_start();
                dynamic_sidebar('mega-menu');
                $widget_html = trim(ob_get_clean());
                $output .= $widget_html;
                $output .= "</li>\n";
            }
            // Reset flag after handling top-level submenu
            $this->inject_widget_for_next_submenu = false;
        }
    }

    /**
     * End the list of after the elements are added.
     *
     * Closes the submenu <ul> container.
     *
     * @param string   $output Used to append additional content. Passed by reference.
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     *
     * @return void
     */
    public function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "{$indent}</ul>\n";
    }

    /**
     * Start the element output.
     *
     * Adds classes indicating depth and presence of children, sets ARIA
     * attributes when a submenu is present, and outputs the anchor with
     * Tailwind utility classes. Injects an SVG chevron indicator when the item
     * has children.
     *
     * Applies the 'walker_nav_menu_start_el' filter per WordPress core.
     *
     * @param string        $output Used to append additional content. Passed by reference.
     * @param WP_Post       $item   Menu item data object.
     * @param int           $depth  Depth of menu item. Used for padding.
     * @param stdClass|null $args   An object of wp_nav_menu() arguments.
     * @param int           $id     Current item ID.
     *
     * @return void
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes[] = 'menu-item';
        $classes[] = 'depth-' . $depth;

        $has_children = !empty($args->has_children);
        if ($has_children) {
            $classes[] = 'has-children';
        }

        // mark if this top-level item has grandchildren
        $has_grandchildren = false;
        if ($depth === 0) {
            $id_field = $this->db_fields['id'];
            $element_id = $item->{$id_field};
            $has_grandchildren = !empty($this->elements_with_grandchildren[$element_id]);
            if ($has_grandchildren) {
                $classes[] = 'has-grandchildren';
            }
            // Prepare to inject widget column for the upcoming top-level submenu if mega-menu
            $this->inject_widget_for_next_submenu = $has_grandchildren;
        }

        // Tailwind group to control submenu visibility on hover/focus for desktop
        if ($depth === 0) {
            $classes[] = 'group';
            $classes[] = 'relative';
        }

        $class_names = join(' ', array_map('esc_attr', array_filter($classes)));
        $attrs = [];
        $attrs['id'] = 'menu-item-' . $item->ID;
        $attrs['class'] = $class_names;
        if ($depth === 0 && $has_grandchildren) {
            $attrs['data-has-grandchildren'] = '1';
        }

        $attr_str = '';
        foreach ($attrs as $key => $val) {
            $attr_str .= ' ' . $key . '="' . $val . '"';
        }

        $output .= '<li' . $attr_str . '>';

        $title = apply_filters('the_title', $item->title, $item->ID);
        $item_output  = isset($args->before) ? $args->before : '';

        if ($has_children) {
            // Parent items are toggles: render as button without href
            $btn = [];
            $btn['type'] = 'button';
            $btn['class'] = 'inline-flex items-center gap-1 py-2 text-sm';
            $btn['aria-haspopup'] = 'true';
            $btn['aria-expanded'] = 'false';
            $btn_attrs = '';
            foreach ($btn as $attr => $value) {
                if ($value !== '') {
                    $btn_attrs .= ' ' . $attr . '="' . esc_attr($value) . '"';
                }
            }
            $item_output .= '<button' . $btn_attrs . '>';            
            $item_output .= isset($args->link_before) ? $args->link_before : '';
            $item_output .= '<span>' . $title . '</span>';
            $item_output .= '<svg class="h-3 w-3 opacity-70" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>';
            $item_output .= isset($args->link_after) ? $args->link_after : '';
            $item_output .= '</button>';
        } else {
            // Leaf items are normal links
            $atts = [];
            $atts['href'] = !empty($item->url) ? $item->url : '';
            $atts['class'] = 'inline-flex items-center gap-1 py-2 text-sm';
            $atts['role'] = 'menuitem';
            $attributes = '';
            foreach ($atts as $attr => $value) {
                if (!empty($value)) {
                    $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                    $attributes .= ' ' . $attr . '="' . $value . '"';
                }
            }
            $item_output .= '<a' . $attributes . '>';
            $item_output .= isset($args->link_before) ? $args->link_before : '';
            $item_output .= '<span>' . $title . '</span>';
            $item_output .= isset($args->link_after) ? $args->link_after : '';
            $item_output .= '</a>';
        }

        $item_output .= isset($args->after) ? $args->after : '';

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    /**
     * Ends the element output, if needed.
     *
     * Closes the list item element.
     *
     * @param string        $output Used to append additional content. Passed by reference.
     * @param WP_Post       $item   Page data object. Not used.
     * @param int           $depth  Depth of page. Not used.
     * @param stdClass|null $args   An object of wp_nav_menu() arguments.
     *
     * @return void
     */
    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
}
