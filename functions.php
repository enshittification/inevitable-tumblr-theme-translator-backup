<?php

defined( 'ABSPATH' ) || exit;

use Chrysalis\T3\Plugin;

/**
 * Returns the plugin's main class instance.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  Plugin
 */
function tumblr3_get_plugin_instance(): Plugin {
	return Plugin::get_instance();
}

/**
 * Returns the plugin's slug.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string
 */
function tumblr3_get_plugin_slug(): string {
	return sanitize_key( TUMBLR3_METADATA['TextDomain'] );
}

/**
 * We need a custom do_shortcode implementation because do_shortcodes_in_html_tags()
 * is run before running reguular shortcodes, which means that things like link hrefs
 * get populated before they even have context.
 *
 * @todo nested tags of the same type aren't rendering properly.
 *
 * @param [type] $content
 * @param boolean $ignore_html
 * @return void
 */
function tumblr3_do_shortcode( $content, $ignore_html = false ) {
	global $shortcode_tags;
	static $pattern = null;

	// Avoid generating this multiple times.
	if ( null === $pattern ) {
		$pattern = get_shortcode_regex( array_keys( $shortcode_tags ) );
	}

	$content = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $content );

	// Always restore square braces so we don't break things like <!--[if IE ]>.
	$content = unescape_invalid_shortcodes( $content );

	return $content;
}

/**
 * Undocumented function
 *
 * @param [type] $content
 * @return void
 */
function tumblr3_theme_parse( $content ) {
	$tags   = array_keys( TUMBLR3_TAGS );
	$blocks = array_keys( TUMBLR3_BLOCKS );
	$lang   = array_keys( TUMBLR3_LANG );

	// Capture each Tumblr Tag in the page and verify it against our arrays.
	$content = preg_replace_callback(
		'/\{(.*?)\}/',
		function ( $matches ) use ( $tags, $blocks, $lang ) {
			$captured_tag = $matches[0];
			$raw_tag      = $matches[1];
			$trim_tag     = explode( ' ', $matches[1] )[0];
			$attr         = '';

			/**
			 * Handle theme options blocks.
			 *
			 * @todo This should be a conditonal return, not just an empty string.
			 */
			if ( str_starts_with( ltrim( $raw_tag, '/' ), 'block:If' ) ) {
				return '';
			}

			// Verify the block against our array.
			if ( str_starts_with( $raw_tag, 'block:' ) || str_starts_with( $raw_tag, '/block:' ) ) {
				if ( in_array( ltrim( $raw_tag, '/' ), $blocks, true ) ) {
					return '[' . str_replace( 'block:', 'block_', strtolower( $raw_tag ) ) . ']';
				}

				// False positive.
				return $captured_tag;
			}

			// Is the tag one of the portrait functions? Convert the sizing to an attribute to avoid a function for each size.
			if ( str_ends_with( $trim_tag, '-16' ) ||
			str_ends_with( $trim_tag, '-24' ) ||
			str_ends_with( $trim_tag, '-30' ) ||
			str_ends_with( $trim_tag, '-40' ) ||
			str_ends_with( $trim_tag, '-48' ) ||
			str_ends_with( $trim_tag, '-64' ) ||
			str_ends_with( $trim_tag, '-96' ) ||
			str_ends_with( $trim_tag, '-100' ) ||
			str_ends_with( $trim_tag, '-128' ) ||
			str_ends_with( $trim_tag, '-250' ) ||
			str_ends_with( $trim_tag, '-400' ) ||
			str_ends_with( $trim_tag, '-500' ) ||
			str_ends_with( $trim_tag, '-640' ) ||
			str_ends_with( $trim_tag, '-700' )
			) {
				$split_tag = explode( '-', $trim_tag );

				// Ensure the tag is in the correct format.
				if ( count( $split_tag ) !== 2 ) {
					return $captured_tag;
				}

				$trim_tag = $split_tag[0];
				$attr     = ' size=' . $split_tag[1];
			}

			// Verify the tag against our array.
			if ( in_array( ltrim( $trim_tag, '/' ), $tags, true ) ) {
				return '[tag_' . strtolower( $trim_tag ) . $attr . ']';
			}

			// Verify the lang against our array.
			if ( in_array( ltrim( $raw_tag, 'lang:' ), $lang, true ) ) {
				$return_lang = TUMBLR3_LANG[ ltrim( $raw_tag, 'lang:' ) ];
				return ( is_array( $return_lang ) ) ? $return_lang[0] : $return_lang;
			}

			return $captured_tag;
		},
		$content
	);

	return $content;
}

require TUMBLR3_PATH . 'includes/assets.php';
require TUMBLR3_PATH . 'includes/missing-functions.php';
require TUMBLR3_PATH . 'includes/block-functions.php';
require TUMBLR3_PATH . 'includes/tag-functions.php';
