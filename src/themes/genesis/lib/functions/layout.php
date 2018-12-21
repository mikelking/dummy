<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Genesis\Layout
 * @author  StudioPress
 * @license GPL-2.0-or-later
 * @link    https://my.studiopress.com/themes/genesis/
 */

add_action( 'genesis_setup', 'genesis_create_initial_layouts' );
/**
 * Register Genesis default layouts.
 *
 * Genesis comes with six layouts registered by default. These are:
 *
 *  - content-sidebar (default)
 *  - sidebar-content
 *  - content-sidebar-sidebar
 *  - sidebar-sidebar-content
 *  - sidebar-content-sidebar
 *  - full-width-content
 *
 * @since 1.4.0
 */
function genesis_create_initial_layouts() {

	// Common path to default layout images.
	$url = GENESIS_ADMIN_IMAGES_URL . '/layouts/';

	$layouts = apply_filters( 'genesis_initial_layouts', array(
		'content-sidebar'         => array(
			'label'   => __( 'Content, Primary Sidebar', 'genesis' ),
			'img'     => $url . 'cs.gif',
			'default' => is_rtl() ? false : true,
			'type'    => array( 'site' ),
		),
		'sidebar-content'         => array(
			'label'   => __( 'Primary Sidebar, Content', 'genesis' ),
			'img'     => $url . 'sc.gif',
			'default' => is_rtl() ? true : false,
			'type'    => array( 'site' ),
		),
		'content-sidebar-sidebar' => array(
			'label' => __( 'Content, Primary Sidebar, Secondary Sidebar', 'genesis' ),
			'img'   => $url . 'css.gif',
			'type'  => array( 'site' ),
		),
		'sidebar-sidebar-content' => array(
			'label' => __( 'Secondary Sidebar, Primary Sidebar, Content', 'genesis' ),
			'img'   => $url . 'ssc.gif',
			'type'  => array( 'site' ),
		),
		'sidebar-content-sidebar' => array(
			'label' => __( 'Secondary Sidebar, Content, Primary Sidebar', 'genesis' ),
			'img'   => $url . 'scs.gif',
			'type'  => array( 'site' ),
		),
		'full-width-content'      => array(
			'label' => __( 'Full Width Content', 'genesis' ),
			'img'   => $url . 'c.gif',
			'type'  => array( 'site' ),
		),
	), $url );

	foreach ( (array) $layouts as $layout_id => $layout_args ) {
		genesis_register_layout( $layout_id, $layout_args );
	}

}

/**
 * Register new layouts in Genesis.
 *
 * Modifies the global `$_genesis_layouts` variable.
 *
 * The support `$args` keys are:
 *
 *  - label (Internationalized name of the layout),
 *  - img   (URL path to layout image),
 *  - type  (Layout type).
 *
 * Although the 'default' key is also supported, the correct way to change the default is via the
 * `genesis_set_default_layout()` function to ensure only one layout is set as the default at one time.
 *
 * @since 1.4.0
 *
 * @see genesis_set_default_layout() Set a default layout.
 *
 * @global array $_genesis_layouts Holds all layouts data.
 *
 * @param string $id   ID of layout.
 * @param array  $args Layout data.
 * @return bool|array Return `false` if ID is missing or is already set. Return merged `$args` otherwise.
 */
function genesis_register_layout( $id = '', $args = array() ) {

	global $_genesis_layouts;

	if ( ! is_array( $_genesis_layouts ) ) {
		$_genesis_layouts = array();
	}

	// Don't allow empty $id, or double registrations.
	if ( ! $id || isset( $_genesis_layouts[ $id ] ) ) {
		return false;
	}

	$defaults = array(
		'label' => __( 'No Label Selected', 'genesis' ),
		'img'   => GENESIS_ADMIN_IMAGES_URL . '/layouts/none.gif',
		'type'  => array( 'site' ),
	);

	$args = wp_parse_args( $args, $defaults );

	$_genesis_layouts[ $id ] = $args;

	return $args;

}

/**
 * Add new layout type to a layout without having to directly modify the global variable.
 *
 * @since 2.5.1
 *
 * @param string       $id   ID of layout.
 * @param array|string $type Array (or string of single type) of types to add.
 * @return array Return merged type array.
 */
function genesis_add_type_to_layout( $id, $type = array() ) {

	global $_genesis_layouts;

	$new_type = array_merge( (array) $_genesis_layouts[ $id ]['type'], (array) $type );

	$_genesis_layouts[ $id ]['type'] = $new_type;

	return $new_type;

}

/**
 * Remove layout type from a layout without having to directly modify the global variable.
 *
 * @since 2.5.1
 *
 * @param string       $id   ID of layout.
 * @param array|string $type Array (or string of single type) of types to remove.
 * @return array Return type array.
 */
function genesis_remove_type_from_layout( $id, $type = array() ) {

	global $_genesis_layouts;

	$new_type = array_values( array_diff( (array) $_genesis_layouts[ $id ]['type'], (array) $type ) );

	$_genesis_layouts[ $id ]['type'] = $new_type;

	return $new_type;

}

/**
 * Set a default layout.
 *
 * Allow a user to identify a layout as being the default layout on a new install, as well as serve as the fallback layout.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layouts data.
 *
 * @param string $id ID of layout to set as default.
 * @return bool|string Return `false` if ID is empty or layout is not registered. Return ID otherwise.
 */
function genesis_set_default_layout( $id = '' ) {

	global $_genesis_layouts;

	if ( ! is_array( $_genesis_layouts ) ) {
		$_genesis_layouts = array();
	}

	// Don't allow empty $id, or unregistered layouts.
	if ( ! $id || ! isset( $_genesis_layouts[ $id ] ) ) {
		return false;
	}

	// Remove default flag for all other layouts.
	foreach ( (array) $_genesis_layouts as $key => $value ) {
		if ( isset( $_genesis_layouts[ $key ]['default'] ) ) {
			unset( $_genesis_layouts[ $key ]['default'] );
		}
	}

	$_genesis_layouts[ $id ]['default'] = true;

	return $id;

}

/**
 * Unregister a layout in Genesis.
 *
 * Modifies the global $_genesis_layouts variable.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 *
 * @param string $id ID of the layout to unregister.
 * @return bool `false` if ID is empty, or layout is not registered, `true` if unregister is successful.
 */
function genesis_unregister_layout( $id = '' ) {

	global $_genesis_layouts;

	if ( ! $id || ! isset( $_genesis_layouts[ $id ] ) ) {
		return false;
	}

	unset( $_genesis_layouts[ $id ] );

	return true;

}

/**
 * Return all registered Genesis layouts.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 *
 * @param string $type Layout type to return. Leave empty to return all types.
 * @return array Registered layouts.
 */
function genesis_get_layouts( $type = 'site' ) {

	global $_genesis_layouts;

	// If no layouts exists, return empty array.
	if ( ! is_array( $_genesis_layouts ) ) {
		$_genesis_layouts = array();
		return $_genesis_layouts;
	}

	$layouts = array();

	$types = array_reverse( (array) $type );

	// Default fallback is site.
	$types[] = 'site';

	if ( is_numeric( $types[0] ) ) {
		$id = $types[0];
		$types[0] = $types[1] . '-' . $types[0];
	}

	// Cycle through looking for layouts of $type.
	foreach ( (array) $types as $type ) {
		foreach ( (array) $_genesis_layouts as $id => $data ) {
			if ( in_array( $type, $data['type'] ) ) {
				$layouts[ $id ] = $data;
			}
		}
		if ( $layouts ) {
			break;
		}
	}

	/**
	 * Filter the layouts array.
	 *
	 * Allows developer to filter the array of layouts returned.
	 *
	 * @since 2.5.0
	 *
	 * @param array  $layouts Layout data.
	 * @param string $type 	  Layout type.
	 */
	$layouts = (array) apply_filters( 'genesis_get_layouts', $layouts, $type );

	return $layouts;

}

/**
 * Return registered layouts in a format the WordPress Customizer accepts.
 *
 * @since 2.0.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 *
 * @param string $type Layout type to return. Leave empty to return all types.
 * @return array Registered layouts.
 */
function genesis_get_layouts_for_customizer( $type = 'site' ) {

	$layouts = genesis_get_layouts( $type );

	if ( empty( $layouts ) ) {
		return $layouts;
	}

	// Simplified layout array.
	foreach ( (array) $layouts as $id => $data ) {
		$customizer_layouts[ $id ] = $data['label'];
	}

	return $customizer_layouts;

}

/**
 * Return the data from a single layout, specified by the $id passed to it.
 *
 * @since 1.4.0
 *
 * @param string $id   ID of the layout to return data for.
 * @param string $type Optional. Layout type. Default is 'site'.
 * @return null|array `null` if ID is not set, or layout is not registered. Array of layout data
 *                    otherwise, with 'label' and 'image' (and possibly 'default') sub-keys.
 */
function genesis_get_layout( $id, $type = 'site' ) {

	$layouts = genesis_get_layouts( $type );

	if ( ! $id || ! isset( $layouts[ $id ] ) ) {
		return null;
	}

	return $layouts[ $id ];

}

/**
 * Return the layout that is set to default.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 *
 * @param string $type Optional. Type of layout. Default is 'site'.
 * @return string Return ID of the layout, or `nolayout`.
 */
function genesis_get_default_layout( $type = 'site' ) {

	$layouts = genesis_get_layouts( $type );

	$default = 'nolayout';

	foreach ( (array) $layouts as $key => $value ) {
		if ( isset( $value['default'] ) && $value['default'] ) {
			$default = $key;
			break;
		}
	}

	return $default;

}

/**
 * Determine if the site has more than 1 registered layouts.
 *
 * @since 2.3.0
 *
 * @param string $type Optional. Type of layout. Default is 'site'.
 * @return bool `true` if more than one layout, `false` otherwise.
 */
function genesis_has_multiple_layouts( $type = 'site' ) {

	$layouts = genesis_get_layouts( $type );

	return count( $layouts ) > 1;

}

/**
 * Return the site layout for different contexts.
 *
 * Checks both the custom field and the theme option to find the user-selected site layout, and returns it.
 *
 * Applies `genesis_site_layout` filter early to allow shortcutting of function.
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query Query object.
 *
 * @param bool $use_cache Conditional to use cache or get fresh.
 * @return string Key of site layout or filtered value of `genesis_site_layout`.
 */
function genesis_site_layout( $use_cache = true ) {

	// Allow child theme to short-circuit this function.
	$pre = apply_filters( 'genesis_site_layout', null );
	if ( null !== $pre ) {
		return $pre;
	}

	// If we're supposed to use the cache, setup cache. Use if value exists.
	if ( $use_cache ) {

		// Setup cache.
		static $layout_cache = '';

		// If cache is populated, return value.
		if ( '' !== $layout_cache ) {
			return esc_attr( $layout_cache );
		}

	}

	global $wp_query;

	// Default to site for layout type.
	$type = 'site';

	if ( is_singular() || ( is_home() && ! genesis_is_root_page() ) ) { // If viewing a singular page or post, or the posts page, but not the front page.

		$post_id      = is_home() ? get_option( 'page_for_posts' ) : null;
		$custom_field = genesis_get_custom_field( '_genesis_layout', $post_id );
		$site_layout  = $custom_field ? $custom_field : genesis_get_option( 'site_layout' );
		$type         = array( 'singular', get_post_type(), $post_id );

	} elseif ( is_category() || is_tag() || is_tax() ) { // If viewing a taxonomy archive.

		$term        = $wp_query->get_queried_object();
		$term_layout = $term ? get_term_meta( $term->term_id, 'layout', true ) : '';
		$site_layout = $term_layout ? $term_layout : genesis_get_option( 'site_layout' );
		$type        = array( 'archive', $term->taxonomy, $term->term_id );

	} elseif ( is_post_type_archive() && genesis_has_post_type_archive_support() ) { // If viewing a supported post type.

		$site_layout = genesis_get_cpt_option( 'layout' ) ? genesis_get_cpt_option( 'layout' ) : genesis_get_option( 'site_layout' );
		$type        = array( 'archive', 'post-type-archive-' . get_post_type() );

	} elseif ( is_author() ) { // If viewing an author archive.

		$site_layout = get_the_author_meta( 'layout', (int) get_query_var( 'author' ) ) ? get_the_author_meta( 'layout', (int) get_query_var( 'author' ) ) : genesis_get_option( 'site_layout' );
		$type        = array( 'archive', 'author', get_query_var( 'author' ) );

	} else { // Else pull the theme option.

		$site_layout = genesis_get_option( 'site_layout' );

	}

	// Use default layout as a fallback, if necessary.
	if ( ! genesis_get_layout( $site_layout, $type ) ) {
		$site_layout = genesis_get_default_layout();
	}

	// Push layout into cache, if caching turned on.
	if ( $use_cache ) {
		$layout_cache = $site_layout;
	}

	// Return site layout.
	return esc_attr( $site_layout );

}

/**
 * Output the form elements necessary to select a layout.
 *
 * You must manually wrap this in an HTML element with the class of `genesis-layout-selector` in order for the CSS and
 * JavaScript to apply properly.
 *
 * Supported `$args` keys are:
 *  - name     (default is ''),
 *  - selected (default is ''),
 *  - echo     (default is true).
 *
 * The Genesis admin script is enqueued to ensure the layout selector behaviour (amending label class to add border on
 * selected layout) works.
 *
 * @since 1.7.0
 *
 * @param array $args Optional. Function arguments. Default is empty array.
 * @return null|string HTML markup of labels, images and radio inputs for layout selector.
 */
function genesis_layout_selector( $args = array() ) {

	// Enqueue the JavaScript.
	genesis_scripts()->enqueue_and_localize_admin_scripts();

	// Merge defaults with user args.
	$args = wp_parse_args(
		$args,
		array(
			'name'     => '',
			'selected' => '',
			'type'     => 'type',
			'echo'     => true,
		)
	);

	$output = '';

	foreach ( genesis_get_layouts( $args['type'] ) as $id => $data ) {
		$class = $id == $args['selected'] ? ' selected' : '';

		$output .= sprintf(
			'<label class="box%2$s" for="%5$s"><span class="screen-reader-text">%1$s </span><img src="%3$s" alt="%1$s" /><input type="radio" name="%4$s" id="%5$s" value="%5$s" %6$s class="screen-reader-text" /></label>',
			esc_attr( $data['label'] ),
			esc_attr( $class ),
			esc_url( $data['img'] ),
			esc_attr( $args['name'] ),
			esc_attr( $id ),
			checked( $id, $args['selected'], false )
		);
	}

	// Echo or return output.
	if ( $args['echo'] ) {
		echo $output;

		return null;
	} else {
		return $output;
	}

}

/**
 * Return a structural wrap div.
 *
 * A check is made to see if the `$context` is in the `genesis-structural-wraps` theme support data. If so, then the
 * `$output` may be echoed or returned.
 *
 * @since 2.7.0
 *
 * @param string $context The location ID.
 * @param string $output  Optional. The markup to include. Can also be 'open'
 *                        (default) or 'close' to use pre-determined markup for consistency.
 * @return null|string Wrap HTML, or `null` if `genesis-structural-wraps` support is falsy.
 */
function genesis_get_structural_wrap( $context = '', $output = 'open' ) {

	$wraps = get_theme_support( 'genesis-structural-wraps' );

	// If theme doesn't support structural wraps, bail.
	if ( ! $wraps ) {
		return null;
	}

	// Map of old $contexts to new $contexts.
	$map = array(
		'nav'    => 'menu-primary',
		'subnav' => 'menu-secondary',
		'inner'  => 'site-inner',
	);

	// Make the swap, if necessary.
	$swap = array_search( $context, $map, true );
	if ( $swap && in_array( $swap, $wraps[0], true ) ) {
		$wraps[0] = str_replace( $swap, $map[ $swap ], $wraps[0] );
	}

	if ( ! in_array( $context, (array) $wraps[0] ) ) {
		return '';
	}

	// Save original output param.
	$original_output = $output;

	switch ( $output ) {
		case 'open':
			$output = sprintf( '<div %s>', genesis_attr( 'structural-wrap' ) );
			break;
		case 'close':
			$output = '</div>';
			break;
	}

	$output = apply_filters( "genesis_structural_wrap-{$context}", $output, $original_output );

	return $output;

}

/**
 * Echo a structural wrap div.
 *
 * A check is made to see if the `$context` is in the `genesis-structural-wraps` theme support data. If so, then the
 * `$output` may be echoed or returned.
 *
 * @since 1.6.0
 * @since 2.7.0 Logic moved to `genesis_get_structural_wrap()` and third parameter deprecated.
 *
 * @param string $context    The location ID.
 * @param string $output     Optional. The markup to include. Can also be 'open'
 *                           (default) or 'close' to use pre-determined markup for consistency.
 * @param bool   $deprecated Deprecated.
 * @return null|string Wrap HTML, or `null` if `genesis-structural-wraps` support is falsy.
 */
function genesis_structural_wrap( $context = '', $output = 'open', $deprecated = null ) {

	if ( null !== $deprecated ) {
		$message = 'The default is true, so remove the third argument.';

		if ( false === (bool) $deprecated ) {
			$message = 'Use `genesis_get_structural_wrap()` instead.';
		}

		_deprecated_argument( __FUNCTION__, '2.7.0', $message );
	}

	$output = genesis_get_structural_wrap( $context, $output );

	// Apply original default value.
	$deprecated = null === $deprecated ? true : $deprecated;

	if ( false === (bool) $deprecated ) { // Kept for backwards compatibility.
		return $output;
	}

	echo $output;

}

/**
 * Return layout key 'content-sidebar'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'content-sidebar'`.
 */
function __genesis_return_content_sidebar() {

	return 'content-sidebar';

}

/**
 * Return layout key 'sidebar-content'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'sidebar-content'`.
 */
function __genesis_return_sidebar_content() {

	return 'sidebar-content';

}

/**
 * Return layout key 'content-sidebar-sidebar'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'content-sidebar-sidebar'`.
 */
function __genesis_return_content_sidebar_sidebar() {

	return 'content-sidebar-sidebar';

}

/**
 * Return layout key 'sidebar-sidebar-content'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'sidebar-sidebar-content'`.
 */
function __genesis_return_sidebar_sidebar_content() {

	return 'sidebar-sidebar-content';

}

/**
 * Return layout key 'sidebar-content-sidebar'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'sidebar-content-sidebar'`.
 */
function __genesis_return_sidebar_content_sidebar() {

	return 'sidebar-content-sidebar';

}

/**
 * Return layout key 'full-width-content'.
 *
 * Used as shortcut second parameter for `add_filter()`.
 *
 * @since 1.7.0
 *
 * @return string `'full-width-content'`.
 */
function __genesis_return_full_width_content() {

	return 'full-width-content';

}
