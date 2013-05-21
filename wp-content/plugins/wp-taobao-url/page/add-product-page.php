<?php
/**
 * Template Name: add product page
 *
 * This page allows users to paste in a Tao Bao product URL, and add that product to shopping cart.
 */

if (!is_user_logged_in()) {
  auth_redirect(); 
} 

require_once(__DIR__ . '/../ui/ProductUrlInput.php');
require_once(__DIR__ . '/../lib/Configuration.php');

class AddProductPage {
  public static function onEnqueueScripts() {
    $js = \daigou\Configuration::getJavaScriptDirectory();

    wp_enqueue_script('daigou.add-product-page', $js . '/add-product-page.js', array('jquery', 'daigou.ProductUrlInput'));
  }
}

add_action('wp_enqueue_scripts', array('AddProductPage', 'onEnqueueScripts'));
?>
<?php get_header(); ?>       
<div id="content" class="page col-full" style="height: 600px;">
  
</div><!-- /#content -->
<?php get_footer(); ?>
