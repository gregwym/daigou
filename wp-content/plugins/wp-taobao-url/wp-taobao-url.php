<?php
/**
 * @package Taobao_URL
 * @version 0.1
 */
/*
Plugin Name: Taobao URL
Plugin URI: https://github.com/gregwym/wp-taobao-url
Description: WordPress plugin for Taobao URL parsing and Product information fetching.
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

		add_shortcode( 'taobao_url', array($this, 'add_taobao_url_textfield'));
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
		echo json_encode(array(
			'taobao' => $result,
			'exchangeRate' => 6.0,
			'domesticShippingCost' => 22
		));
		die();
	}

	public function add_taobao_url_textfield($attributes) {
		require(__DIR__ . '/page/add-product-page.php');
	} // End add_taobao_url_textfield
}

$wpTaobaURL = new Taobao_URL();
