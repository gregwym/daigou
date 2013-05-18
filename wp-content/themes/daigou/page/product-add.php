<?php
/**
 * Template Name: Add Product
 *
 * This page allows users to paste in a Tao Bao product URL, and add that product to shopping cart.
 */
 get_header();
?>
       
  <div id="content" class="page col-full">
    <?php 
    	require_once(__DIR__ . '/../ui/productUrlInput.php');
    	daigou\productUrlInput(); 
   	?>
  </div><!-- /#content -->
		
<?php get_footer(); ?>