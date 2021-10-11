<?php
defined( 'ABSPATH' ) || exit;

// Easy safe include file
function ptg_include( $filename = '' ) {
    $file_path = realpath( PLUGIN_TAGS_PATH . ltrim( $filename, '/' ) );
    if ( file_exists( $file_path ) ) {
        include_once $file_path;
    }
}

// Easily get array value
function ptg_maybe_get( $array = array(), $key = 0, $default = null ) {
    return isset( $array[ $key ] ) ? $array[ $key ] : $default;
}

// Easy sanitized function to get POST value
function ptg_maybe_get_POST( $key = '', $default = null ) {
    return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
}

// Easy sanitized function to get GET value
function ptg_maybe_get_GET( $key = '', $default = null ) {
    return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : $default;
}

// Filter to get plugin slug out of plugin filename in whitelisted plugins list.
function ptg_plugins_filter( $plugin_filename ) {

    global $plugins_to_display;

    // Get first part of plugin filename
    // (ex: "advanced-custom-fields-pro" in "advanced-custom-fields-pro/acf.php")
    preg_match( '/(.*?)(?=\/)/', $plugin_filename, $matches );
    $plugin_slug = reset( $matches );

    return in_array( $plugin_slug, $plugins_to_display, true );
}
