<?php
namespace daigou;

require_once(__DIR__ . '/../lib/Dom.php');

class ProductUrlInput {
	public static function loadResources() {
		
	}
}

/**
 * A input box that allows users to paste in a Tao Bao product URL
 */
function productUrlInput() {
	$id = Dom::getId();
	wp_enqueue_script( 'ajax-script', get_stylesheet_directory_uri() . '/js/URI.js', array('jquery'));
	wp_localize_script( 'ajax-script', 'ajax_object', array(1, 2, 3));
	wp_localize_script( 'ajax-script', 'ajax_object', array(5, 6, 7));
            //array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 'ddd') );
	echo "<div id=\"$id\"></div>";
}
