<?php
/**
 * F4D functions and definitions
 *
 * @package F4D
 * @since F4D 1.3.1
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since F4D 1.0
 */
if ( ! isset( $content_width ) )
	$content_width = 790; /* Default the embedded content width to 790px */


/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @since F4D 1.0
 *
 * @return void
 */
if ( ! function_exists( 'f4d_setup' ) ) {
	function f4d_setup() {
		global $content_width;

		/**
		 * Make theme available for translation
		 * Translations can be filed in the /languages/ directory
		 * If you're building a theme based on F4D, use a find and replace
		 * to change 'f4d' to the name of your theme in all the template files
		 */
		load_theme_textdomain( 'f4d', trailingslashit( get_template_directory() ) . 'languages' );

		// This theme styles the visual editor with editor-style.css to match the theme style.
		add_editor_style();

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Enable support for Post Thumbnails
		add_theme_support( 'post-thumbnails' );

		// Create an extra image size for the Post featured image
		add_image_size( 'post_feature_full_width', 792, 300, true );

		// This theme uses wp_nav_menu() in one location
		register_nav_menus( array(
				'primary' => esc_html__( 'Primary Menu', 'f4d' )
			) );

		// This theme supports a variety of post formats
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );

		// Enable support for Custom Backgrounds
		add_theme_support( 'custom-background', array(
				// Background color default
				'default-color' => 'fff',
				// Background image default
				'default-image' => trailingslashit( get_template_directory_uri() ) . 'images/faint-squares.jpg'
			) );

		// Enable support for Custom Headers (or in our case, a custom logo)
		add_theme_support( 'custom-header', array(
				// Header image default
				'default-image' => trailingslashit( get_template_directory_uri() ) . 'images/logo.png',
				// Header text display default
				'header-text' => false,
				// Header text color default
				'default-text-color' => '000',
				// Flexible width
				'flex-width' => true,
				// Header image width (in pixels)
				'width' => 300,
				// Flexible height
				'flex-height' => true,
				// Header image height (in pixels)
				'height' => 80
			) );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		// Enable support for WooCommerce
		add_theme_support( 'woocommerce' );

		// Enable support for Theme Options.
		// Rather than reinvent the wheel, we're using the Options Framework by Devin Price, so huge props to him!
		// http://wptheming.com/options-framework-theme/
		if ( !function_exists( 'optionsframework_init' ) ) {
			define( 'OPTIONS_FRAMEWORK_DIRECTORY', trailingslashit( get_template_directory_uri() ) . 'inc/' );
			require_once trailingslashit( dirname( __FILE__ ) ) . 'inc/options-framework.php';

			// Loads options.php from child or parent theme
			$optionsfile = locate_template( 'options.php' );
			load_template( $optionsfile );
		}

		// If WooCommerce is running, check if we should be displaying the Breadcrumbs
		if( f4d_is_woocommerce_active() && !of_get_option( 'woocommerce_breadcrumbs', '1' ) ) {
			add_action( 'init', 'f4d_remove_woocommerce_breadcrumbs' );
		}
	}
}
add_action( 'after_setup_theme', 'f4d_setup' );


/**
 * Enable backwards compatability for title-tag support
 *
 * @since F4D 1.3
 *
 * @return void
 */
if ( ! function_exists( '_wp_render_title_tag' ) ) {
	function f4d_slug_render_title() { ?>
		<title><?php wp_title( '|', true, 'right' ); ?></title>
	<?php }
	add_action( 'wp_head', 'f4d_slug_render_title' );
}


/**
 * Returns the Google font stylesheet URL, if available.
 *
 * The use of PT Sans and Arvo by default is localized. For languages that use characters not supported by the fonts, the fonts can be disabled.
 *
 * @since F4D 1.2.5
 *
 * @return string Font stylesheet or empty string if disabled.
 */
function f4d_fonts_url() {
	$fonts_url = '';
	$subsets = 'latin';

	/* translators: If there are characters in your language that are not supported by PT Sans, translate this to 'off'.
	 * Do not translate into your own language.
	 */
	$pt_sans = _x( 'on', 'PT Sans font: on or off', 'f4d' );

	/* translators: To add an additional PT Sans character subset specific to your language, translate this to 'greek', 'cyrillic' or 'vietnamese'.
	 * Do not translate into your own language.
	 */
	$subset = _x( 'no-subset', 'PT Sans font: add new subset (cyrillic)', 'f4d' );

	if ( 'cyrillic' == $subset )
		$subsets .= ',cyrillic';

	/* translators: If there are characters in your language that are not supported by Arvo, translate this to 'off'.
	 * Do not translate into your own language.
	 */
	$arvo = _x( 'on', 'Arvo font: on or off', 'f4d' );

	if ( 'off' !== $pt_sans || 'off' !== $arvo ) {
		$font_families = array();

		if ( 'off' !== $pt_sans )
			$font_families[] = 'Bitter:700';

		if ( 'off' !== $arvo )
			$font_families[] = 'Palanquin';

		$protocol = is_ssl() ? 'https' : 'http';
		$query_args = array(
			'family' => implode( '|', $font_families ),
			'subset' => $subsets,
		);
		$fonts_url = add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" );
	}

	return $fonts_url;
}


/**
 * Adds additional stylesheets to the TinyMCE editor if needed.
 *
 * @since F4D 1.2.5
 *
 * @param string $mce_css CSS path to load in TinyMCE.
 * @return string The filtered CSS paths list.
 */
function f4d_mce_css( $mce_css ) {
	$fonts_url = f4d_fonts_url();

	if ( empty( $fonts_url ) ) {
		return $mce_css;
	}

	if ( !empty( $mce_css ) ) {
		$mce_css .= ',';
	}

	$mce_css .= esc_url_raw( str_replace( ',', '%2C', $fonts_url ) );

	return $mce_css;
}
add_filter( 'mce_css', 'f4d_mce_css' );


/**
 * Register widgetized areas
 *
 * @since F4D 1.0
 *
 * @return void
 */
function f4d_widgets_init() {
	register_sidebar( array(
			'name' => esc_html__( 'Main Sidebar', 'f4d' ),
			'id' => 'sidebar-main',
			'description' => esc_html__( 'Appears in the sidebar on posts and pages except the optional Front Page template, which has its own widgets', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Blog Sidebar', 'f4d' ),
			'id' => 'sidebar-blog',
			'description' => esc_html__( 'Appears in the sidebar on the blog and archive pages only', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Single Post Sidebar', 'f4d' ),
			'id' => 'sidebar-single',
			'description' => esc_html__( 'Appears in the sidebar on single posts only', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Page Sidebar', 'f4d' ),
			'id' => 'sidebar-page',
			'description' => esc_html__( 'Appears in the sidebar on pages only', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'First Front Page Banner Widget', 'f4d' ),
			'id' => 'frontpage-banner1',
			'description' => esc_html__( 'Appears in the banner area on the Front Page', 'f4d' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h1 class="widget-title">',
			'after_title' => '</h1>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Second Front Page Banner Widget', 'f4d' ),
			'id' => 'frontpage-banner2',
			'description' => esc_html__( 'Appears in the banner area on the Front Page', 'f4d' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h1 class="widget-title">',
			'after_title' => '</h1>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'First Front Page Widget Area', 'f4d' ),
			'id' => 'sidebar-homepage1',
			'description' => esc_html__( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Second Front Page Widget Area', 'f4d' ),
			'id' => 'sidebar-homepage2',
			'description' => esc_html__( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Third Front Page Widget Area', 'f4d' ),
			'id' => 'sidebar-homepage3',
			'description' => esc_html__( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Fourth Front Page Widget Area', 'f4d' ),
			'id' => 'sidebar-homepage4',
			'description' => esc_html__( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'First Footer Widget Area', 'f4d' ),
			'id' => 'sidebar-footer1',
			'description' => esc_html__( 'Appears in the footer sidebar', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Second Footer Widget Area', 'f4d' ),
			'id' => 'sidebar-footer2',
			'description' => esc_html__( 'Appears in the footer sidebar', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Third Footer Widget Area', 'f4d' ),
			'id' => 'sidebar-footer3',
			'description' => esc_html__( 'Appears in the footer sidebar', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
			'name' => esc_html__( 'Fourth Footer Widget Area', 'f4d' ),
			'id' => 'sidebar-footer4',
			'description' => esc_html__( 'Appears in the footer sidebar', 'f4d' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		) );

	register_sidebar( array(
		'name' => 'Top Area Widget',
		'id' => 'top_widget',
		'description' => esc_html__( 'Appears in site header', 'f4d' ),
		'before_widget' => '<div class="">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="hidetit">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'f4d_widgets_init' );


/**
 * Enqueue scripts and styles
 *
 * @since F4D 1.0
 *
 * @return void
 */
function f4d_scripts_styles() {

	/**
	 * Register and enqueue our stylesheets
	 */

	// Start off with a clean base by using normalise. If you prefer to use a reset stylesheet or something else, simply replace this
	wp_register_style( 'normalize', trailingslashit( get_template_directory_uri() ) . 'css/normalize.css' , array(), '3.0.2', 'all' );
	wp_enqueue_style( 'normalize' );

	// Register and enqueue our icon font
	// We're using the awesome Font Awesome icon font. http://fortawesome.github.io/Font-Awesome
	wp_register_style( 'fontawesome', trailingslashit( get_template_directory_uri() ) . 'css/font-awesome.min.css' , array( 'normalize' ), '4.2.0', 'all' );
	wp_enqueue_style( 'fontawesome' );

	// Our styles for setting up the grid.
	// If you prefer to use a different grid system, simply replace this and perform a find/replace in the php for the relevant styles. I'm nice like that!
	wp_register_style( 'gridsystem', trailingslashit( get_template_directory_uri() ) . 'css/grid.css' , array( 'fontawesome' ), '1.0.0', 'all' );
	wp_enqueue_style( 'gridsystem' );

	/*
	 * Load our Google Fonts.
	 *
	 * To disable in a child theme, use wp_dequeue_style()
	 * function mytheme_dequeue_fonts() {
	 *     wp_dequeue_style( 'quark-fonts' );
	 * }
	 * add_action( 'wp_enqueue_scripts', 'mytheme_dequeue_fonts', 11 );
	 */
	$fonts_url = f4d_fonts_url();
	if ( !empty( $fonts_url ) ) {
		wp_enqueue_style( 'quark-fonts', esc_url_raw( $fonts_url ), array(), null );
	}

	// If using a child theme, auto-load the parent theme style.
	// Props to Justin Tadlock for this recommendation - http://justintadlock.com/archives/2014/11/03/loading-parent-styles-for-child-themes
	if ( is_child_theme() ) {
		wp_enqueue_style( 'parent-style', trailingslashit( get_template_directory_uri() ) . 'style.css' );
	}

	// Enqueue the default WordPress stylesheet
	wp_enqueue_style( 'style', get_stylesheet_uri() );


	/**
	 * Register and enqueue our scripts
	 */

	// Load Modernizr at the top of the document, which enables HTML5 elements and feature detects
	wp_register_script( 'modernizr', trailingslashit( get_template_directory_uri() ) . 'js/modernizr-2.8.3-min.js', array(), '2.8.3', false );
	wp_enqueue_script( 'modernizr' );

	// Adds JavaScript to pages with the comment form to support sites with threaded comments (when in use)
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Load jQuery Validation as well as the initialiser to provide client side comment form validation
	// You can change the validation error messages below
	if ( is_singular() && comments_open() ) {
		wp_register_script( 'validate', trailingslashit( get_template_directory_uri() ) . 'js/jquery.validate.min.1.13.0.js', array( 'jquery' ), '1.13.0', true );
		wp_register_script( 'commentvalidate', trailingslashit( get_template_directory_uri() ) . 'js/comment-form-validation.js', array( 'jquery', 'validate' ), '1.13.0', true );

		wp_enqueue_script( 'commentvalidate' );
		wp_localize_script( 'commentvalidate', 'comments_object', array(
			'req' => get_option( 'require_name_email' ),
			'author'  => esc_html__( 'Please enter your name', 'f4d' ),
			'email'  => esc_html__( 'Please enter a valid email address', 'f4d' ),
			'comment' => esc_html__( 'Please add a comment', 'f4d' ) )
		);
	}

	// Include this script to envoke a button toggle for the main navigation menu on small screens
	//wp_register_script( 'small-menu', trailingslashit( get_template_directory_uri() ) . 'js/small-menu.js', array( 'jquery' ), '20130130', true );
	//wp_enqueue_script( 'small-menu' );

}
add_action( 'wp_enqueue_scripts', 'f4d_scripts_styles' );


/**
 * Displays navigation to next/previous pages when applicable.
 *
 * @since F4D 1.0
 *
 * @param string html ID
 * @return void
 */
if ( ! function_exists( 'f4d_content_nav' ) ) {
	function f4d_content_nav( $nav_id ) {
		global $wp_query;
		$big = 999999999; // need an unlikely integer

		$nav_class = 'site-navigation paging-navigation';
		if ( is_single() ) {
			$nav_class = 'site-navigation post-navigation nav-single';
		}
		?>
		<nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
			<h3 class="assistive-text"><?php esc_html_e( 'Post navigation', 'f4d' ); ?></h3>

			<?php if ( is_single() ) { // navigation links for single posts ?>

				<?php previous_post_link( '<div class="nav-previous">%link</div>', '<span class="meta-nav">' . _x( '<i class="fa fa-angle-left"></i>', 'Previous post link', 'f4d' ) . '</span> %title' ); ?>
				<?php next_post_link( '<div class="nav-next">%link</div>', '%title <span class="meta-nav">' . _x( '<i class="fa fa-angle-right"></i>', 'Next post link', 'f4d' ) . '</span>' ); ?>

			<?php } 
			elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) { // navigation links for home, archive, and search pages ?>

				<?php echo paginate_links( array(
					'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format' => '?paged=%#%',
					'current' => max( 1, get_query_var( 'paged' ) ),
					'total' => $wp_query->max_num_pages,
					'type' => 'list',
					'prev_text' => wp_kses( __( '<i class="fa fa-angle-left"></i> Previous', 'f4d' ), array( 'i' => array( 
						'class' => array() ) ) ),
					'next_text' => wp_kses( __( 'Next <i class="fa fa-angle-right"></i>', 'f4d' ), array( 'i' => array( 
						'class' => array() ) ) )
				) ); ?>

			<?php } ?>

		</nav><!-- #<?php echo $nav_id; ?> -->
		<?php
	}
}


/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own f4d_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 * (Note the lack of a trailing </li>. WordPress will add it itself once it's done listing any children and whatnot)
 *
 * @since F4D 1.0
 *
 * @param array Comment
 * @param array Arguments
 * @param integer Comment depth
 * @return void
 */
if ( ! function_exists( 'f4d_comment' ) ) {
	function f4d_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) {
		case 'pingback' :
		case 'trackback' :
			// Display trackbacks differently than normal comments ?>
			<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="pingback">
					<p><?php esc_html_e( 'Pingback:', 'f4d' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( esc_html__( '(Edit)', 'f4d' ), '<span class="edit-link">', '</span>' ); ?></p>
				</article> <!-- #comment-##.pingback -->
			<?php
			break;
		default :
			// Proceed with normal comments.
			global $post; ?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="comment">
					<header class="comment-meta comment-author vcard">
						<?php
						echo get_avatar( $comment, 44 );
						printf( '<cite class="fn">%1$s %2$s</cite>',
							get_comment_author_link(),
							// If current post author is also comment author, make it known visually.
							( $comment->user_id === $post->post_author ) ? '<span> ' . esc_html__( 'Post author', 'f4d' ) . '</span>' : '' );
						printf( '<a href="%1$s" title="Posted %2$s"><time itemprop="datePublished" datetime="%3$s">%4$s</time></a>',
							esc_url( get_comment_link( $comment->comment_ID ) ),
							sprintf( esc_html__( '%1$s @ %2$s', 'f4d' ), esc_html( get_comment_date() ), esc_attr( get_comment_time() ) ),
							get_comment_time( 'c' ),
							/* Translators: 1: date, 2: time */
							sprintf( esc_html__( '%1$s at %2$s', 'f4d' ), get_comment_date(), get_comment_time() )
						);
						?>
					</header> <!-- .comment-meta -->

					<?php if ( '0' == $comment->comment_approved ) { ?>
						<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'f4d' ); ?></p>
					<?php } ?>

					<section class="comment-content comment">
						<?php comment_text(); ?>
						<?php edit_comment_link( esc_html__( 'Edit', 'f4d' ), '<p class="edit-link">', '</p>' ); ?>
					</section> <!-- .comment-content -->

					<div class="reply">
						<?php comment_reply_link( array_merge( $args, array( 'reply_text' => wp_kses( __( 'Reply <span>&darr;</span>', 'f4d' ), array( 'span' => array() ) ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					</div> <!-- .reply -->
				</article> <!-- #comment-## -->
			<?php
			break;
		} // end comment_type check
	}
}


/**
 * Update the Comments form so that the 'required' span is contained within the form label.
 *
 * @since F4D 1.0
 *
 * @param string Comment form fields html
 * @return string The updated comment form fields html
 */
function f4d_comment_form_default_fields( $fields ) {

	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? ' aria-required="true"' : "" );

	$fields[ 'author' ] = '<p class="comment-form-author">' . '<label for="author">' . esc_html__( 'Name', 'f4d' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' . '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>';

	$fields[ 'email' ] =  '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'f4d' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' . '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>';

	$fields[ 'url' ] =  '<p class="comment-form-url"><label for="url">' . esc_html__( 'Website', 'f4d' ) . '</label>' . '<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>';

	return $fields;

}
add_action( 'comment_form_default_fields', 'f4d_comment_form_default_fields' );


/**
 * Update the Comments form to add a 'required' span to the Comment textarea within the form label, because it's pointless 
 * submitting a comment that doesn't actually have any text in the comment field!
 *
 * @since F4D 1.0
 *
 * @param string Comment form textarea html
 * @return string The updated comment form textarea html
 */
function f4d_comment_form_field_comment( $field ) {

	$field = '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun', 'f4d' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>';

	return $field;

}
add_action( 'comment_form_field_comment', 'f4d_comment_form_field_comment' );


/**
 * Prints HTML with meta information for current post: author and date
 *
 * @since F4D 1.0
 *
 * @return void
 */
if ( ! function_exists( 'f4d_posted_on' ) ) {
	function f4d_posted_on() {
		$post_icon = '';
		switch ( get_post_format() ) {
			case 'aside':
				$post_icon = 'fa-file-o';
				break;
			case 'audio':
				$post_icon = 'fa-volume-up';
				break;
			case 'chat':
				$post_icon = 'fa-comment';
				break;
			case 'gallery':
				$post_icon = 'fa-camera';
				break;
			case 'image':
				$post_icon = 'fa-picture-o';
				break;
			case 'link':
				$post_icon = 'fa-link';
				break;
			case 'quote':
				$post_icon = 'fa-quote-left';
				break;
			case 'status':
				$post_icon = 'fa-user';
				break;
			case 'video':
				$post_icon = 'fa-video-camera';
				break;
			default:
				$post_icon = 'fa-calendar';
				break;
		}

		// Translators: 1: Icon 2: Permalink 3: Post date and time 4: Publish date in ISO format 5: Post date
		$date = sprintf( '<i class="fa %1$s"></i> <a href="%2$s" title="Posted %3$s" rel="bookmark"><time class="entry-date" datetime="%4$s" itemprop="datePublished">%5$s</time></a>',
			$post_icon,
			esc_url( get_permalink() ),
			sprintf( esc_html__( '%1$s @ %2$s', 'f4d' ), esc_html( get_the_date() ), esc_attr( get_the_time() ) ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() )
		);

		// Translators: 1: Date link 2: Author link 3: Categories 4: No. of Comments
		$author = sprintf( '<i class="fa fa-pencil"></i> <address class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></address>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( esc_html__( 'View all posts by %s', 'f4d' ), get_the_author() ) ),
			get_the_author()
		);

		// Return the Categories as a list
		$categories_list = get_the_category_list( esc_html__( ' ', 'f4d' ) );

		// Translators: 1: Permalink 2: Title 3: No. of Comments
		$comments = sprintf( '<span class="comments-link"><i class="fa fa-comment"></i> <a href="%1$s" title="%2$s">%3$s</a></span>',
			esc_url( get_comments_link() ),
			esc_attr( esc_html__( 'Comment on ' . the_title_attribute( 'echo=0' ) ) ),
			( get_comments_number() > 0 ? sprintf( _n( '%1$s Comment', '%1$s Comments', get_comments_number(), 'f4d' ), get_comments_number() ) : esc_html__( 'No Comments', 'f4d' ) )
		);

		// Translators: 1: Date 2: Author 3: Categories 4: Comments
		printf( wp_kses( __( '<div class="header-meta">%1$s%2$s<span class="post-categories">%3$s</span>%4$s</div>', 'f4d' ), array( 
			'div' => array ( 
				'class' => array() ), 
			'span' => array( 
				'class' => array() ) ) ),
			$date,
			$author,
			$categories_list,
			( is_search() ? '' : $comments )
		);
	}
}


/**
 * Prints HTML with meta information for current post: categories, tags, permalink
 *
 * @since F4D 1.0
 *
 * @return void
 */
if ( ! function_exists( 'f4d_entry_meta' ) ) {
	function f4d_entry_meta() {
		// Return the Tags as a list
		$tag_list = "";
		if ( get_the_tag_list() ) {
			$tag_list = get_the_tag_list( '<span class="post-tags">', esc_html__( ' ', 'f4d' ), '</span>' );
		}

		// Translators: 1 is tag
		if ( $tag_list ) {
			printf( wp_kses( __( '<i class="fa fa-tag"></i> %1$s', 'f4d' ), array( 'i' => array( 'class' => array() ) ) ), $tag_list );
		}
	}
}


/**
 * Adjusts content_width value for full-width templates and attachments
 *
 * @since F4D 1.0
 *
 * @return void
 */
function f4d_content_width() {
	if ( is_page_template( 'page-templates/full-width.php' ) || is_attachment() ) {
		global $content_width;
		$content_width = 1200;
	}
}
add_action( 'template_redirect', 'f4d_content_width' );


/**
 * Change the "read more..." link so it links to the top of the page rather than part way down
 *
 * @since F4D 1.0
 *
 * @param string The 'Read more' link
 * @return string The link to the post url without the more tag appended on the end
 */
function f4d_remove_more_jump_link( $link ) {
	$offset = strpos( $link, '#more-' );
	if ( $offset ) {
		$end = strpos( $link, '"', $offset );
	}
	if ( $end ) {
		$link = substr_replace( $link, '', $offset, $end-$offset );
	}
	return $link;
}
add_filter( 'the_content_more_link', 'f4d_remove_more_jump_link' );


/**
 * Returns a "Continue Reading" link for excerpts
 *
 * @since F4D 1.0
 *
 * @return string The 'Continue reading' link
 */
function f4d_continue_reading_link() {
	return '&hellip;<p><a class="more-link" href="'. esc_url( get_permalink() ) . '" title="' . esc_html__( 'Continue reading', 'f4d' ) . ' &lsquo;' . get_the_title() . '&rsquo;">' . wp_kses( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'f4d' ), array( 'span' => array( 
			'class' => array() ) ) ) . '</a></p>';
}


/**
 * Replaces "[...]" (appended to automatically generated excerpts) with the f4d_continue_reading_link().
 *
 * @since F4D 1.0
 *
 * @param string Auto generated excerpt
 * @return string The filtered excerpt
 */
function f4d_auto_excerpt_more( $more ) {
	return f4d_continue_reading_link();
}
add_filter( 'excerpt_more', 'f4d_auto_excerpt_more' );


/**
 * Extend the user contact methods to include Twitter, Facebook and Google+
 *
 * @since F4D 1.0
 *
 * @param array List of user contact methods
 * @return array The filtered list of updated user contact methods
 */
function f4d_new_contactmethods( $contactmethods ) {
	// Add Twitter
	$contactmethods['twitter'] = 'Twitter';

	//add Facebook
	$contactmethods['facebook'] = 'Facebook';

	//add Google Plus
	$contactmethods['googleplus'] = 'Google+';

	return $contactmethods;
}
add_filter( 'user_contactmethods', 'f4d_new_contactmethods', 10, 1 );


/**
 * Add a filter for wp_nav_menu to add an extra class for menu items that have children (ie. sub menus)
 * This allows us to perform some nicer styling on our menu items that have multiple levels (eg. dropdown menu arrows)
 *
 * @since F4D 1.0
 *
 * @param Menu items
 * @return array An extra css class is on menu items with children
 */
function f4d_add_menu_parent_class( $items ) {

	$parents = array();
	foreach ( $items as $item ) {
		if ( $item->menu_item_parent && $item->menu_item_parent > 0 ) {
			$parents[] = $item->menu_item_parent;
		}
	}

	foreach ( $items as $item ) {
		if ( in_array( $item->ID, $parents ) ) {
			$item->classes[] = 'menu-parent-item';
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'f4d_add_menu_parent_class' );


/**
 * Add Filter to allow Shortcodes to work in the Sidebar
 *
 * @since F4D 1.0
 */
add_filter( 'widget_text', 'do_shortcode' );


/**
 * Return an unordered list of linked social media icons, based on the urls provided in the Theme Options
 *
 * @since F4D 1.0
 *
 * @return string Unordered list of linked social media icons
 */
if ( ! function_exists( 'f4d_get_social_media' ) ) {
	function f4d_get_social_media() {
		$output = '';
		$icons = array(
			array( 'url' => of_get_option( 'social_twitter', '' ), 'icon' => 'fa-twitter', 'title' => esc_html__( 'Follow me on Twitter', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_facebook', '' ), 'icon' => 'fa-facebook', 'title' => esc_html__( 'Friend me on Facebook', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_googleplus', '' ), 'icon' => 'fa-google-plus', 'title' => esc_html__( 'Connect with me on Google+', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_linkedin', '' ), 'icon' => 'fa-linkedin', 'title' => esc_html__( 'Connect with me on LinkedIn', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_slideshare', '' ), 'icon' => 'fa-slideshare', 'title' => esc_html__( 'Follow me on SlideShare', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_dribbble', '' ), 'icon' => 'fa-dribbble', 'title' => esc_html__( 'Follow me on Dribbble', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_tumblr', '' ), 'icon' => 'fa-tumblr', 'title' => esc_html__( 'Follow me on Tumblr', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_github', '' ), 'icon' => 'fa-github', 'title' => esc_html__( 'Fork me on GitHub', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_bitbucket', '' ), 'icon' => 'fa-bitbucket', 'title' => esc_html__( 'Fork me on Bitbucket', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_foursquare', '' ), 'icon' => 'fa-foursquare', 'title' => esc_html__( 'Follow me on Foursquare', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_youtube', '' ), 'icon' => 'fa-youtube', 'title' => esc_html__( 'Subscribe to me on YouTube', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_instagram', '' ), 'icon' => 'fa-instagram', 'title' => esc_html__( 'Follow me on Instagram', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_flickr', '' ), 'icon' => 'fa-flickr', 'title' => esc_html__( 'Connect with me on Flickr', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_pinterest', '' ), 'icon' => 'fa-pinterest', 'title' => esc_html__( 'Follow me on Pinterest', 'f4d' ) ),
			array( 'url' => of_get_option( 'social_rss', '' ), 'icon' => 'fa-rss', 'title' => esc_html__( 'Subscribe to my RSS Feed', 'f4d' ) )
		);

		foreach ( $icons as $key ) {
			$value = $key['url'];
			if ( !empty( $value ) ) {
				$output .= sprintf( '<li><a href="%1$s" title="%2$s"%3$s><span class="fa-stack fa-lg"><i class="fa fa-square fa-stack-2x"></i><i class="fa %4$s fa-stack-1x fa-inverse"></i></span></a></li>',
					esc_url( $value ),
					$key['title'],
					( !of_get_option( 'social_newtab', '0' ) ? '' : ' target="_blank"' ),
					$key['icon']
				);
			}
		}

		if ( !empty( $output ) ) {
			$output = '<ul>' . $output . '</ul>';
		}

		return $output;
	}
}


/**
 * Return a string containing the footer credits & link
 *
 * @since F4D 1.0
 *
 * @return string Footer credits & link
 */
if ( ! function_exists( 'f4d_get_credits' ) ) {
	function f4d_get_credits() {
		$output = '';
		$output = sprintf( '%1$s <a href="%2$s" title="%3$s">%4$s</a>',
			esc_html__( 'Designed by', 'f4d' ),
			esc_url( esc_html__( 'http://f4digital.com/', 'f4d' ) ),
			esc_attr( esc_html__( 'Using', 'f4d' ) ),
			esc_html__( 'WordPress', 'f4d' )
		);

		return $output;
	}
}


/**
 * Outputs the selected Theme Options inline into the <head>
 *
 * @since F4D 1.0
 *
 * @return void
 */
function f4d_theme_options_styles() {
	$output = '';
	$imagepath =  trailingslashit( get_template_directory_uri() ) . 'images/';
	$background_defaults = array(
		'color' => '#222222',
		'image' => $imagepath . 'dark-noise.jpg',
		'repeat' => 'repeat',
		'position' => 'top left',
		'attachment'=>'scroll' );

	$background = of_get_option( 'banner_background', $background_defaults );
	if ( $background ) {
		$bkgrnd_color = apply_filters( 'of_sanitize_color', $background['color'] );
		$output .= "#bannercontainer { ";
		$output .= "background: " . $bkgrnd_color . " url('" . esc_url( $background['image'] ) . "') " . $background['repeat'] . " " . $background['attachment'] . " " . $background['position'] . ";";
		$output .= " }";
	}

	$footerColour = apply_filters( 'of_sanitize_color', of_get_option( 'footer_color', '#222222' ) );
	if ( !empty( $footerColour ) ) {
		$output .= "\n#footercontainer { ";
		$output .= "background-color: " . $footerColour . ";";
		$output .= " }";
	}

	if ( of_get_option( 'footer_position', 'center' ) ) {
		$output .= "\n.smallprint { ";
		$output .= "text-align: " . sanitize_text_field( of_get_option( 'footer_position', 'center' ) ) . ";";
		$output .= " }";
	}

	if ( $output != '' ) {
		$output = "\n<style>\n" . $output . "\n</style>\n";
		echo $output;
	}
}
add_action( 'wp_head', 'f4d_theme_options_styles' );


/**
 * Recreate the default filters on the_content
 * This will make it much easier to output the Theme Options Editor content with proper/expected formatting.
 * We don't include an add_filter for 'prepend_attachment' as it causes an image to appear in the content, on attachment pages.
 * Also, since the Theme Options editor doesn't allow you to add images anyway, no big deal.
 *
 * @since F4D 1.0
 */
add_filter( 'meta_content', 'wptexturize' );
add_filter( 'meta_content', 'convert_smilies' );
add_filter( 'meta_content', 'convert_chars'  );
add_filter( 'meta_content', 'wpautop' );
add_filter( 'meta_content', 'shortcode_unautop'  );


/**
 * Unhook the WooCommerce Wrappers
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );


/**
 * Outputs the opening container div for WooCommerce
 *
 * @since F4D 1.3
 *
 * @return void
 */
if ( ! function_exists( 'f4d_before_woocommerce_wrapper' ) ) {
	function f4d_before_woocommerce_wrapper() {
		echo '<div id="primary" class="site-content row" role="main">';
	}
}


/**
 * Outputs the closing container div for WooCommerce
 *
 * @since F4D 1.3
 *
 * @return void
 */
if ( ! function_exists( 'f4d_after_woocommerce_wrapper' ) ) {
	function f4d_after_woocommerce_wrapper() {
		echo '</div> <!-- /#primary.site-content.row -->';
	}
}


/**
 * Check if WooCommerce is active
 *
 * @since F4D 1.3
 *
 * @return void
 */
function f4d_is_woocommerce_active() {
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return true;
	}
	else {
		return false;
	}
}


/**
 * Check if WooCommerce is active and a WooCommerce template is in use and output the containing div
 *
 * @since F4D 1.3
 *
 * @return void
 */
if ( ! function_exists( 'f4d_setup_woocommerce_wrappers' ) ) {
	function f4d_setup_woocommerce_wrappers() {
		if ( f4d_is_woocommerce_active() && is_woocommerce() ) {
				add_action( 'f4d_before_woocommerce', 'f4d_before_woocommerce_wrapper', 10, 0 );
				add_action( 'f4d_after_woocommerce', 'f4d_after_woocommerce_wrapper', 10, 0 );		
		}
	}
	add_action( 'template_redirect', 'f4d_setup_woocommerce_wrappers', 9 );
}


/**
 * Outputs the opening wrapper for the WooCommerce content
 *
 * @since F4D 1.3
 *
 * @return void
 */
if ( ! function_exists( 'f4d_woocommerce_before_main_content' ) ) {
	function f4d_woocommerce_before_main_content() {
		if( ( is_shop() && !of_get_option( 'woocommerce_shopsidebar', '1' ) ) || ( is_product() && !of_get_option( 'woocommerce_productsidebar', '1' ) ) ) {
			echo '<div class="col grid_12_of_12">';
		}
		else {
			echo '<div class="col grid_8_of_12">';
		}
	}
	add_action( 'woocommerce_before_main_content', 'f4d_woocommerce_before_main_content', 10 );
}


/**
 * Outputs the closing wrapper for the WooCommerce content
 *
 * @since F4D 1.3
 *
 * @return void
 */
if ( ! function_exists( 'f4d_woocommerce_after_main_content' ) ) {
	function f4d_woocommerce_after_main_content() {
		echo '</div>';
	}
	add_action( 'woocommerce_after_main_content', 'f4d_woocommerce_after_main_content', 10 );
}


/**
 * Remove the sidebar from the WooCommerce templates
 *
 * @since F4D 1.3
 *
 * @return void
 */
if ( ! function_exists( 'f4d_remove_woocommerce_sidebar' ) ) {
	function f4d_remove_woocommerce_sidebar() {
		if( ( is_shop() && !of_get_option( 'woocommerce_shopsidebar', '1' ) ) || ( is_product() && !of_get_option( 'woocommerce_productsidebar', '1' ) ) ) {
			remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		}
	}
	add_action( 'woocommerce_before_main_content', 'f4d_remove_woocommerce_sidebar' );
}


/**
 * Remove the breadcrumbs from the WooCommerce pages
 *
 * @since F4D 1.3
 *
 * @return void
 */
if ( ! function_exists( 'f4d_remove_woocommerce_breadcrumbs' ) ) {
	function f4d_remove_woocommerce_breadcrumbs() {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
	}
}


/**
 * Set the number of products to display on the WooCommerce shop page
 *
 * @since F4D 1.3.1
 *
 * @return void
 */
if ( ! function_exists( 'f4d_set_number_woocommerce_products' ) ) {
	function f4d_set_number_woocommerce_products() {
		if ( of_get_option( 'shop_products', '12' ) ) {
			$numprods = "return " . sanitize_text_field( of_get_option( 'shop_products', '12' ) ) . ";";
			add_filter( 'loop_shop_per_page', create_function( '$cols', $numprods ), 20 );
		}
	}
	add_action( 'init', 'f4d_set_number_woocommerce_products' );
}


//Trim and Excerpt Customization

remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'replacement_wp_trim_excerpt');

function replacement_wp_trim_excerpt($text) {
	$raw_excerpt = $text;
	if ( '' == $text ) {
		$text = get_the_content('');

		$text = strip_shortcodes( $text );

		$text = apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]>', $text);
		$text = strip_tags($text,'<a>,</a>,<ul>,</ul>,<li>,</li>,<p>,</p>');
		$excerpt_length = apply_filters('excerpt_length', 55);
		$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
		$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
		if ( count($words) > $excerpt_length ) {
			array_pop($words);
			$text = implode(' ', $words);
			$text = $text . $excerpt_more;
		} else {
			$text = implode(' ', $words);
		}
	}
	return $text;
	//return apply_filters('replacement_wp_trim_excerpt', $text, $raw_excerpt);
}



/**  ADD PDF ICON LINK and CHANGE NAMES OF CATEGORIES
 * Add classes to Display Posts Shortcode plugin
 * @author Bill Erickson
 * @link http://wordpress.org/extend/plugins/display-posts-shortcode/
 *
 * @param string $output the original markup for an individual post
 * @param array $atts all the attributes passed to the shortcode
 * @param string $image the image part of the output
 * @param string $title the title part of the output
 * @param string $date the date part of the output
 * @param string $excerpt the excerpt part of the output
 * @param string $inner_wrapper what html element to wrap each post in (default is li)
 * @return string $output the modified markup for an individual post
 */
 
// add_filter( 'display_posts_shortcode_output', 'be_customize_display_posts', 10, 6 );
function be_display_posts_classes( $output, $atts, $image, $title, $date, $excerpt, $inner_wrapper) {
	
	global $post;
	
	$classes = 'listing-item';
	
	// Counter
	global $dps_counter;
	$classes .= ' dps-list-item-' . $dps_counter;
	$dps_counter++;
	
	// Current Page
	global $dps_current_page;
	if( $dps_current_page == get_permalink() )
		$classes .= ' current';
		
		$classes .= $atts['post_type'];
		
		$categories = (array) get_the_category();
    $separator = " ";
    $thelist = '';
    foreach($categories as $category) {    // concate
        $thelist .= $separator . $category->category_nicename;
    }
	
	if ( $atts['include_excerpt'] )
		// Now let's rebuild the excerpt with the read more link at the end
		{
                //Now we can truncate
			$read_more ='<p><a href="'.get_permalink() . '"> Read More...</a></p>';
		$excerpt = '<div class="excerpt"><p>' . substr(get_the_excerpt(), 0,285 ) . '...</p></div><div class="post-footer"><a class="uprepbtn" href="'. get_permalink() .'">Read More</a></div> <hr>';
		}
	else $excerpt = '';
		
		$pdf = esc_attr( get_post_meta( $post->ID, 'pdflink', true ) );
		$sig = esc_attr( get_post_meta( $post->ID, 'author', true ) );
	
	if ( $atts['include_date'] ) $date = ' <div class="header"><h3>'  . $title . '<span class="date">'. get_the_date('M j, Y') .' | <a class="PDF Link Title" href="' . $pdf . '" title="PDF Link" target="_blank"><i class="fa fa-file-pdf-o"></i></a></span></h3><div class="signature">' . $sig . '</div></div>';
	else $date = '';
	
	if ( $atts['include_content'] ) $content = ' <div class="content">' . get_the_content() . '</div>';
	else $content = '';
	
	$thetitle = $title;
	
	$title = $thetitle;

	// Now let's rebuild the output.
	$output = '<' . $inner_wrapper . ' class="' . $classes . ' ' . $thelist . '  ' . get_post_format() . '">' . $date . $title . $excerpt . $content . $image . '</' . $inner_wrapper . '>';

	// Finally we'll return the modified output
	return $output;
	
}
add_filter( 'display_posts_shortcode_output', 'be_display_posts_classes', 10, 7 );


/**
 * Display Posts Shortcode - start counter and save current url
 * @author Bill Erickson
 * @link http://wordpress.org/extend/plugins/display-posts-shortcode/
 *
 * @param array $args
 * @return array $args
 */
function be_display_posts_counter_start( $args ) {
	global $dps_counter, $dps_current_page, $post;
	$dps_counter = 0;
	return $args;
}
add_filter( 'display_posts_shortcode_args', 'be_display_posts_counter_start' );
//     Extending the Display-posts-shortcode plugin  //

/**
 * Display Posts Shortcode - Display message if no results
 *
 * @author Bill Erickson
 * @link http://www.billerickson.net/code/display-posts-shortcode-no-results
 *
 * @param string $output, default is empty
 * @return string $output

function be_no_results( $output ) {
	$output = '<p>There is no content to display.</p>';
	return $output;
}
add_filter( 'display_posts_shortcode_no_results', 'be_no_results' );
 */

/**
 * Display Posts - Exclude Current Post
 *
 * @author Bill Erickson
 * @link http://wordpress.org/extend/plugins/display-posts-shortcode/
 *
 * @param array $args
 * @return array
 */
function be_exclude_current_post( $args ) {
	if( is_singular() && !isset( $args['post__in'] ) )
		$args['post__not_in'] = array( get_the_ID() );

	return $args;
}
add_filter( 'display_posts_shortcode_args', 'be_exclude_current_post' );

//Replace Howdy in the admin top bar //

function howdy_message($translated_text, $text, $domain) {

	$new_message = str_replace('Howdy', 'Hello', $text);
	return $new_message;
}
add_filter('gettext', 'howdy_message', 10, 3);

// REMOVE UNNECESSARY META TAGS FROM HTML MARKUP //
remove_action( 'wp_head', 'wp_generator' ) ; 
remove_action( 'wp_head', 'wlwmanifest_link' ) ; 
remove_action( 'wp_head', 'rsd_link' ) ;


//  Gravity Forms Customizations //

        //Add DropDown to List field - Pet Type//
add_filter( 'gform_column_input_8_59_2', 'set_column_59_2', 10, 5 );
function set_column_59_2( $input_info, $field, $column, $value, $form_id ) {
    return array(
    'type' => 'select', 
    'choices' => array(
    	array( 'text' => 'Select', 'value' => '' ),
        array( 'text' => 'Bird', 'value' => 'Bird' ),
        array( 'text' => 'Cat', 'value' => 'Cat' ),
        array( 'text' => 'Dog', 'value' => 'Dog' ),
        array( 'text' => 'Farm Animal', 'value' => 'Farm Animal' ),
        array( 'text' => 'Horse', 'value' => 'Horse' ),
        array( 'text' => 'Rabbit', 'value' => 'Rabbit' ),
        array( 'text' => 'Reptile', 'value' => 'Reptile' ),
        array( 'text' => 'Rodent', 'value' => 'Rodent' )
    )

);
}

               //Add DropDown to List field - Pet Gender//
add_filter( 'gform_column_input_8_59_3', 'set_column_59_3', 10, 5 );
function set_column_59_3( $input_info, $field, $column, $value, $form_id ) {
    return array(
    'type' => 'select', 
    'choices' => array(
    	array( 'text' => 'Select', 'value' => '' ),
        array( 'text' => 'Female', 'value' => 'Female' ),
        array( 'text' => 'Male', 'value' => 'Male' )
    )

);    
}

// Populating List Fields with multiple columns, taken from: https://www.gravityhelp.com/documentation/article/gform_field_value_parameter_name/#2-populate-from-a-cookie  "Allow field to be populated dynamically" and that the parameter name is set to "list".
/*

add_filter( 'gform_field_value_list', 'populate_list' );
function populate_list( $value ) {
  $list_array = array(
            array(
                'Column 1' => 'row1col1',
                'Column 2' => 'row1col2',
                'Column 3' => 'row1col3',
            ),
            array(
                'Column 1' => 'row2col1',
                'Column 2' => 'row2col2',
                'Column 3' => 'row2col3'
            ),
  );
    return $list_array;
}


// Populate multiple fields using one function

add_filter( 'gform_field_value', 'populate_fields', 10, 3 );
function populate_fields( $value, $field, $name ) {

    $values = array(
        'field_one'   => 'value one',
        'field_two'   => 'value two',
        'field_three' => 'value three',
    );

    return isset( $values[ $name ] ) ? $values[ $name ] : $value;
}


*/

//Add Unique Pet Id to Each Pet Added
/*
add_filter('gform_field_value_uuid', 'customer_reference');
function customer_reference(){
	$number = mt_rand(10000000000, 99999999999);
	return 'PLT' . substr($number,0,11);
}
*/

