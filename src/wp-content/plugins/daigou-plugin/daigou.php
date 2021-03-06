<?php
/**
 * @package Daigou
 * @version 0.1
 */
/*
Plugin Name: Daigou
Description: WordPress plugin for Daigou
Author: Greg Wang & ShengMin Zhang
Version: 0.1
Author URI: http://sample.com/
*/
namespace daigou;

// File Security Check
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	die ( 'You do not have sufficient permissions to access this page!' );
}

class Daigou {
	private $new_customer_data;

	public function __construct() {
		add_action('wp_ajax_GetProductById', array($this, 'ajax_get_product_by_id'));
		add_action('wp_ajax_nopriv_GetProductById', array($this, 'ajax_get_product_by_id'));
		add_action('wp_enqueue_scripts', array($this, 'register_script'));

		add_shortcode('taobao_url', array($this, 'add_taobao_url_textfield'));
		add_shortcode('guide', array($this, 'add_guide'));
		add_filter('woocommerce_single_product_image_html', array($this, 'display_external_product_image'), 10, 2);

		// Store customer notes when adding a product to cart
		add_action('woocommerce_after_main_content', array($this, 'move_woocommerce_tabs'));
		add_action('woocommerce_before_add_to_cart_button', array($this, 'add_customer_notes_textfield'));
		add_filter('add_to_cart_redirect', array($this, 'add_customer_notes'));

		// Save customer password
		add_filter('woocommerce_new_customer_data', array($this, 'cache_new_customer_data'));
		add_action('woocommerce_created_customer', array($this, 'save_new_customer_data'), 10, 1);
		add_action('woocommerce_customer_change_password', array($this, 'save_customer_new_pass'), 10, 1);
		add_action('woocommerce_customer_reset_password', array($this, 'save_customer_new_pass'), 10, 1);

		// Add Continue shopping
		add_action('woo_foot', array($this, 'add_continue_shopping'));
	}

	public function register_script() {
		$jsDir = plugins_url( 'js', __FILE__ );
		$cssDir = plugins_url( 'css', __FILE__ );

		wp_enqueue_style('daigou', $cssDir . '/daigou.css');

		wp_register_script('daigou.Dom', $jsDir . '/Dom.js');
		wp_register_script('daigou.Configuration', $jsDir . '/Configuration.js');
		wp_register_script('daigou.LoadingMask', $jsDir . '/LoadingMask.js', array('jquery', 'daigou.Dom'));

		wp_localize_script('daigou.Configuration', 'DaigouConfiguration', array(
			'ajaxUrl' => admin_url('admin-ajax.php')
		));

		wp_register_script(
			'daigou.ProductUrlInput',
			$jsDir . '/ProductUrlInput.js',
			array('jquery', 'daigou.Dom', 'daigou.Configuration', 'daigou.LoadingMask')
		);
		wp_register_script('daigou.add-product-page', $jsDir . '/add-product-page.js', array('jquery', 'daigou.ProductUrlInput'));
		wp_register_script('daigou.Guide', $jsDir . '/Guide.js', array('jquery', 'daigou.Dom'));
	}

	public function ajax_get_product_by_id() {
		require_once(__DIR__ . '/lib/TaoBaoClient.php');
		require_once(__DIR__ . '/lib/ExchangeRateManager.php');

		$id = intval($_POST['id']);
		$result = TaoBaoClient::getProductById($id);

		if (!$result || !property_exists($result, 'item')) {
			echo json_encode(array(
				'error' => '找不到您所要的商品哟，亲!请人肉发送至request@daigouge.com',
			));
			die();
		}

		/* Default post config for reference
		$defaults = array(
			'post_status'           => 'draft',
			'post_type'             => 'post',
			'post_author'           => $user_ID,
			'ping_status'           => get_option('default_ping_status'),
			'post_parent'           => 0,
			'menu_order'            => 0,
			'to_ping'               => '',
			'pinged'                => '',
			'post_password'         => '',
			'guid'                  => '',
			'post_content_filtered' => '',
			'post_excerpt'          => '',
			'import_id'             => 0
		);
		*/

		// For WooTheme compatibility.
		// Define variables that are used w/o pre-checking.
		$_POST['post_type'] = 'product';

		$item = $result->{'item'};
		$price_in_rmb = (float) $item->{'price'};
		$exchange_rate = max(1, ExchangeRateManager::get_rate_from_cad_to_rmb() - 0.3);
		$price_in_cad = $price_in_rmb / $exchange_rate;
		$product_url = $item->{'detail_url'};
		$post_content = sprintf('
			 商品链接: %s %6$s
			 单件价格(人民币): ¥%.2f %6$s
			 单件价格(加元): C$%.2f %6$s
			 汇率: %.2f %6$s
			 商品详情: %s %6$s
		', $product_url, $price_in_rmb, $price_in_cad, $exchange_rate, $item->{'desc'}, PHP_EOL);

		// Add new product
		$product = array(
			'post_type'    => 'product',
			'post_title'   => $item->{'title'},
			'post_content' => $post_content,
			'post_status'  => 'publish',
			// 'post_author'  => $user_ID,
		);
		$product_id = \wp_insert_post($product);

		if (gettype($product_id) !== 'integer' || $product_id === 0) {
			echo json_encode(array(
				'error' => '有错误发生，亲!请重试一次',
			));
			die();
		}

		// Update product slug as product_id => cleaner URL
		$product['ID'] = $product_id;
		$product['post_name'] = $product_id;
		\wp_update_post($product);

		// Update product meta
		\update_post_meta( $product_id, '_regular_price', $price_in_cad );
		\update_post_meta( $product_id, '_price', $price_in_cad );
		\update_post_meta( $product_id, '_layout', 'layout-full' );
		\update_post_meta( $product_id, '_visibility', 'hidden' );

		// Add product picture as attachment, and assign as product thumbnail.
		$prod_pic = array(
			'post_type'      => 'attachment',
			'post_title'     => 'Picture for Product #' . $product_id,
			'post_mime_type' => 'image/jpeg',
			'post_status'    => 'inherit',
			// 'post_author'    => $user_ID,
			'post_parent'    => $product_id,
			'guid'           => $item->{'pic_url'},
		);
		$prod_pic_id = \wp_insert_post($prod_pic);

		// Continue upon error for product picture insertion.
		if (gettype($product_id) === 'integer' && $product_id > 0) {
			\update_post_meta( $product_id, '_thumbnail_id', $prod_pic_id );
		}

		echo json_encode(array(
			'productUrl' => \get_permalink( $product_id ),
		));

		die();
	}

	public function add_guide() {
		wp_enqueue_script('daigou.Guide');
		echo '<div id="guide-container" style="width: 800px; margin: 0 auto;"></div>';
	}

	public function add_taobao_url_textfield($attributes) {
		wp_enqueue_script('daigou.add-product-page');
		require(__DIR__ . '/page/add-product-page.php');
	} // End add_taobao_url_textfield

	public function display_external_product_image($result, $post_id) {
		$post_image_id = \get_post_meta($post_id, '_thumbnail_id', true);
		if (strlen($post_image_id) > 0) {
			$post_image = \get_post($post_image_id);
			$post_image_url = $post_image->guid;
			$post_image_title = $post_image->post_title;
			if (strlen($post_image_url) > 0) {
				$image = sprintf('<img width="300" src="%s" class="attachment-shop_single wp-post-image" alt="%s">', $post_image_url, $post_image_title);
				$result = sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s"  rel="prettyPhoto">%s</a>', $post_image_url, $post_image_title, $image );
			}
		}

		return $result;
	}

	public function move_woocommerce_tabs() {
		if (\get_post_type() == 'product') {
			echo '<script>';
			// echo '	jQuery(".summary").append(jQuery(".woocommerce-tabs"));';
			// echo '	jQuery(".reviews_tab").hide();';

			echo '	jQuery(".reviews_tab a").html("买家备注");';
			echo '	jQuery("#comments h2").html("买家备注");';
			echo '	jQuery(".add_review").remove();';
			echo '	jQuery(".noreviews").html("暂时没有买家备注. ");';
			echo '</script>';
		}
	}

	public function add_customer_notes_textfield() {
		echo '<div style="display: inline-block;">
				<p>打折商品的抓取价格可能与真实价格不符. 如出现此情况,
				请您在上方填写该商品的单件价格, 并以<strong>"价格需要调整"</strong>的方式提交订单.
				我们会尽快调整订单的价格并与您邮件联系. <br><strong>汇率/价格明细请见下方的商品介绍</strong></p>
				</div>';
		echo '<textarea name="notes" style="display:inline-block;width:100%;min-height:120px;margin:0 0 10px 0;"
				placeholder="请在这里注明需要的尺寸, 颜色, 或者其他对该商品的特殊需求. ">尺码:
颜色:
单件价格(人民币):
其他特殊要求:</textarea>';
	}

	public function add_customer_notes($url) {
		// Retrieve customer notes, skip if has nothing.
		$notes = $_REQUEST['notes'];
		if (strlen($notes) == 0) {
			return $url;
		}

		// Retrieve corresponding product id.
		$product_id = $_REQUEST['add-to-cart'];
		if (strlen($product_id) == 0) {
			$product_id = $_REQUEST['product_id'];
		}

		// Cannot retrieve corresponding product id, just quit.
		if (strlen($product_id) == 0) {
			return $url;
		}

		// Construct and insert as comment to the product.
		$content = $notes . PHP_EOL;
		$comment = array(
			'comment_post_ID' => $product_id,
			'comment_content' => $content,
			'comment_agent' => 'Daigou',
			'comment_author_IP' => $_SERVER['HTTP_X_FORWARDED_FOR'],
		);

		if (\is_user_logged_in()) {
			$current_user = \wp_get_current_user();
			$comment['comment_author'] = $current_user->user_login;
			$comment['comment_author_email'] = $current_user->user_email;
			$comment['user_id'] = $current_user->ID;
		}

		\wp_insert_comment($comment);

		return $url;
	}

	public function cache_new_customer_data($data) {
		$this->new_customer_data = $data;
		return $data;
	}

	public function save_new_customer_data($user_id) {
		if (!empty($this->new_customer_data)) {
			$user_pass = $this->new_customer_data['user_pass'];
			if (strlen($user_pass) > 0) {
				\update_user_meta($user_id, '_user_pass', $user_pass);
			}
			unset($this->new_customer_data);
		}
	}

	public function save_customer_new_pass($user_id) {
		if (is_object( $user_id )) {
			$user_id = $user_id->ID;
		}
		if (isset( $_POST['password_1'] )) {
			\update_user_meta($user_id, '_user_pass', esc_attr( $_POST['password_1'] ));
		}
	}

	public function add_continue_shopping() {
		echo '<script type="text/javascript">
				(function($){
					$(\'.woocommerce-message\').append(
						\'<a href="http://www.daigouge.com/" class="button" style="margin-right:12px;">继续购物 →</a>\'
					);
				}).call(document, jQuery);
			</script>';
	}
}

$wpDaigou = new Daigou();
