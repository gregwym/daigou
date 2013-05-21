<?php
namespace daigou;

class Configuration {
	const TAOBAO_API_KEY = '21387032';
	const TAOBAO_API_SECRET = 'e7aa253ff09a8b81e812945a5329e960';

	public static function getJavaScriptDirectory() {
		$dir = get_stylesheet_directory_uri();
		return (WP_DEBUG) ? $dir . '/js' : $dir . '/js-min';
	}

	public static function getCssDirectory() {
		$dir = get_stylesheet_directory_uri();
		return $dir . '/css';
	}
}
