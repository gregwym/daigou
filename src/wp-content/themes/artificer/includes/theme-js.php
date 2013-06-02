<?php
if ( ! is_admin() ) { add_action( 'wp_enqueue_scripts', 'woothemes_add_javascript' ); }

if ( ! function_exists( 'woothemes_add_javascript' ) ) {
	function woothemes_add_javascript() {
		global $woo_options;

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'third party', get_template_directory_uri() . '/includes/js/third-party.js', array( 'jquery' ) );
		wp_enqueue_script( 'general', get_template_directory_uri() . '/includes/js/general.js', array( 'jquery' ) );

		if ( isset( $woo_options['woo_enable_lightbox'] ) && $woo_options['woo_enable_lightbox'] == 'true' ) {
			wp_enqueue_script( 'prettyPhoto', get_template_directory_uri() . '/includes/js/jquery.prettyPhoto.js', array( 'jquery' ) );
			wp_enqueue_script( 'enable-lightbox', get_template_directory_uri() . '/includes/js/enable-lightbox.js', array( 'jquery', 'prettyPhoto' ) );
			wp_enqueue_style( 'prettyPhoto', get_template_directory_uri().'/includes/css/prettyPhoto.css' );
		}
	}
}

//Add an HTML5 Shim
add_action( 'wp_head', 'html5_shim');

if (!function_exists('html5_shim')) {
	function html5_shim() {
		?>
		<!--[if lt IE 9]>
			<script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<?php
	}
}

?>