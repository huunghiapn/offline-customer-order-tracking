<?php
/**
 * Plugin Name: Offline Customer's Order Tracking
 * Plugin URI:  https://github.com/huunghiapn/offline-customer-order-tracking
 * Description: "Offline Customer's Order Tracking" provides the simplest way to manage store-based purchases. Create/Edit/Delete/Search customer's order
 * Version:     1.0
 * Author:      NghiaNH
 * Author URI:  https://profiles.wordpress.org/nghianh/
 * License:     GPLv2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 **/
if (!defined('ABSPATH')) {
    exit;
}

//****************************************************************************
//* Define section
//****************************************************************************

// Define path and URL to the ACF plugin.
define('MY_OCOT_PATH', plugin_dir_path(__FILE__));
define('MY_OCOT_URL', plugin_dir_url(__FILE__));

//****************************************************************************
//* Class section
//****************************************************************************

include_once(MY_OCOT_PATH . 'inc/acf/acf.php');
/**
 * Start the instance
 */

new OfflineOrder();

/**
 * The main class of the plugin
 *
 * @author   nghianh
 * @since    1.0
 */
class OfflineOrder
{
    /**
     * Setup class.
     *
     * @since 1.0
     */
    public function __construct()
    {
        add_action('init', array($this, 'init'));

        // Customize the url setting to fix incorrect asset URLs.
        add_filter('acf/settings/url', array($this, 'my_acf_settings_url'));

        // (Optional) Hide the ACF admin menu item.
        add_filter('acf/settings/show_admin', array( $this, 'my_acf_settings_show_admin'));


        add_action('acf/include_field_types', array($this, 'include_field_types_unique_id'));

        // remove Yoast SEO
        add_action('add_meta_boxes', array($this, 'ocot_remove_wp_seo_meta_box'), 100);

        //Admin Filter BY Custom Fields
        add_action('restrict_manage_posts', array($this, 'ocot_admin_posts_filter_restrict_manage_posts'));
        add_filter('parse_query', array($this, 'ocot_posts_filter'));

        // Add the custom columns to the ocot post type:
        add_filter('manage_offline_order_posts_columns', array($this, 'set_custom_edit_offline_order_columns'), 10, 2);

        // Add the data to the custom columns for the book post type:
        add_action('manage_offline_order_posts_custom_column', array($this, 'custom_offline_order_column'), 10, 2);

        // add js to edit page
        add_action('admin_print_scripts-post-new.php', array($this, 'ocot_admin_script'), 11);
        add_action('admin_print_scripts-post.php', array($this, 'ocot_admin_script'), 11);
    }

    /**
     * Throw a notice if ACF is NOT active
     */
    public function notice_if_not_acf()
    {
        $class = 'notice notice-warning';

        $message = __('Offline Customer\'s Order Tracking is not running because ACF is not active. Please activate both plugins.',
            'ocot');

        printf('<div class="%1$s"><p><strong>%2$s</strong></p></div>', $class, $message);
    }

    /**
     * Run this method under the "init" action
     */
    public function init()
    {

        // Load the localization feature
        $this->i18n();

        if (class_exists('ACF')) {
            // Run this plugin normally if ACF is active
            $this->main();

        } else {
            // Throw a notice if WooCommerce is NOT active
            add_action('admin_notices', array($this, 'notice_if_not_acf'));
        }
    }

    /**
     * Localize the plugin
     * @since 1.0
     */
    public function i18n()
    {
        load_plugin_textdomain('ocot', false, basename(dirname(__FILE__)) . '/languages/');
    }

    /**
     * The main method to load the components
     */
    public function main()
    {

        // Register custom post type
        $this->ocot_register_my_cpts();

        // Register order's field
        $this->add_local_field_group();
    }
    //****************************************************************************
    //* Functions section
    //****************************************************************************
    public function my_acf_settings_url($url)
    {
        return MY_OCOT_URL . 'inc/acf/';
    }

    public function my_acf_settings_show_admin($show_admin)
    {
        return false;
    }

    // Include field type for ACF5
    public function include_acf()
    {
        include_once(MY_OCOT_PATH . 'inc/acf/acf.php');
    }

    // Include field type for ACF5
    public function include_field_types_unique_id($version)
    {
        include_once(MY_OCOT_PATH . 'inc/acf-unique_id-v5.php');
    }

    // Register custom post type
    public function ocot_register_my_cpts()
    {
        /**
         * Post Type: Offline Customer's Order.
         */

        $labels = [
            "name" => __("Offline Customer's Order", "ocot"),
            "singular_name" => __("Offline Customer's Order", "ocot"),
            "menu_name" => __("Offline Customer's Order", "ocot"),
            "all_items" => __("All Order", "ocot"),
            "add_new" => __("Add Order", "ocot"),
            "add_new_item" => __("Add Order", "ocot"),
            "edit_item" => __("Edit Order", "ocot"),
        ];

        $args = [
            "label" => __("Offline Customer's Order", "ocot"),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "delete_with_user" => false,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "exclude_from_search" => true,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => ["slug" => "offline_order", "with_front" => true],
            "query_var" => true,
            "menu_icon" => "dashicons-cart",
            "supports" => false,
        ];

        register_post_type("offline_order", $args);
    }

    // Register custom field
    public function add_local_field_group()
    {
        if (function_exists('acf_add_local_field_group')):

            acf_add_local_field_group(array(
                'key' => 'group_order_info',
                'title' => __('Order Information', "ocot"),
                'fields' => array(
                    array(
                        'key' => 'field_5df8b028912f4',
                        'label' => __('Order No', "ocot"),
                        'name' => 'order_id',
                        'type' => 'unique_id',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                    ),
                    array(
                        'key' => 'field_5df879539e949',
                        'label' => __('Customer\'s Name', "ocot"),
                        'name' => 'customer_name',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5df8796a9e94a',
                        'label' => __('Customer\'s phone', "ocot"),
                        'name' => 'customer_phone',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5df88fc549233',
                        'label' => __('Customer\'s Address', "ocot"),
                        'name' => 'customer_address',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_5df8799f9e94b',
                        'label' => __('Purchases Date', "ocot"),
                        'name' => 'purchases_date',
                        'type' => 'date_picker',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                        'display_format' => 'd/m/Y',
                        'return_format' => 'd/m/Y',
                        'first_day' => 1,
                    ),
                    array(
                        'key' => 'field_5df87b5de2e03',
                        'label' => __('Products', "ocot"),
                        'name' => 'products',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array(
                            0 => 'product',
                        ),
                        'taxonomy' => '',
                        'allow_null' => 0,
                        'multiple' => 1,
                        'return_format' => 'object',
                        'ui' => 1,
                    ),
                    array(
                        'key' => 'field_5df87baee2e04',
                        'label' => __('Total', "ocot"),
                        'name' => 'total_money',
                        'type' => 'number',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => 'đ',
                        'min' => '',
                        'max' => '',
                        'step' => 1000,
                    ),
                    array(
                        'key' => 'field_5df8b48ab5dac',
                        'label' => __('Notes', "ocot"),
                        'name' => 'notes',
                        'type' => 'textarea',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'maxlength' => '',
                        'rows' => '',
                        'new_lines' => '',
                    ),
                    array(
                        'key' => 'field_5df8ba3630d29',
                        'label' => __('Export', "ocot"),
                        'name' => 'print',
                        'type' => 'message',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => '<button type="button" class="button-secondary" id="btn-print" data-action="print">' . __('Print Order', "ocot") . '</button>',
                        'new_lines' => 'wpautop',
                        'esc_html' => 0,
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'offline_order',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'acf_after_title',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
            ));

        endif;
    }

    //Disable Yoast SEO on ocot
    public function ocot_remove_wp_seo_meta_box()
    {
        remove_meta_box('wpseo_meta', 'offline_order', 'normal');
    }

    public function ocot_admin_posts_filter_restrict_manage_posts()
    {
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }

        //only add filter to post type you want
        if ('offline_order' == $type) {
            $current_name = isset($_GET['ocot_filter_name']) ? $_GET['ocot_filter_name'] : '';
            $current_phone = isset($_GET['ocot_filter_phone']) ? $_GET['ocot_filter_phone'] : '';
            ?>
            <input type="text" name="ocot_filter_name" placeholder="<?php _e("Customer's name", "ocot"); ?>"
                   value="<?php echo $current_name; ?>">
            <input type="text" name="ocot_filter_phone" placeholder="<?php _e("Customer's phone", "ocot"); ?>"
                   value="<?php echo $current_phone; ?>">
            <?php
        }
    }

    /**
     * if submitted filter by post meta
     *
     */
    public function ocot_posts_filter($query)
    {
        global $pagenow;
        $type = 'post';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }
        if ('offline_order' == $type && is_admin() && $pagenow == 'edit.php') {
            $meta_query_args = array(
                'relation' => 'OR', // Optional
            );

            if (isset($_GET['ocot_filter_name']) && $_GET['ocot_filter_name'] != '') {
                array_push($meta_query_args, array(
                    'key' => 'customer_name',
                    'value' => $_GET['ocot_filter_name'],
                    'compare' => 'LIKE'
                ));
            }

            if (isset($_GET['ocot_filter_phone']) && $_GET['ocot_filter_phone'] != '') {
                array_push($meta_query_args, array(
                    'key' => 'customer_phone',
                    'value' => $_GET['ocot_filter_phone'],
                    'compare' => 'LIKE'
                ));
            }
            $query->query_vars['meta_query'] = $meta_query_args;
        }
    }

    public function set_custom_edit_offline_order_columns($columns)
    {
        unset($columns['title']);

        $new_columns['order_id'] = __('Order No', 'ocot');
        $new_columns['customer_name'] = __('Customer\'s Name', 'ocot');
        $new_columns['customer_phone'] = __('Customer\' Phone', 'ocot');
        $new_columns['date'] = __('Created Date', 'ocot');

        return $new_columns;
    }

    public function custom_offline_order_column($column, $post_id)
    {
        switch ($column) {
            case 'order_id':
                echo get_post_meta($post_id, 'order_id', true);
                break;
            case 'customer_name' :
                echo get_post_meta($post_id, 'customer_name', true);
                break;
            case 'customer_phone' :
                echo get_post_meta($post_id, 'customer_phone', true);
                break;

        }
    }

    public function ocot_admin_script()
    {
        global $post_type;
        if ('offline_order' == $post_type)
            wp_enqueue_script('ocot-admin-script', MY_OCOT_URL . 'js/main.js');
    }


    /**
     * Add "Settings" link in the Plugins list page when the plugin is active
     *
     * @since 1.0
     * @author nghianh
     */
    public function add_settings_link($links)
    {
        $settings = array('<a href="' . admin_url('admin.php?page=offline_order') . '">' . __('Settings', 'ocot') . '</a>');
        $links = array_reverse(array_merge($links, $settings));

        return $links;
    }

}