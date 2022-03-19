<?php
/**
 * Plugin Name:         Plugin Tags
 * Description:         Add tags to the plugins list view to quickly note which does what.
 * Version:             1.2.3
 * Tags:                plugin tags, plugin notes, plugin keywords, plugin management
 * Author:              DamChtlv
 * Author URI:          https://dam.cht.lv
 * License:             GPLv2 or later
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:        5.6 or higher
 * Requires at least:   4.9 or higher
 * Text Domain:         ptags
 * Domain Path:         /lang
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Plugin_Tags' ) ) {

    /**
     * Plugin Tags
     */
    class Plugin_Tags {

        /**
         * Plugin version
         *
         * @var string
         */
        public $version = '1.2.3';

        // Constructor
        public function __construct() {
            // Do nothing.
        }

        // Init plugin core
        public function initialize() {

            // Constants
            $this->define( 'PLUGIN_TAGS_FILE', __FILE__ );
            $this->define( 'PLUGIN_TAGS_PATH', plugin_dir_path( __FILE__ ) );
            $this->define( 'PLUGIN_TAGS_URL', plugin_dir_url( __FILE__ ) );
            $this->define( 'PLUGIN_TAGS_BASENAME', plugin_basename( __FILE__ ) );

            // Includes
            $this->includes();

            // Load
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 20, 4 );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
            add_filter( 'views_plugins', array( $this, 'plugin_tags_filters' ) );
            add_filter( 'all_plugins', array( $this, 'plugin_tags_filtered_list' ) );

            // Ajax
            add_action( 'wp_ajax_ptags_update_tags', array( $this, 'ajax_update_tags_config' ) );

        }

        /**
         * Plugins tags view filtered
         *
         * @param array $plugins
         * @return $plugins
         */
        public function plugin_tags_filtered_list( $plugins ) {

            $plugin_tag_view = ptg_maybe_get_GET( 'plugin_tag' );
            if ( !$plugin_tag_view ) {
                return $plugins;
            }

            // Get existing values
            $plugin_tags_option = get_option( 'plugin_tags_option' );
            $plugin_tags_option = empty( $plugin_tags_option ) || !is_array( $plugin_tags_option ) ? array() : $plugin_tags_option;
            $plugin_tags_option = apply_filters( 'ptags/option', $plugin_tags_option );

            if ( !isset( $plugin_tags_option['plugins'] ) ) {
                return $plugins;
            }

            global $plugins_to_display;
            $plugins_to_display = array();

            foreach ( $plugin_tags_option['plugins'] as $plugin_slug => $plugin_data ) {

                $tag_name = ptg_maybe_get( $plugin_data, 'tag' );
                if ( !$tag_name || $tag_name !== $plugin_tag_view ) {
                    continue;
                }

                $plugins_to_display[] = trim( $plugin_slug );

            }

            // Check plugin slug using plugins filename and a regex
            $plugins = array_filter( $plugins, 'ptg_plugins_filter', ARRAY_FILTER_USE_KEY );

            return $plugins;
        }

        /**
         * Plugins tags filters
         *
         * @param array $views
         * @return $views
         */
        public function plugin_tags_filters( $views ) {

            global $page;

            // Get existing values
            $plugin_tags_option = get_option( 'plugin_tags_option' );
            $plugin_tags_option = empty( $plugin_tags_option ) || !is_array( $plugin_tags_option ) ? array() : $plugin_tags_option;
            $plugin_tags_option = apply_filters( 'ptags/option', $plugin_tags_option );

            if ( !isset( $plugin_tags_option['tags'] ) ) {
                return $views;
            }

            foreach ( $plugin_tags_option['tags'] as $tag_slug => $tag_data ) {

                $has_tag_view = ptg_maybe_get( $tag_data, 'view' );
                if ( !$has_tag_view ) {
                    continue;
                }

                $tag_view_url       = self_admin_url( "plugins.php?plugin_status=all&paged={$page}&plugin_tag={$tag_slug}" );
                $views[ $tag_slug ] = wp_sprintf( "<a href='%s'>%s</a>", $tag_view_url, $tag_slug );

            }

            return $views;
        }

        // Update plugin tags config (ajax)
        public function ajax_update_tags_config() {

            $plugin_slug = ptg_maybe_get_POST( 'plugin_slug' );
            if ( !$plugin_slug ) {
                wp_send_json_error( __( 'Missing plugin slug data.', 'ptags' ) );
            }

            // Get ajax data & escape them
            $tag_name  = ptg_maybe_get_POST( 'tag_name' );
            $tag_color = ptg_maybe_get_POST( 'tag_color' );
            $tag_view  = ptg_maybe_get_POST( 'tag_view' );
            if ( !$tag_name && $tag_color === false && !$tag_view ) {
                wp_send_json_error( __( 'Missing tag data.', 'ptags' ) );
            }

            // Get existing values
            $plugin_tags_option = get_option( 'plugin_tags_option' );
            $plugin_tags_option = empty( $plugin_tags_option ) || !is_array( $plugin_tags_option ) ? array() : $plugin_tags_option;
            $plugin_tags_option = apply_filters( 'ptags/option', $plugin_tags_option );

            // Get new values
            $plugin_tag_data = array();
            if ( $tag_color ) {
                $plugin_tag_data['color'] = $tag_color;
            }
            if ( $tag_name ) {
                $plugin_tag_data['tag'] = $tag_name;
            }
            if ( $tag_view ) {
                $plugin_tag_data['view'] = 1;

                // Merge old data with new one
                if ( isset( $plugin_tags_option['tags'][ $tag_view ] ) ) {

                    $plugin_old_tag_data = $plugin_tags_option['tags'][ $tag_view ];
                    $plugin_tag_data     = array_merge( $plugin_old_tag_data, $plugin_tag_data );

                    $old_tag_view_state = ptg_maybe_get( $plugin_old_tag_data, 'view' );
                    if ( $old_tag_view_state === $plugin_tag_data['view'] ) {
                        unset( $plugin_tag_data['view'] );
                    }
                }

                $plugin_tags_option['tags'][ $tag_view ] = $plugin_tag_data;

            } else {

                // Merge old data with new one
                if ( isset( $plugin_tags_option['plugins'][ $plugin_slug ] ) ) {

                    $plugin_old_tag_data = $plugin_tags_option['plugins'][ $plugin_slug ];
                    $plugin_old_tag_view = ptg_maybe_get( $plugin_old_tag_data, 'tag' );

                    // Check if a view tag was already set for this one
                    if ( isset( $plugin_tags_option['tags'] ) && !empty( $plugin_tags_option['tags'] ) ) {
                        foreach ( $plugin_tags_option['tags'] as $tag_view => &$tag_data ) {

                            if ( $tag_view !== $plugin_old_tag_view ) {
                                continue;
                            }

                            // Update it to use the new tag name
                            unset( $plugin_tags_option['tags'][ $tag_view ] );
                            $plugin_tags_option['tags'][ $tag_name ] = $tag_data;
                        }
                    }

                    $plugin_tag_data = array_merge( $plugin_old_tag_data, $plugin_tag_data );
                }

                $plugin_tags_option['plugins'][ $plugin_slug ] = $plugin_tag_data;
            }

            // Update option
            update_option( 'plugin_tags_option', $plugin_tags_option );

            wp_send_json_success( __( 'Plugin tags config updated.', 'ptags' ) );

        }

        // Return user color scheme
        public function get_color_scheme() {

            global $_wp_admin_css_colors;
            $color_scheme = get_user_option( 'admin_color' );

            // It's possible to have a color scheme set that is no longer registered.
            if ( empty( $_wp_admin_css_colors[ $color_scheme ] ) ) {
                $color_scheme = 'fresh';
            }

            if ( !empty( $_wp_admin_css_colors[ $color_scheme ]->colors ) ) {
                $colors = $_wp_admin_css_colors[ $color_scheme ]->colors;
            } elseif ( !empty( $_wp_admin_css_colors['fresh']->colors ) ) {
                $colors = $_wp_admin_css_colors['fresh']->colors;
            } else {
                // Fall back to the default set of icon colors if the default scheme is missing.
                $colors = array(
                    'base'    => '#a7aaad',
                    'focus'   => '#72aee6',
                    'current' => '#fff',
                );
            }

            $colors = array_values( $colors );
            return $colors;

        }

        // Load admin assets
        public function admin_assets() {

            global $current_screen;
            if ( !$current_screen || !isset( $current_screen->base ) || $current_screen->base !== 'plugins' ) {
                return;
            }

            // CSS
            wp_enqueue_style(
                'plugin-tags',
                PLUGIN_TAGS_URL . 'assets/css/plugin-tags.css',
                null,
                1.0
            );

            // JS
            wp_enqueue_script(
                'plugin-tags',
                PLUGIN_TAGS_URL . 'assets/js/plugin-tags.js',
                array( 'jquery' ),
                1.0,
                true
            );

            // Global JS object
            wp_add_inline_script(
                'plugin-tags',
                'const plugin_tags = ' . wp_json_encode(
                    array(
                        'ajaxurl'      => admin_url( 'admin-ajax.php' ),
                        'color_scheme' => $this->get_color_scheme(),
                    )
                ),
                'before',
            );

        }

        /**
         * Plugin row meta (display)
         *
         * @param array $plugin_metas
         * @param string $plugin_file
         * @param array $plugin_data
         * @param string $status
         * @return $plugin_metas
         */
        public function plugin_row_meta( $plugin_metas, $plugin_file, $plugin_data, $status ) {

            $plugin_name = ptg_maybe_get( $plugin_data, 'Name' );
            $plugin_slug = isset( $plugin_data['slug'] ) ? ptg_maybe_get( $plugin_data, 'slug' ) : sanitize_title( $plugin_name );
            $plugin_tags = ptg_maybe_get( $plugin_data, 'Tags' );

            // Default displayed values
            $tag_name  = __( 'No tag', 'ptags' );
            $tag_color = $tag_color_index = '';

            // Check if the plugin has some "tags" set, if so, use them as default
            if ( $plugin_tags ) {
                $plugin_tags_arr = explode( ', ', $plugin_tags );
                $tag_name        = is_array( $plugin_tags_arr ) && !empty( $plugin_tags_arr ) ? reset( $plugin_tags_arr ) : $plugin_tags;
            }

            // Get existing values
            $plugin_tags_option = get_option( 'plugin_tags_option' );
            $plugin_tags_option = empty( $plugin_tags_option ) || !is_array( $plugin_tags_option ) ? array() : $plugin_tags_option;
            $plugin_tags_option = apply_filters( 'ptags/option', $plugin_tags_option );

            // Get existing data if it exists
            if ( isset( $plugin_tags_option['plugins'][ $plugin_slug ] ) ) {
                $plugin_tag_data = $plugin_tags_option['plugins'][ $plugin_slug ];
                $tag_name        = ptg_maybe_get( $plugin_tag_data, 'tag', $tag_name );
                $tag_color_index = ptg_maybe_get( $plugin_tag_data, 'color' );
            }

            // Turn color number into hex color user scheme
            if ( $tag_color_index ) {
                $color_scheme = $this->get_color_scheme();
                $tag_color    = ptg_maybe_get( $color_scheme, $tag_color_index );
                $tag_color    = "style='--plugin-tag-bg:$tag_color;'";
            }

            // Template
            $tag_html = sprintf(
                "<div class='plugin-tags' $tag_color>" .
                    "<span class='plugin-tag' contenteditable='true' title='Change tag'>" .
                        '%s' .
                    '</span> ' .
                    "<button type='button' class='js-change-color dashicons dashicons-admin-appearance' title='Change color' data-color='$tag_color_index'></button>" .
                    "<button type='button' class='js-toggle-tag-view dashicons dashicons-sticky' title='Toggle tag view'></button>" .
                '</div>',
                esc_html( $tag_name ),
            );

            $plugin_metas = array_merge( array( $tag_html ), $plugin_metas );
            return $plugin_metas;

        }

        // Includes files
        public function includes() {

            // Helpers
            require_once realpath( PLUGIN_TAGS_PATH . 'includes/helpers.php' );

            // ptg_include( 'includes/file.php' );

        }

        /**
         * Define constants
         *
         * @param      $name
         * @param bool $value
         */
        private function define( $name, $value = true ) {
            if ( !defined( $name ) ) {
                define( $name, $value );
            }
        }

    }
}

/**
 * Instantiate plugin
 *
 * @return Plugin_Tags
 */
function plugin_tags() {
    global $plugin_tags;

    if ( !isset( $plugin_tags ) ) {
        $plugin_tags = new Plugin_Tags();
        $plugin_tags->initialize();
    }

    return $plugin_tags;
}

// Instantiate
plugin_tags();
