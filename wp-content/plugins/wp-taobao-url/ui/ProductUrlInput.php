<?php
namespace daigou;

require_once(__DIR__ . '/../lib/Configuration.php');

class ProductUrlInput {
	public static function onEnqueueScripts() {
		$js = Configuration::getJavaScriptDirectory();

		wp_register_script('daigou.ProductDetailBox', $js . '/ProductDetailBox.js', array('jquery', 'daigou.Dom'));
		wp_register_script('daigou.ProductUrlInput', $js . '/ProductUrlInput.js', array('jquery', 'daigou.Dom', 'daigou.Configuration', 'URI', 'daigou.ProductDetailBox'));
	}
}

add_action('wp_enqueue_scripts', array('\daigou\ProductUrlInput', 'onEnqueueScripts'));
