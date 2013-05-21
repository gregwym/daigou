<?php
namespace daigou;
// File Security Check
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'You do not have sufficient permissions to access this page!' );
}

require_once(__DIR__ . '/lib/TaoBaoClient.php');
require_once(__DIR__ . '/lib/Configuration.php');

class Functions {
	public static function onEnqueueScripts() {
		$jsDir = Configuration::getJavaScriptDirectory();
		$cssDir = Configuration::getCssDirectory();

		wp_enqueue_style('daigou', $cssDir . '/daigou.css');

		wp_register_script('daigou.Dom', $jsDir . '/Dom.js');
		wp_register_script('daigou.Configuration', $jsDir . '/Configuration.js');
		wp_register_script('URI', $jsDir . '/URI.js');

		wp_localize_script('daigou.Configuration', 'DaigouConfiguration', array(
			'ajaxUrl' => admin_url('admin-ajax.php')
		));
	}

	public static function onAjaxGetProductById() {
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
}

add_action('wp_ajax_GetProductById', array('\daigou\Functions', 'onAjaxGetProductById'));
add_action('wp_enqueue_scripts', array('\daigou\Functions', 'onEnqueueScripts'));