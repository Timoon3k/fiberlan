<?php
/** Idempotent bootstrap for the isolated Docker instance. */
if ( PHP_SAPI !== 'cli' ) {
    exit( 1 );
}

$_SERVER['HTTP_HOST']      = '127.0.0.1:8080';
$_SERVER['REQUEST_URI']    = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_PORT']    = '8080';

$install = ( $argv[1] ?? '' ) === 'install';
if ( $install ) {
    define( 'WP_INSTALLING', true );
}
require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( $install && ! is_blog_installed() ) {
    $password = trim( stream_get_contents( STDIN ) );
    if ( '' === $password ) {
        throw new RuntimeException( 'Missing local administrator password.' );
    }
    add_filter( 'pre_wp_mail', '__return_true' );
    wp_install( 'FIBERLAN SC', 'fiberlan-editor', 'dev@fiberlan.invalid', false, '', $password, 'pl_PL' );
    update_option( 'WPLANG', 'pl_PL' );
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure( '/%postname%/' );
    flush_rewrite_rules( false );
}
wp_installing( false );

if ( $install && 'fiberlan' !== get_stylesheet() ) {
    $theme = wp_get_theme( 'fiberlan' );
    if ( ! $theme->exists() || $theme->errors() ) {
        throw new RuntimeException( 'The Fiberlan block theme is missing or incomplete.' );
    }
    switch_theme( 'fiberlan' );
}
if ( $install ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $activated = activate_plugin( 'fiberlan-site/fiberlan-site.php' );
    if ( is_wp_error( $activated ) ) {
        throw new RuntimeException( $activated->get_error_message() );
    }
    exit( 0 );
}

// In a fresh process, WordPress loads the active theme and registers its patterns.
wp_set_current_user( get_user_by( 'login', 'fiberlan-editor' )->ID );
$result = fl_install_stage_two();
if ( is_wp_error( $result ) ) {
    throw new RuntimeException( $result->get_error_message() );
}
if ( ! is_readable( get_stylesheet_directory() . '/templates/front-page.html' ) || ! get_option( 'page_on_front' ) ) {
    throw new RuntimeException( 'Missing front page or template.' );
}
printf( "WordPress %s, PHP %s, active theme %s, front page #%d\n", get_bloginfo( 'version' ), PHP_VERSION, get_stylesheet(), get_option( 'page_on_front' ) );
