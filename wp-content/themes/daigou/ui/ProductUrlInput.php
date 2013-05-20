<?php
namespace daigou;

class ProductUrlInput {
	public static function onEnqueueScripts() {
		$dir = get_stylesheet_directory_uri();

		wp_register_script('daigou.ProductDetailBox', $dir . '/js/ProductDetailBox.js', array('jquery', 'daigou.Dom'));
		wp_register_script('daigou.ProductUrlInput', $dir . '/js/ProductUrlInput.js', array('jquery', 'daigou.Dom', 'daigou.Configuration', 'URI', 'daigou.ProductDetailBox'));
	}
}

add_action('wp_enqueue_scripts', array('\daigou\ProductUrlInput', 'onEnqueueScripts'));
