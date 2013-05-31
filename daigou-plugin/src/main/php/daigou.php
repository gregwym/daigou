<?php
/**
 * @package Taobao_URL
 * @version 0.1
 */
/*
Plugin Name: Daigou
Description: WordPress plugin for Daigou
Author: Greg Wang & ShengMin Zhang
Version: 0.1
Author URI: http://sample.com/
*/
namespace daigou;

// File Security Check
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	die ( 'You do not have sufficient permissions to access this page!' );
}

// Enable display_errors for debug
if (!ini_get('display_errors')) {
	ini_set('display_errors', '1');
}

class Taobao_URL {
	public function __construct() {
		add_action('wp_ajax_GetProductById', array($this, 'ajax_get_product_by_id'));
		add_action('wp_enqueue_scripts', array($this, 'register_script'));

		add_shortcode('taobao_url', array($this, 'add_taobao_url_textfield'));
		add_filter('woocommerce_single_product_image_html', array($this, 'display_external_product_image'), 10, 2);
	}

	public function register_script() {
		$jsDir = plugins_url( 'js', __FILE__ );
		$cssDir = plugins_url( 'css', __FILE__ );

		wp_enqueue_style('daigou', $cssDir . '/daigou.css');

		wp_register_script('daigou.Dom', $jsDir . '/Dom.js');
		wp_register_script('daigou.Configuration', $jsDir . '/Configuration.js');
		wp_register_script('URI', $jsDir . '/URI.js');

		wp_localize_script('daigou.Configuration', 'DaigouConfiguration', array(
			'ajaxUrl' => admin_url('admin-ajax.php')
		));

		wp_register_script('daigou.ProductDetailBox', $jsDir . '/ProductDetailBox.js', array('jquery', 'daigou.Dom'));
		wp_register_script('daigou.ProductUrlInput', $jsDir . '/ProductUrlInput.js', array('jquery', 'daigou.Dom', 'daigou.Configuration', 'URI', 'daigou.ProductDetailBox'));
		wp_enqueue_script('daigou.add-product-page', $jsDir . '/add-product-page.js', array('jquery', 'daigou.ProductUrlInput'));
	}

	public function ajax_get_product_by_id() {
		require_once(__DIR__ . '/lib/TaoBaoClient.php');

		$id = intval($_POST['id']);
		// TODO: fetch the exchange rate
		$result = TaoBaoClient::getProductById($id);

		// TODO: Define return value from TaoBaoClient, and handle errors.
		/*
		if (gettype($result) !== 'array') {
			echo json_encode(array(
				'error' => 'Fail to fetch product information.',
			));
			die();
		}
		*/

		/* Default post config for reference
		$defaults = array(
			'post_status'           => 'draft',
			'post_type'             => 'post',
			'post_author'           => $user_ID,
			'ping_status'           => get_option('default_ping_status'),
			'post_parent'           => 0,
			'menu_order'            => 0,
			'to_ping'               => '',
			'pinged'                => '',
			'post_password'         => '',
			'guid'                  => '',
			'post_content_filtered' => '',
			'post_excerpt'          => '',
			'import_id'             => 0
		);
		*/

		// For WooTheme compatibility.
		// Define variables that are used w/o pre-checking.
		$_POST['post_type'] = 'product';

		// Add new product
		$product = array(
			'post_type'    => 'product',
			'post_title'   => $result->{'item'}->{'title'},
			// TODO: add product description
			'post_content' => $result->{'item'}->{'detail_url'},
			'post_status'  => 'publish',
			// 'post_author'  => $user_ID,
		);
		$product_id = \wp_insert_post($product);

		if (gettype($product_id) !== 'integer' || $product_id === 0) {
			echo json_encode(array(
				'error' => 'Fail to create new product.',
			));
			die();
		}

		// Update product slug as product_id => cleaner URL
		$product['ID'] = $product_id;
		$product['post_name'] = $product_id;
		\wp_update_post($product);

		// Update product meta
		// TODO: Calculate price with exchange rate.
		\update_post_meta( $product_id, '_regular_price', $result->{'item'}->{'price'} );
		\update_post_meta( $product_id, '_price', $result->{'item'}->{'price'} );
		\update_post_meta( $product_id, '_visibility', 'visible' );

		// Add product picture as attachment, and assign as product thumbnail.
		$prod_pic = array(
			'post_type'      => 'attachment',
			'post_title'     => 'Picture for Product #' . $product_id,
			'post_mime_type' => 'image/jpeg',
			'post_status'    => 'inherit',
			// 'post_author'    => $user_ID,
			'post_parent'    => $product_id,
			'guid'           => $result->{'item'}->{'pic_url'},
		);
		$prod_pic_id = \wp_insert_post($prod_pic);

		// Continue upon error for product picture insertion.
		if (gettype($product_id) === 'integer' && $product_id > 0) {
			\update_post_meta( $product_id, '_thumbnail_id', $prod_pic_id );
		}

		echo json_encode(array(
			'taobao'               => $result,
			'exchangeRate'         => 6.0,
			'domesticShippingCost' => 22,
			'product_id'           => $product_id,
			'product_url'          => \get_permalink( $product_id ),
		));

		die();
	}

	public function add_taobao_url_textfield($attributes) {
		require(__DIR__ . '/page/add-product-page.php');
	} // End add_taobao_url_textfield

	public function display_external_product_image($result, $post_id) {
		$post_image_id = \get_post_meta($post_id, '_thumbnail_id', true);
		if (strlen($post_image_id) > 0) {
			$post_image = \get_post($post_image_id);
			$post_image_url = $post_image->guid;
			$post_image_title = $post_image->post_title;
			if (strlen($post_image_url) > 0) {
				$image = sprintf('<img width="300" src="%s" class="attachment-shop_single wp-post-image" alt="%s">', $post_image_url, $post_image_title);
				$result = sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s"  rel="prettyPhoto">%s</a>', $post_image_url, $post_image_title, $image );
			}
		}

		return $result;
	}
}

$wpTaobaURL = new Taobao_URL();
