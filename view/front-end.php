<?php

defined( 'ABSPATH' ) || exit;

// Shortcodes don't currently have a doing_shortcode() or similar. So we need a global to track the context.
global $tumblr3_parse_context;
$tumblr3_parse_context = 'theme';

$theme = get_option( 'tumblr3_theme_html', '' );

// Capture wp_head output.
ob_start();
wp_head();
$head = ob_get_contents();
ob_end_clean();

// Capture wp_footer output.
ob_start();
wp_footer();
$footer = ob_get_contents();
ob_end_clean();

// Capture body_class output.
ob_start();
body_class();
$body_class = ob_get_contents();
ob_end_clean();

$theme = str_replace( '</head>', $head . '</head>', $theme );
$theme = str_replace( '</body>', $footer . '</body>', $theme );

echo tumblr3_do_shortcode( tumblr3_theme_parse( $theme ) );
