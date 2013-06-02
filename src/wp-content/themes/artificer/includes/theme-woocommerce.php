<?php
/*-----------------------------------------------------------------------------------*/
/* This theme supports WooCommerce, woo! */
/*-----------------------------------------------------------------------------------*/

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
	add_theme_support( 'woocommerce' );
}

/*-------------------------------------------------------------------------------------------*/
/* WOOCOMMERCE OVERRIDES */
/*-------------------------------------------------------------------------------------------*/

// Disable WooCommerce styles
define('WOOCOMMERCE_USE_CSS', false);

/*-------------------------------------------------------------------------------------------*/
/* GENERAL LAYOUT */
/*-------------------------------------------------------------------------------------------*/

// Adjust markup on all WooCommerce pages
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', 'artificer_before_content', 10 );
add_action( 'woocommerce_after_main_content', 'artificer_after_content', 20 );

// Fix the layout etc
if (!function_exists('artificer_before_content')) {
	function artificer_before_content() {
	?>
		<!-- #content Starts -->
		<?php woo_content_before(); ?>
	    <div id="content" class="col-full">

	        <!-- #main Starts -->
	        <?php woo_main_before(); ?>
	        <div id="main" class="col-left">
	    <?php
	}
}

if (!function_exists('artificer_after_content')) {
	function artificer_after_content() {
	?>
			</div><!-- /#main -->
	        <?php woo_main_after(); ?>
			<?php woocommerce_get_sidebar(); ?>
	    </div><!-- /#content -->
		<?php woo_content_after(); ?>
	    <?php
	}
}

// Only display sidebar on product archives if instructed to do so via woo_shop_archives_fullwidth
if (!function_exists('woocommerce_get_sidebar')) {
	function woocommerce_get_sidebar() {
		global $woo_options;

		if (!is_woocommerce()) {
			get_sidebar();
		} elseif ( $woo_options[ 'woo_shop_archives_fullwidth' ] == "false" && (is_woocommerce()) || (is_product()) ) {
			get_sidebar();
		} elseif ( $woo_options[ 'woo_shop_archives_fullwidth' ] == "true" && (is_archive(array('product'))) ) {
			// no sidebar
		}
	}
}

// Add a class to the body if full width shop archives are specified
add_filter( 'body_class','artificer_woocommerce_layout_body_class', 10 );		// Add layout to body_class output

if ( ! function_exists( 'artificer_woocommerce_layout_body_class' ) ) {
	function artificer_woocommerce_layout_body_class( $wc_classes ) {

		global $woo_options;

		$layout = '';

		// Add woocommerce-fullwidth class if full width option is enabled
		if ( $woo_options[ 'woo_shop_archives_fullwidth' ] == "true" && (is_shop() || is_product_category())) {
			$layout = 'woocommerce-fullwidth';
		}

		// Add classes to body_class() output
		$wc_classes[] = $layout;
		return $wc_classes;

	} // End woocommerce_layout_body_class()
}

// Add the cart link to the header
add_action('woo_nav_before', 'artificer_header_cart_link', 20);
if ( ! function_exists( 'artificer_header_cart_link' ) ) {
	function artificer_header_cart_link() {
		if ( class_exists( 'woocommerce' ) ) { echo woocommerce_cart_link(); }
	}
}

// Add the checkout link to the header
add_action('woo_nav_before', 'artificer_header_checkout_link',10);
if ( ! function_exists( 'artificer_header_checkout_link' ) ) {
	function artificer_header_checkout_link() {
	global $woocommerce;
	?>
	<a href="<?php echo $woocommerce->cart->get_checkout_url()?>" class="checkout"><span class="lozenge"><?php _e('Checkout','woothemes') ?></span></a>
	<?php }
}

/*-------------------------------------------------------------------------------------------*/
/* PRODUCTS LOOP */
/*-------------------------------------------------------------------------------------------*/

// Add the inner div in product loop
add_action( 'woocommerce_before_shop_loop_item', 'artificer_product_inner_open', 5, 2);
add_action( 'woocommerce_after_shop_loop_item', 'artificer_product_inner_close', 12, 2);
add_action( 'woocommerce_before_subcategory', 'artificer_product_inner_open', 5, 2);
add_action( 'woocommerce_after_subcategory', 'artificer_product_inner_close', 12, 2);

function artificer_product_inner_open() {
	echo '<div class="inner">';
}
function artificer_product_inner_close() {
	echo '</div> <!--/.wrap-->';
}

// Change columns in product loop to 3
add_filter('loop_shop_columns', 'loop_columns');

if (!function_exists('loop_columns')) {
	function loop_columns() {
		return 3;
	}
}

// Display x products per page based on user input
add_filter('loop_shop_per_page', 'products_per_page');
if (!function_exists('products_per_page')) {
	function products_per_page() {
		global $woo_options;
		if ( isset( $woo_options['woo_products_per_page'] ) ) {
			return $woo_options['woo_products_per_page'];
		}
	}
}

// Remove pagination (we're using the WooFramework default pagination)
// < 2.0
remove_action( 'woocommerce_pagination', 'woocommerce_pagination', 10 );
add_action( 'woocommerce_pagination', 'woocommerceframework_pagination', 10 );
//   2.0 +
if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '>=' ) ) {
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
	add_action( 'woocommerce_after_shop_loop', 'woocommerceframework_pagination', 10 );
}

function woocommerceframework_pagination() {
	if ( is_search() && is_post_type_archive() ) {
		add_filter( 'woo_pagination_args', 'woocommerceframework_add_search_fragment', 10 );
		add_filter( 'woo_pagination_args_defaults', 'woocommerceframework_woo_pagination_defaults', 10 );
	}
	woo_pagination();
}

function woocommerceframework_add_search_fragment ( $settings ) {
	$settings['add_fragment'] = '&post_type=product';

	return $settings;
} // End woocommerceframework_add_search_fragment()

function woocommerceframework_woo_pagination_defaults ( $settings ) {
	$settings['use_search_permastruct'] = false;

	return $settings;
} // End woocommerceframework_woo_pagination_defaults()

// Add wrapping div around pagination
add_action( 'woocommerce_pagination', 'woocommerce_pagination_wrap_open', 5 );
add_action( 'woocommerce_pagination', 'woocommerce_pagination_wrap_close', 25 );

if (!function_exists('woocommerce_pagination_wrap_open')) {
	function woocommerce_pagination_wrap_open() {
		echo '<section class="pagination-wrap">';
	}
}

if (!function_exists('woocommerce_pagination_wrap_close')) {
	function woocommerce_pagination_wrap_close() {
		echo '</section>';
	}
}

// Add image wrap
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_product_thumbnail_wrap_open', 9, 2);

if (!function_exists('woocommerce_product_thumbnail_wrap_open')) {
	function woocommerce_product_thumbnail_wrap_open() {
		echo '<div class="img-wrap">';
	}
}

// Close image wrap
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_product_thumbnail_wrap_close', 15, 2);
if (!function_exists('woocommerce_product_thumbnail_wrap_close')) {
	function woocommerce_product_thumbnail_wrap_close() {
		echo '</div> <!--/.wrap-->';
	}
}

// Move sale flash
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 25);

/*-------------------------------------------------------------------------------------------*/
/* BREADCRUMB */
/*-------------------------------------------------------------------------------------------*/

// Remove WC breadcrumb (we're using the WooFramework breadcrumb)
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);

// Customise the breadcrumb
add_filter( 'woo_breadcrumbs_args', 'woo_custom_breadcrumbs_args', 10 );

if (!function_exists('woo_custom_breadcrumbs_args')) {
	function woo_custom_breadcrumbs_args ( $args ) {
		$textdomain = 'woothemes';
		$args = array('separator' => '>', 'before' => '', 'show_home' => __( 'Home', $textdomain ),);
		return $args;
	} // End woo_custom_breadcrumbs_args()
}

// Adjust the star rating in the sidebar
add_filter('woocommerce_star_rating_size_sidebar', 'woostore_star_sidebar');

if (!function_exists('woostore_star_sidebar')) {
	function woostore_star_sidebar() {
		return 12;
	}
}

/*-------------------------------------------------------------------------------------------*/
/* SINGLE PRODUCT */
/*-------------------------------------------------------------------------------------------*/

// Redefine woocommerce_output_related_products()
function woocommerce_output_related_products() {
	woocommerce_related_products(3,3); // Display 3 products in rows of 3
}

// If theme lightbox is enabled, disable the WooCommerce lightbox and make product images prettyPhoto galleries
add_action( 'wp', 'woocommerce_prettyphoto' );
function woocommerce_prettyphoto() {
	global $woo_options;
	if ( $woo_options[ 'woo_enable_lightbox' ] == "true" ) {
		update_option( 'woocommerce_enable_lightbox', false );
	}
}

// Upsells
if (!function_exists('woocommerceframework_upsell_display')) {
	function woocommerceframework_upsell_display() {
	    // Display 3 up sells if full width layout in use.
	    woocommerce_upsell_display( 3, 3 );
	}
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'woocommerceframework_upsell_display', 15 );

/*-------------------------------------------------------------------------------------------*/
/* WIDGETS */
/*-------------------------------------------------------------------------------------------*/

// Adjust the star rating in the recent reviews widget
add_filter('woocommerce_star_rating_size_recent_reviews', 'woostore_star_reviews');

if (!function_exists('woostore_star_reviews')) {
	function woostore_star_reviews() {
		return 12;
	}
}

/*-------------------------------------------------------------------------------------------*/
/* AJAX FRAGMENTS */
/*-------------------------------------------------------------------------------------------*/

// Handle cart in header fragment for ajax add to cart
add_filter('add_to_cart_fragments', 'header_add_to_cart_fragment');
function header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	ob_start();

	woocommerce_cart_link();

	$fragments['a.cart-button'] = ob_get_clean();

	return $fragments;

}

function woocommerce_cart_link() {
	global $woocommerce;
	?>
	<a href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="<?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count);?> <?php _e('in your shopping cart', 'woothemes'); ?>" class="cart-button ">
	<span class="label"><?php _e('My Basket:', 'woothemes'); ?></span>
	<?php echo $woocommerce->cart->get_cart_total();  ?>
	<span class="items"><?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count); ?></span>
	</a>
	<?php
}

?>