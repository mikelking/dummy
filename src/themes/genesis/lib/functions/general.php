<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package StudioPress\Genesis
 * @author  StudioPress
 * @license GPL-2.0-or-later
 * @link    https://my.studiopress.com/themes/genesis/
 */

/**
 * Enable the author box for ALL users.
 *
 * @since 1.4.1
 *
 * @param array $args Optional. Arguments for enabling author box. Default is empty array.
 */
function genesis_enable_author_box( $args = array() ) {

	$args = wp_parse_args(
		$args,
		array(
			'type' => 'single',
		)
	);

	if ( 'single' === $args['type'] ) {
		add_filter( 'get_the_author_genesis_author_box_single', '__return_true' );
	} elseif ( 'archive' === $args['type'] ) {
		add_filter( 'get_the_author_genesis_author_box_archive', '__return_true' );
	}

}

/**
 * Redirect the user to an admin page, and add query args to the URL string for alerts, etc.
 *
 * @since 1.6.0
 *
 * @param string $page       Menu slug.
 * @param array  $query_args Optional. Associative array of query string arguments (key => value). Default is an empty array.
 * @return void Return early if first argument, `$page`, is falsy.
 */
function genesis_admin_redirect( $page, array $query_args = array() ) {

	if ( ! $page ) {
		return;
	}

	$url = html_entity_decode( menu_page_url( $page, 0 ) );

	foreach ( (array) $query_args as $key => $value ) {
		if ( empty( $key ) && empty( $value ) ) {
			unset( $query_args[ $key ] );
		}
	}

	$url = add_query_arg( $query_args, $url );

	wp_safe_redirect( esc_url_raw( $url ) );

}

add_action( 'template_redirect', 'genesis_custom_field_redirect', 20 );
/**
 * Redirect singular page to an alternate URL.
 *
 * @since 2.0.0
 *
 * @return void Return early if not a singular entry.
 */
function genesis_custom_field_redirect() {

	if ( ! is_singular() ) {
		return;
	}

	$url = genesis_get_custom_field( 'redirect' );
	if ( $url ) {
		wp_redirect( esc_url_raw( $url ), 301 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Can't whitelist what we don't know.
		exit;
	}

}

/**
 * Return a specific value from the array passed as the second argument to `add_theme_support()`.
 *
 * Supports associative and index array of theme support arguments.
 *
 * @since 1.9.0
 *
 * @param string $feature The theme feature.
 * @param string $arg     The theme feature argument.
 * @param string $default Optional. Fallback if value is blank or doesn't exist.
 *                        Default is empty string.
 * @return mixed Return value if associative array, true if indexed array, or
 *               `$default` if theme does not support `$feature` or `$arg` does not exist.
 */
function genesis_get_theme_support_arg( $feature, $arg, $default = '' ) {

	$support = get_theme_support( $feature );

	if ( ! $arg && $support ) {
		return true;
	}

	if ( ! $support || ! isset( $support[0] ) ) {
		return $default;
	}

	if ( array_key_exists( $arg, (array) $support[0] ) ) {
		return $support[0][ $arg ];
	}

	if ( in_array( $arg, (array) $support[0] ) ) {
		return true;
	}

	return $default;

}

/**
 * Check if the environment is in development mode via SCRIPT_DEBUG constant.
 *
 * @since 3.0.0
 *
 * @return bool True when debugging scripts, otherwise false.
 */
function genesis_is_in_dev_mode() {

	return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

}

/**
 * Gets the theme handle from the 'Theme Name' header in style.css.
 *
 * Uses 'Name' instead of 'Text Domain' because the Theme Name header is more
 * commonly set and maintained.
 *
 * @since 3.0.0
 *
 * @return string The theme's handle.
 */
function genesis_get_theme_handle() {

	static $handle;

	if ( is_null( $handle ) ) {
		$handle = sanitize_title_with_dashes( wp_get_theme()->get( 'Name' ) );
	}

	return $handle;

}

/**
 * Gets the active theme version from style.css in production, or a timestamp if SCRIPT_DEBUG is true.
 *
 * @since 3.0.0
 *
 * @return string Theme version or current Unix timestamp for use as a cache-busting string.
 */
function genesis_get_theme_version() {

	if ( genesis_is_in_dev_mode() ) {
		return (string) time();
	}

	static $version;

	if ( is_null( $version ) ) {
		$version = wp_get_theme()->get( 'Version' );
	}

	return $version;

}

/**
 * Locate and require a config file.
 *
 * First, search child theme for the config. If config file doesn't exist in the child,
 * search the parent for the config file.
 *
 * @since 2.8.0
 *
 * @param string $config The config file to look for (not including .php file extension).
 * @return array The config data.
 */
function genesis_get_config( $config ) {

	$parent_file = sprintf( '%s/config/%s.php', get_template_directory(), $config );
	$child_file  = sprintf( '%s/config/%s.php', get_stylesheet_directory(), $config );

	$data = array();

	if ( is_readable( $child_file ) ) {
		$data = require $child_file;
	}

	if ( empty( $data ) && is_readable( $parent_file ) ) {
		$data = require $parent_file;
	}

	return (array) $data;

}

/**
 * Detect active plugin by constant, class or function existence.
 *
 * @since 1.6.0
 *
 * @param array $plugins Array of array for constants, classes and / or functions to check for plugin existence.
 * @return bool True if plugin exists or false if plugin constant, class or function not detected.
 */
function genesis_detect_plugin( array $plugins ) {

	// Check for classes.
	if ( isset( $plugins['classes'] ) ) {
		foreach ( $plugins['classes'] as $name ) {
			if ( class_exists( $name ) ) {
				return true;
			}
		}
	}

	// Check for functions.
	if ( isset( $plugins['functions'] ) ) {
		foreach ( $plugins['functions'] as $name ) {
			if ( function_exists( $name ) ) {
				return true;
			}
		}
	}

	// Check for constants.
	if ( isset( $plugins['constants'] ) ) {
		foreach ( $plugins['constants'] as $name ) {
			if ( defined( $name ) ) {
				return true;
			}
		}
	}

	// No class, function or constant found to exist.
	return false;

}

/**
 * Check that we're targeting a specific Genesis admin page.
 *
 * The `$pagehook` argument is expected to be one of 'genesis', 'seo-settings' or 'genesis-import-export' although
 * others can be accepted.
 *
 * @since 1.8.0
 *
 * @global string $page_hook Page hook for current page.
 *
 * @param string $pagehook Page hook string to check.
 * @return bool Return `true` if the global `$page_hook` matches given `$pagehook`, `false` otherwise.
 */
function genesis_is_menu_page( $pagehook = '' ) {

	global $page_hook;

	if ( isset( $page_hook ) && $page_hook === $pagehook ) {
		return true;
	}

	// May be too early for $page_hook.
	if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === $pagehook ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification -- Checking request inside wp-admin, no further processing.
		return true;
	}

	return false;

}

/**
 * Check whether we are currently viewing the site via the WordPress Customizer.
 *
 * @since 2.0.0
 *
 * @global WP_Customize_Manager $wp_customize Customizer instance.
 *
 * @return bool Return true if viewing page via Customizer, false otherwise.
 */
function genesis_is_customizer() {

	global $wp_customize;

	return is_a( $wp_customize, 'WP_Customize_Manager' ) && $wp_customize->is_preview();

}

/**
 * Determine if the Blog template is being used.
 *
 * `is_page_template()` is not available within the loop or any loop that
 * modifies $wp_query because it changes all the conditionals therein.
 * Since the conditionals change, is_page() no longer returns true, thus
 * is_page_template() will always return false.
 *
 * @since 2.1.0
 *
 * @link http://codex.wordpress.org/Function_Reference/is_page_template#Cannot_Be_Used_Inside_The_Loop
 *
 * @return bool True if Blog template is being used, false otherwise.
 */
function genesis_is_blog_template() {

	global $wp_the_query;

	return 'page_blog.php' === get_post_meta( $wp_the_query->get_queried_object_id(), '_wp_page_template', true );

}

/**
 * Get the `post_type` from the global `$post` if supplied value is empty.
 *
 * @since 2.0.0
 *
 * @param string $post_type_name Post type name.
 * @return string Post type name of global `$post`.
 */
function genesis_get_global_post_type_name( $post_type_name = '' ) {

	if ( ! $post_type_name ) {
		$post_type_name = get_post_type();
		if ( false === get_post_type() ) {
			$post_type_name = get_query_var( 'post_type' );
		}
	}

	return $post_type_name;

}

/**
 * Get list of custom post type objects which need an archive settings page.
 *
 * Archive settings pages are added for CPTs that:
 *
 * - are public,
 * - are set to show the UI,
 * - are set to show in the admin menu,
 * - have an archive enabled,
 * - not one of the built-in types,
 * - support "genesis-cpt-archives-settings".
 *
 * This last item means that if you're using an archive template and don't want Genesis interfering with it with these
 * archive settings, then don't add the support. This support check is handled in
 * {@link genesis_has_post_type_archive_support()}.
 *
 * Applies the `genesis_cpt_archives_args` filter, to change the conditions for which post types are deemed valid.
 *
 * The results are held in a static variable, since they won't change over the course of a request.
 *
 * @since 2.0.0
 *
 * @return array A list of post type names or objects.
 */
function genesis_get_cpt_archive_types() {

	static $genesis_cpt_archive_types;
	if ( $genesis_cpt_archive_types ) {
		return $genesis_cpt_archive_types;
	}

	$args = apply_filters(
		'genesis_cpt_archives_args',
		array(
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'has_archive'  => true,
			'_builtin'     => false,
		)
	);

	$genesis_cpt_archive_types = get_post_types( $args, 'objects' );

	return $genesis_cpt_archive_types;

}

/**
 * Get list of custom post type names which need an archive settings page.
 *
 * @since 2.0.0
 *
 * @return array Custom post type names.
 */
function genesis_get_cpt_archive_types_names() {

	$post_type_names = array();
	foreach ( genesis_get_cpt_archive_types() as $post_type ) {
		$post_type_names[] = $post_type->name;
	}

	return $post_type_names;

}

/**
 * Check if a post type supports an archive setting page.
 *
 * @since 2.0.0
 *
 * @param string $post_type_name Post type name.
 * @return bool `true` if custom post type name has `genesis-cpt-archives-settings` support, `false` otherwise.
 */
function genesis_has_post_type_archive_support( $post_type_name = '' ) {

	$post_type_name = genesis_get_global_post_type_name( $post_type_name );

	return in_array( $post_type_name, genesis_get_cpt_archive_types_names() ) &&
		post_type_supports( $post_type_name, 'genesis-cpt-archives-settings' );

}

/**
 * Determine if HTML5 is activated by the child theme.
 *
 * @since 2.0.0
 *
 * @return bool `true` if current theme supports `html5`, `false` otherwise.
 */
function genesis_html5() {

	return current_theme_supports( 'html5' );

}

/**
 * Determine if theme support genesis-accessibility is activated by the child theme.
 * Assumes the presence of a screen-reader-text class in the stylesheet (required generated class as from WordPress 4.2)
 *
 * Adds screen-reader-text by default.
 * Skip links to primary navigation, main content, sidebars and footer, semantic headings and a keyboard accessible dropdown menu
 * can be added as extra features as: 'skip-links', 'headings', 'drop-down-menu'
 *
 * @since 2.2.0
 *
 * @param string $arg Optional. Specific accessibility feature to check for support. Default is screen-reader-text.
 * @return bool `true` if current theme supports `genesis-accessibility`, or a specific feature of it, `false` otherwise.
 */
function genesis_a11y( $arg = 'screen-reader-text' ) {

	$feature = 'genesis-accessibility';

	if ( 'screen-reader-text' === $arg ) {
		return current_theme_supports( $feature );
	}

	$support = get_theme_support( $feature );

	// No support for feature.
	if ( ! $support ) {
		return false;
	}

	// No args passed in to add_theme_support(), so accept none.
	if ( ! isset( $support[0] ) ) {
		return false;
	}

	// Support for specific arg found.
	if ( in_array( $arg, $support[0] ) ) {
		return true;
	}

	return false;

}

/**
 * Display a HTML sitemap.
 *
 * Used in `page_archive.php` and `404.php`.
 *
 * @see genesis_get_sitemap()
 *
 * @since 2.2.0
 *
 * @param string $heading Optional. Heading element. Default is `h2`.
 */
function genesis_sitemap( $heading = 'h2' ) {

	echo wp_kses_post( genesis_get_sitemap( $heading ) );

}

/**
 * Get markup for a HTML sitemap.
 *
 * Can be filtered with `genesis_sitemap_output`.
 *
 * If the number of published posts is 0, then Categories, Authors,
 * Monthly and Recent Posts headings will not be shown.
 *
 * $heading:  genesis_a11y( 'headings' ) ? 'h2' : 'h4' );
 *
 * @since 2.4.0
 *
 * @param string $heading Optional. Heading element. Default is `h2`.
 * @return string $heading Sitemap content.
 */
function genesis_get_sitemap( $heading = 'h2' ) {

	/**
	 * Filter the sitemap before the default sitemap is built.
	 *
	 * @since 2.5.0
	 *
	 * @param null $sitemap Null value. Change to something else to have that be returned.
	 */
	$pre = apply_filters( 'genesis_pre_get_sitemap', null );
	if ( null !== $pre ) {
		return $pre;
	}

	$sitemap  = sprintf( '<%2$s>%1$s</%2$s>', __( 'Pages:', 'genesis' ), $heading );
	$sitemap .= sprintf( '<ul>%s</ul>', wp_list_pages( 'title_li=&echo=0' ) );

	$post_counts = wp_count_posts();
	if ( $post_counts->publish > 0 ) {
		$sitemap .= sprintf( '<%2$s>%1$s</%2$s>', __( 'Categories:', 'genesis' ), $heading );
		$sitemap .= sprintf( '<ul>%s</ul>', wp_list_categories( 'sort_column=name&title_li=&echo=0' ) );

		$sitemap .= sprintf( '<%2$s>%1$s</%2$s>', __( 'Authors:', 'genesis' ), $heading );
		$sitemap .= sprintf( '<ul>%s</ul>', wp_list_authors( 'exclude_admin=0&optioncount=1&echo=0' ) );

		$sitemap .= sprintf( '<%2$s>%1$s</%2$s>', __( 'Monthly:', 'genesis' ), $heading );
		$sitemap .= sprintf( '<ul>%s</ul>', wp_get_archives( 'type=monthly&echo=0' ) );

		$sitemap .= sprintf( '<%2$s>%1$s</%2$s>', __( 'Recent Posts:', 'genesis' ), $heading );
		$sitemap .= sprintf( '<ul>%s</ul>', wp_get_archives( 'type=postbypost&limit=100&echo=0' ) );
	}

	/**
	 * Filter the sitemap.
	 *
	 * @since 2.2.0
	 *
	 * @param string $sitemap Default sitemap.
	 */
	return apply_filters( 'genesis_sitemap_output', $sitemap );

}

/**
 * Build links to install plugins.
 *
 * @since 2.0.0
 *
 * @param string $plugin_slug Plugin slug.
 * @param string $text        Plugin name.
 * @return string HTML markup for links.
 */
function genesis_plugin_install_link( $plugin_slug = '', $text = '' ) {

	$page = 'plugin-install.php';
	$args = array(
		'tab'       => 'plugin-information',
		'TB_iframe' => true,
		'width'     => 600,
		'height'    => 550,
		'plugin'    => $plugin_slug,
	);

	$url = add_query_arg( $args, admin_url( $page ) );

	if ( is_main_site() ) {
		$url = add_query_arg( $args, network_admin_url( $page ) );
	}

	return sprintf( '<a href="%s" class="thickbox">%s</a>', esc_url( $url ), esc_html( $text ) );

}

/**
 * Check if the root page of the site is being viewed.
 *
 * `is_front_page()` returns false for the root page of a website when
 * - the WordPress "Front page displays" setting is set to "A static page"
 * - "Front page" is left undefined
 * - "Posts page" is assigned to an existing page
 *
 * This function checks for is_front_page() or the root page of the website
 * in this edge case.
 *
 * @since 2.2.0
 *
 * @return bool `true` if this is the root page of the site, `false` otherwise.
 */
function genesis_is_root_page() {

	return is_front_page() || ( is_home() && get_option( 'page_for_posts' ) && ! get_option( 'page_on_front' ) && ! get_queried_object() );

}

/**
 * Calculate and return the canonical URL.
 *
 * @since 2.2.0
 *
 * @return null|string The canonical URL if one exists, `null` otherwise.
 */
function genesis_canonical_url() {

	global $wp_query;

	$canonical = '';

	$paged = (int) get_query_var( 'paged' );
	$page  = (int) get_query_var( 'page' );

	if ( is_front_page() ) {
		if ( $paged ) {
			$canonical = get_pagenum_link( $paged );
		} else {
			$canonical = trailingslashit( home_url() );
		}
	}

	if ( is_singular() ) {
		$numpages = substr_count( $wp_query->post->post_content, '<!--nextpage-->' ) + 1;
		$id       = $wp_query->get_queried_object_id();

		if ( ! $id ) {
			return null;
		}

		$cf = genesis_get_custom_field( '_genesis_canonical_uri' );

		if ( $cf ) {
			$canonical = $cf;
		} elseif ( $numpages > 1 && $page > 1 ) {
			$canonical = genesis_paged_post_url( $page, $id );
		} else {
			$canonical = get_permalink( $id );
		}
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$id = $wp_query->get_queried_object_id();

		if ( ! $id ) {
			return null;
		}

		$taxonomy = $wp_query->queried_object->taxonomy;

		$canonical = $paged ? get_pagenum_link( $paged ) : get_term_link( (int) $id, $taxonomy );
	}

	if ( is_author() ) {
		$id = $wp_query->get_queried_object_id();

		if ( ! $id ) {
			return null;
		}

		$canonical = $paged ? get_pagenum_link( $paged ) : get_author_posts_url( $id );
	}

	if ( is_search() ) {
		$canonical = get_search_link();
	}

	return apply_filters( 'genesis_canonical_url', $canonical );

}

/**
 * Checks if this web page is an AMP URL.
 *
 * @since 2.7.0
 *
 * @return bool `true` if AMP URL; else `false`.
 */
function genesis_is_amp() {

	// If the AMP plugin is not installed, bail out and return `false`.
	if ( ! function_exists( 'is_amp_endpoint' ) ) {
		return false;
	}

	return is_amp_endpoint();

}
