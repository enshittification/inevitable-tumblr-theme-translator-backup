<?php

namespace Chrysalis\T3;

defined( 'ABSPATH' ) || exit;

/**
 * Logical node for all integration functionalities.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
final class Hooks {

	/**
	 * Initializes the Hooks.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function initialize(): void {
		add_filter( 'template_include', array( $this, 'redirect_frontend_templates' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @param string $template
	 * @return string
	 */
	public function redirect_frontend_templates( string $template ): string {
		return TUMBLR3_PATH . 'view/front-end.php';
	}
}
