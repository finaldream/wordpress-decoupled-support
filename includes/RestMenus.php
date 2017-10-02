<?php
/**
 * Register menu
 *
 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/
 */


/**
 * Class RestMenus
 */
class RestMenus
{
    const API_NAMESPACE = 'wp/v2';


    /**
     * Register menus route.
     * @return void
     */
    public function registerMenuRoutes()
    {

        register_rest_route(static::API_NAMESPACE, '/menus', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getMenus'],
            ]
        ]);

    }


    /**
     * Get menus.
     *
     * @return array All registered menus
     */
    public function getMenus($request)
    {

        $lang            = $request->get_param('lang');
        $locations       = get_nav_menu_locations();
        $registeredMenus = get_registered_nav_menus();

        $rest_menus = [
            'result' => [],
        ];

        if (isset($lang)) {
            do_action('wpml_switch_language', $lang);
        }

        foreach ($registeredMenus as $locationId => $label) {
            if (!isset($locations[$locationId])) {
                continue;
            }

            $menu = wp_get_nav_menu_object($locations[$locationId]);

            $rest_menus['result']['menus'][$locationId]                = [];
            $rest_menus['result']['menus'][$locationId]['ID']          = $menu->term_id;
            $rest_menus['result']['menus'][$locationId]['name']        = $menu->name;
            $rest_menus['result']['menus'][$locationId]['slug']        = $menu->slug;
            $rest_menus['result']['menus'][$locationId]['description'] = $menu->description;
            $rest_menus['result']['menus'][$locationId]['count']       = $menu->count;

            $rest_menus['result']['menus'][$locationId]['items'] = $this->getChildren($menu->slug);

        }

        return rest_ensure_response($rest_menus);
    }


    /**
     * Get all items of a single menu
     *
     * @param $id
     * @return array
     */
    public function getChildren($id)
    {

        $wp_menu_items = $id ? wp_get_nav_menu_items($id) : [];

        $rest_menu_items = [];

        foreach ($wp_menu_items as $item_object) {
            $rest_menu_items[] = $this->formatMenuItem($item_object);
        }

        $rest_menu_items = $this->nestedMenuItems($rest_menu_items, 0);

        return $rest_menu_items;
    }


    /**
     * Handle nested menu items.
     *
     * Given a flat array of menu items, split them into parent/child items
     * and recurse over them to return children nested in their parent.
     *
     * @param  $menu_items
     * @param  $parent
     * @return array
     */
    private function nestedMenuItems(&$menu_items, $parent = null)
    {

        $parents  = [];
        $children = [];

        // Separate menu_items into parents & children.
        array_map(function ($i) use ($parent, &$children, &$parents) {

            if ($i['id'] != $parent && $i['parent'] == $parent) {
                $parents[] = $i;
            } else {
                $children[] = $i;
            }
        }, $menu_items);

        foreach ($parents as &$parent) {

            if ($this->hasChildren($children, $parent['id'])) {
                $parent['children'] = $this->nestedMenuItems($children, $parent['id']);
            }
        }

        return $parents;
    }


    /**
     * Check if a collection of menu items contains an item that is the parent id of 'id'.
     *
     * @param  array $items
     * @param  int $id
     * @return array
     */
    private function hasChildren($items, $id)
    {

        return array_filter($items, function ($i) use ($id) {

            return $i['parent'] == $id;
        });
    }


    /**
     * Returns all child nav_menu_items under a specific parent.
     *
     * @param int $parent_id The parent nav_menu_item ID
     * @param array $nav_menu_items Navigation menu items
     * @param bool $depth Gives all children or direct children only
     * @return array    returns filtered array of nav_menu_items
     */
    public function getNavMenuItemChildren($parent_id, $nav_menu_items, $depth = true)
    {

        $nav_menu_item_list = [];

        foreach ((array) $nav_menu_items as $nav_menu_item) {

            if ($nav_menu_item->menu_item_parent == $parent_id) {

                $nav_menu_item_list[] = $this->formatMenuItem($nav_menu_item, true, $nav_menu_items);

                if ($depth) {
                    if ($children = $this->getNavMenuItemChildren($nav_menu_item->ID, $nav_menu_items)) {
                        $nav_menu_item_list = array_merge($nav_menu_item_list, $children);
                    }
                }

            }

        }

        return $nav_menu_item_list;
    }


    /**
     * Format a menu item for REST API consumption.
     *
     * @param  object|array $menu_item The menu item
     * @param  bool $children Get menu item children (default false)
     * @param  array $menu The menu the item belongs to (used when $children is set to true)
     * @return array    a formatted menu item for REST
     */
    public function formatMenuItem($menu_item, $children = false, $menu = [])
    {

        $item        = (array) $menu_item;
        $domainRegex = '/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i';

        $menu_item = [
            'id' => abs($item['ID']),
            'order' => (int) $item['menu_order'],
            'parent' => abs($item['menu_item_parent']),
            'title' => $item['title'],
            'url' => preg_replace($domainRegex, '$2', '' . $item['url']),
            'attr' => $item['attr_title'],
            'target' => $item['target'],
            'classes' => implode(' ', apply_filters('nav_menu_css_class', array_filter($item['classes']), $item)),
            'xfn' => $item['xfn'],
            'description' => $item['description'],
            'object_id' => abs($item['object_id']),
            'object' => $item['object'],
            'object_slug' => get_post($item['object_id'])->post_name,
            'type' => $item['type'],
            'type_label' => $item['type_label'],
        ];

        if ($children === true && !empty($menu)) {
            $menu_item['children'] = $this->getNavMenuItemChildren($item['ID'], $menu);
        }

        return apply_filters('rest_menus_format_menu_item', $menu_item);
    }

}
