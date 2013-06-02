<?php
/**
 * Index Template
 *
 * Here we setup all logic and XHTML that is required for the index template, used as both the homepage
 * and as a fallback template, if a more appropriate template file doesn't exist for a specific context.
 *
 * @package WooFramework
 * @subpackage Template
 */
	get_header();
	global $woo_options;

?>

    <div id="content" class="col-full">

    	<?php woo_main_before(); ?>

    	<div class="home-intro">

    	<h1 class="stand-first"><?php bloginfo('name'); ?></h1>

    	<?php if( isset( $woo_options['woo_stand_first'] ) ) {
			echo '<div class="stand-first">';
	        echo stripslashes( $woo_options['woo_stand_first'] );
	        echo '</div>';
		} ?>

		<?php if ( is_woocommerce_activated() ) { ?>

    	<ul class="featured-products">
    	<!-- The first 3 -->
    	<?php
		$args = array( 'post_type' => 'product', 'posts_per_page' => 3, 'meta_query' => array( array('key' => '_visibility','value' => array('catalog', 'visible'),'compare' => 'IN'),array('key' => '_featured','value' => 'yes')) );
		$loop = new WP_Query( $args );

		while ( $loop->have_posts() ) : $loop->the_post(); $_product;

		if ( function_exists( 'get_product' ) ) {
			$_product = get_product( $loop->post->ID );
		} else {
			$_product = new WC_Product( $loop->post->ID );
		}

		?><li class="featured">

					<?php //woocommerce_show_product_sale_flash( $post, $_product ); ?>
					<a href="<?php echo get_permalink( $loop->post->ID ) ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>">
						<?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" />'; ?>


					<h3><?php the_title(); ?> <span class="price"><?php echo $_product->get_price_html(); ?></span></h3>

					</a>

			</li><?php endwhile; ?><!-- the large 1 --><?php
		$args = array( 'post_type' => 'product', 'posts_per_page' => 1, 'offset' => 3, 'meta_query' => array( array('key' => '_visibility','value' => array('catalog', 'visible'),'compare' => 'IN'),array('key' => '_featured','value' => 'yes')) );
		$loop = new WP_Query( $args );
		while ( $loop->have_posts() ) : $loop->the_post();
		if ( function_exists( 'get_product' ) ) {
			$_product = get_product( $loop->post->ID );
		} else {
			$_product = new WC_Product( $loop->post->ID );
		}
		?><li class="featured">

					<?php //woocommerce_show_product_sale_flash( $post, $_product ); ?>
					<a href="<?php echo get_permalink( $loop->post->ID ) ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>">
						<?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_single'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" />'; ?>


					<h3><?php the_title(); ?> <span class="price"><?php echo $_product->get_price_html(); ?></span></h3>

					</a>

				</li><?php endwhile; ?><!-- the last 3 --><?php
		$args = array( 'post_type' => 'product', 'posts_per_page' => 3, 'offset' => 4, 'meta_query' => array( array('key' => '_visibility','value' => array('catalog', 'visible'),'compare' => 'IN'),array('key' => '_featured','value' => 'yes')) );
		$loop = new WP_Query( $args );
		while ( $loop->have_posts() ) : $loop->the_post(); $_product;
		if ( function_exists( 'get_product' ) ) {
			$_product = get_product( $loop->post->ID );
		} else {
			$_product = new WC_Product( $loop->post->ID );
		}
		?><li class="featured">

					<?php //woocommerce_show_product_sale_flash( $post, $_product ); ?>
					<a href="<?php echo get_permalink( $loop->post->ID ) ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>">
						<?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" />'; ?>


					<h3><?php the_title(); ?> <span class="price"><?php echo $_product->get_price_html(); ?></span></h3>

					</a>

				</li><?php endwhile; ?>
		</ul>

		<?php } ?>

		</div><!--/.home-intro-->

		<section id="main" class="<?php if ( $woo_options[ 'woo_homepage_tweet' ] == "false" && $woo_options[ 'woo_display_store_info' ] == "false" ) echo 'fullwidth'; else echo 'col-left'; ?>">

		<?php woo_loop_before(); ?>

		<?php

			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; query_posts( array( 'post_type' => 'post', 'paged' => $paged, 'posts_per_page' => 1 ) );
        	if ( have_posts() ) : $count = 0;
        ?>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); $count++; ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to overload this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', get_post_format() );
				?>

			<?php endwhile; ?>

		<?php else : ?>

            <article <?php post_class(); ?>>
                <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
            </article><!-- /.post -->

        <?php endif; ?>

        <?php woo_loop_after(); ?>

		</section><!-- /#main -->

		<?php woo_main_after(); ?>

		<?php if ( $woo_options[ 'woo_homepage_tweet' ] == "true" || $woo_options[ 'woo_display_store_info' ] == "true" ) { ?>
		<aside id="sidebar" class="col-right">
		<?php } ?>
			<!-- The latest tweet -->
			<?php if ( $woo_options[ 'woo_homepage_tweet' ] == "true" ) { ?>
				<?php artificer_tweet(); ?>
			<?php } ?>
			<!-- The store info -->
			<?php if ( $woo_options[ 'woo_display_store_info' ] == "true" ) {
			$email = get_option('woo_store_email_address');
			$phone = get_option('woo_store_phone_number');
			$twitterID = get_option('woo_contact_twitter');
			?>
				<ul class="store-info">

					<li class="phone">
						<div class="inner">
							<span><?php _e('Call us:','woothemes'); ?></span>
							<a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a>
						</div>
					</li>

					<li class="email">
						<div class="inner">
							<span><?php _e('Send us an email:','woothemes'); ?></span>
							<a href="mailto:<?php echo $email; ?>" title="<?php _e('Send us an email', 'woothemes')?>"><?php echo $email; ?></a>
						</div>
					</li>

				</ul><!--/.store-info-->
			<?php } ?>
		<?php if ( $woo_options[ 'woo_homepage_tweet' ] == "true" || $woo_options[ 'woo_display_store_info' ] == "true" ) { ?>
		</aside>
		<?php } ?>

        <?php //get_sidebar(); ?>

    </div><!-- /#content -->

<?php get_footer(); ?>