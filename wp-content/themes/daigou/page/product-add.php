<?php
/**
 * Template Name: Add Product
 *
 * This page allows users to paste in a Tao Bao product URL, and add that product to shopping cart.
 */

 global $woo_options; 
 get_header();
?>
       
    <div id="content" class="page col-full">
    	hello
    	<?php woo_main_before(); ?>
        
      <?php woo_main_after(); ?>

      <?php get_sidebar(); ?>

    </div><!-- /#content -->
		
<?php get_footer(); ?>