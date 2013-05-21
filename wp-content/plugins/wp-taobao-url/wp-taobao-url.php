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

// Add the taobao URL textfield before main
function add_taobao_url_textfield() {
	if ( is_home() ) {
		echo '<form action="#"><input type="text" name="taobao-url" placeholder="Please paste the Taobao URL here..."></form>';
	}
} // End add_taobao_url_textfield

add_action( 'woo_main_before', 'add_taobao_url_textfield' );

?>
