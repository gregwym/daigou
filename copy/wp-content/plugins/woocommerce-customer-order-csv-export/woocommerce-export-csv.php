<?php
/*
Plugin Name: WooCommerce CSV Export
Plugin URI: http://www.woothemes.com/woocommerce/
Description: This plugin adds export functionality to WooCommerce for customers and orders.
Version: 2.0.2
Author: Ilari Mäkelä
Author URI: http://i28.fi/
*/

/*  Copyright 2012  Ilari Mäkelä  (email : ilari@i28.fi)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once 'woo-includes/woo-functions.php';

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '914de15813a903c767b55445608bf290', '18652' );

if ( is_woocommerce_active() ) {

	/**
	 * Localisation
	 */
	load_plugin_textdomain('wc-export-csv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

	if ( is_admin() ) {

		function woocommerce_export_csv_enqueue_scripts() {
			global $woocommerce, $wp_scripts;

			// Datepicker
			wp_enqueue_script( 'jquery-ui-datepicker' );
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
			wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );

			// Register and include all needed scripts
			wp_enqueue_script( 'woocommerce-export-js', plugins_url( '/js/woocommerce-export.csv.js', __FILE__ ) );
		}

		/**
		 * WordPress Administration Menu
		 */
		function woocommerce_export_csv_admin_menu() {

			$page = add_submenu_page('woocommerce', __( 'WooCommerce CSV Export', 'wc-export-csv' ), __( 'Export CSV', 'wc-export-csv' ), 'manage_woocommerce', 'woocommerce_export_csv', 'woocommerce_export_csv_page' );

			add_action('admin_print_styles-' . $page, 'woocommerce_export_csv_enqueue_scripts');

		}
		add_action( 'admin_menu', 'woocommerce_export_csv_admin_menu' );

		/**
		 * Initialize admin page
		 */
		function woocommerce_export_admin_init() {
			// Check if form was posted and select task accordingly
			if ( isset( $_POST['action'] ) ) {
				if ( $_POST['action'] == 'export' ) {
					$datatype = '';
					$orderitems = NULL;
					$startdate = NULL;
					$enddate = NULL;
					$removechar = NULL;
					$export_format = NULL;
					$status = array();
					$order_status = (array) get_terms('shop_order_status', array('hide_empty' => 0, 'orderby' => 'id'));
					foreach ($order_status as $value) {
						$status[] = $value->name;
					}
					if ( $_POST['export'] == 'clients' ) {
						$datatype = 'clients';
						$export_format = $_POST['export-format-clients'];
					}
					if ( $_POST['export'] == 'orders') {
						$datatype = 'orders';
						$export_format = $_POST['export-format-orders'];
						$status = $_POST['status'];
						$removechar = $_POST['character'];
						$orderitems = mysql_real_escape_string($_POST['orderitems']);
						$startdate = mysql_real_escape_string($_POST['startdate']);
						$enddate = mysql_real_escape_string($_POST['enddate']);
					}
					if ( $datatype ) {

						if ( isset( $_POST['timeout'] ) )
							$timeout = $_POST['timeout'];
						else
							$timeout = 600;

						if ($_POST['bom'] == "FALSE") {
							$bom = FALSE;
						}
						else {
							$bom = TRUE;
						}

						if ( !ini_get( 'safe_mode' ) )
							set_time_limit( $timeout );

						// Generate CSV file for download
						setcookie("fileDownloadToken", $_POST['downloadToken']);
						woocommerce_generate_csv_header();
						woocommerce_export_csv_data( $datatype, $status, $orderitems, $startdate, $enddate, $removechar, $bom, $export_format );
						exit();
					}
				}
			}
		}
		add_action( 'admin_init', 'woocommerce_export_admin_init' );

		/**
		 * Function to create CSV page
		 */
		function woocommerce_export_csv_page() {
			global $wpdb;
			woocommerce_export_csv_template_header();
			woocommerce_export_csv_template_form();
			woocommerce_export_csv_template_footer();
		}

		/**
		 * Function to create CSV file header
		 */
		function woocommerce_generate_csv_header() {
			header( 'Content-type: application/csv' );
			header( 'Content-Disposition: attachment; filename=woocommerce-export.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
		}

		/**
		 * Function to create the contents of the exported CSV file
		 */
		function woocommerce_export_csv_data( $datatype, $status, $orderitems = NULL, $startdate = NULL, $enddate = NULL, $removechar, $bom = FALSE, $export_format ) {

			global $woocommerce, $wpdb;

			if ($bom) {
				$csv = chr(239) . chr(187) . chr(191) . '';
			}
			else {
				$csv = '';
			}
			$separator = ',';

			$orders = woocommerce_export_csv_query($status, $startdate, $enddate);

			switch ( $datatype ) {
				// We are dealing with orders.
			case 'orders':
				// Get custom meta fields
				$custom_meta_fields = apply_filters( 'woocommerce_export_csv_extra_columns',
					array(
						'columns' => array(),
						'data' => array()
					)
				);

				$columns = array();
				// Select the columns based on export format
				// Export format is plugins own format
				if ($export_format=='csv-export-format') {
					// Set and insert column headers for CSV file
					$columns = array(
						__( 'Order ID', 'wc-export-csv' ),
						__( 'Date', 'wc-export-csv' ),
						__( 'Order Status', 'wc-export-csv' ),
						__( 'Shipping', 'wc-export-csv' ),
						__( 'Shipping Tax', 'wc-export-csv' ),
						__( 'Tax', 'wc-export-csv' ),
						__( 'Cart Discount', 'wc-export-csv' ),
						__( 'Order Discount', 'wc-export-csv' ),
						__( 'Order Total', 'wc-export-csv' ),
						__( 'Payment Method', 'wc-export-csv' ),
						__( 'Shipping Method', 'wc-export-csv' ),
						__( 'Billing First Name', 'wc-export-csv' ),
						__( 'Billing Last Name', 'wc-export-csv' ),
						__( 'Billing Email', 'wc-export-csv' ),
						__( 'Billing Phone', 'wc-export-csv' ),
						__( 'Billing Address 1', 'wc-export-csv' ),
						__( 'Billing Address 2', 'wc-export-csv' ),
						__( 'Billing Post code', 'wc-export-csv' ),
						__( 'Billing City', 'wc-export-csv' ),
						__( 'Billing State', 'wc-export-csv' ),
						__( 'Billing Country', 'wc-export-csv' ),
						__( 'Billing Company', 'wc-export-csv' ),
						__( 'Shipping First Name', 'wc-export-csv' ),
						__( 'Shipping Last Name', 'wc-export-csv' ),
						__( 'Shipping Address 1', 'wc-export-csv' ),
						__( 'Shipping Address 2', 'wc-export-csv' ),
						__( 'Shipping Post code', 'wc-export-csv' ),
						__( 'Shipping City', 'wc-export-csv' ),
						__( 'Shipping State', 'wc-export-csv' ),
						__( 'Shipping Country', 'wc-export-csv' ),
						__( 'Shipping Company', 'wc-export-csv' ),
						__( 'Customer Note', 'wc-export-csv' ),
					);

					// All order items inside one column or each on its own row?
					switch ( $orderitems ) {
					case 'oneline':
						$columns[] = __( 'Order Items', 'wc-export-csv' );
						break;
					case 'lineperitem':
						$columns[] = __( 'Item SKU', 'wc-export-csv' );
						$columns[] = __( 'Item Name', 'wc-export-csv' );
						$columns[] = __( 'Item Variation', 'wc-export-csv' );
						$columns[] = __( 'Item Amount', 'wc-export-csv' );
						$columns[] = __( 'Row Price', 'wc-export-csv' );
						break;
					}

					if ( class_exists('WC_EU_VAT_Number') ) {
						$columns[] = __( 'VAT Number', 'wc-export-csv' );
					}

					$columns[] = __( 'Order notes', 'wc-export-csv' );

					$columns = array_merge($columns, $custom_meta_fields['columns']);
				}

				// Export format compatible with import suite plugin
				else {
					// Set and insert column headers for CSV file
					$columns = array(
						'order_number_formatted',
						'order_number',
						'date',
						'status',
						'order_shipping',
						'order_shipping_tax',
						'order_tax',
						'cart_discount',
						'order_discount',
						'order_total',
						'payment_method',
						'shipping_method',
						'customer_user',
						'billing_first_name',
						'billing_last_name',
						'billing_email',
						'billing_phone',
						'billing_address_1',
						'billing_address_2',
						'billing_postcode',
						'billing_city',
						'billing_state',
						'billing_country',
						'billing_company',
						'shipping_first_name',
						'shipping_last_name',
						'shipping_address_1',
						'shipping_address_2',
						'shipping_postcode',
						'shipping_city',
						'shipping_state',
						'shipping_country',
						'shipping_company',
						'customer_note',
					);
				}

				for ( $i = 0; $i < count( $columns ); $i++ ) {
					$csv .= '"' . $columns[$i] . '"'. $separator;
				}

				if ( $orders ) {

					if ($export_format=='csv-export-format') {
						$csv .= "\n";
					}
					else {
						// Count the maximum number of order items so
						// we can set the correct amount of order
						// item columns for the export
						$max_items = "0";
						foreach ( $orders as $order ) {
							$woo_order = new WC_Order($order->orders);
							$items = $woo_order->get_items();
							// Get the amount of order items for single order
							$number_of_items = count( $items );
							// Compare and update if needed
							if ($number_of_items > $max_items) {
								$max_items = $number_of_items;
							}
						}

						// Add correct amount of order_item columns
						for ( $i = 1; $i <= $max_items; $i++ ) {
							$csv .= '"order_item_' .$i . '"'. $separator;
						}

						$csv .= '"order_notes"' . "\n";
					}

					foreach ( $orders as $order ) {

						// Get order object.
						$woo_order = new WC_Order($order->orders);

						// Create empty extra order items var
						$order_extra = array();

						// Get data.
						$order->id = $woo_order->id;
						$order->customer_user = $woo_order->customer_user;
						$order->date = $woo_order->order_date;
						$order->status = __( $woo_order->status, 'woocommerce' );
						$order->customer_note = $woo_order->customer_note;

						$data_fields = array(
							'shipping'           => '_order_shipping',
							'shipping_tax'       => '_order_shipping_tax',
							'tax'                => '_order_tax',
							'cart_discount'      => '_cart_discount',
							'order_discount'     => '_order_discount',
							'order_total'        => '_order_total',
							'payment_method'     => '_payment_method_title',
							'shipping_method'    => '_shipping_method_title',
							'billing_firstname'  => '_billing_first_name',
							'billing_lastname'   => '_billing_last_name',
							'billing_email'      => '_billing_email',
							'billing_phone'      => '_billing_phone',
							'billing_address_1'  => '_billing_address_1',
							'billing_address_2'  => '_billing_address_2',
							'billing_postcode'   => '_billing_postcode',
							'billing_city'       => '_billing_city',
							'billing_state'      => '_billing_state',
							'billing_country'    => '_billing_country',
							'billing_company'    => '_billing_company',
							'shipping_firstname' => '_shipping_first_name',
							'shipping_lastname'  => '_shipping_last_name',
							'shipping_address_1' => '_shipping_address_1',
							'shipping_address_2' => '_shipping_address_2',
							'shipping_postcode'  => '_shipping_postcode',
							'shipping_city'      => '_shipping_city',
							'shipping_state'     => '_shipping_state',
							'shipping_country'   => '_shipping_country',
							'shipping_company'   => '_shipping_company',
							'vat_number'         => 'VAT Number'
						);

						if ( class_exists('WC_EU_VAT_Number') ) {
							$data_fields['vat_number'] = 'VAT Number';
						}

						$extra_data_fields = $custom_meta_fields['data'];

						foreach ($data_fields as $key => $value) {
							$order->$key = isset($woo_order->order_custom_fields[$value]) ? $woo_order->order_custom_fields[$value]['0'] : '';
						}

						foreach ($extra_data_fields as $key => $value) {
							$order_extra[ $key ] = isset($woo_order->order_custom_fields[$value]) ? $woo_order->order_custom_fields[$value]['0'] : '';
						}

						if (isset($woocommerce->countries->countries[ $order->billing_country ])) $order->billing_country = $woocommerce->countries->countries[ $order->billing_country ];
						if (isset($woocommerce->countries->countries[ $order->shipping_country ])) $order->shipping_country = $woocommerce->countries->countries[ $order->shipping_country ];

						// Select the output based on export format
						// Export format is plugins own format
						if ($export_format=='csv-export-format') {
						  // Build the data according to the selected item presentation
							switch ( $orderitems ) {
							case 'oneline':
								$items = $woo_order->get_items();

								$order->items = array();

								foreach ( $items as $key => $value ) {
									$product = $woo_order->get_product_from_item( $value );
									$item = $value['name'];

									if ($product->sku) {
										$item .= ' (' . $product->sku . ')';
									}

									$item .= ' x' . $value['qty'];

									$item_meta = new WC_Order_Item_Meta( $value['item_meta'] );
									$variation = $item_meta->display( true, true );

									if ($variation) $item .= ' - ' . str_replace(array("\r", "\r\n", "\n"), '', $variation);

									$replace_pattern = array('&#8220;', '&#8221;');

									$order->items[] = str_replace($replace_pattern, '""', $item );
								}

								$order->items = implode("; ", $order->items);

								$order_id = $order->orders;
								if ($woo_order->get_order_number()) {
									$order->order_id = ltrim($woo_order->get_order_number(), $removechar);
								}

								// Get order notes from database
								$order_notes = $wpdb->get_results("SELECT DISTINCT comment_content
								FROM (SELECT DISTINCT comment_content
								FROM $wpdb->comments
								WHERE comment_post_ID = " . $woo_order->id . "
								) AS WHATEVER");

								$notes_count = count($order_notes);

								// Add order notes to one column and separate notes with pipe character
								for ( $i = 0; $i < $notes_count; $i++ ) {
									if ( $i == ( $notes_count - 1 ) )
										$order->order_notes .= $order_notes[$i]->comment_content;
									else
									$order->order_notes .= $order_notes[$i]->comment_content . '|';
								}

								// Print data to csv
								$csvdata = array(
									$order->order_id,
									$order->date,
									$order->status,
									$order->shipping,
									$order->shipping_tax,
									$order->tax,
									$order->cart_discount,
									$order->order_discount,
									$order->order_total,
									$order->payment_method,
									$order->shipping_method,
									$order->billing_firstname,
									$order->billing_lastname,
									$order->billing_email,
									$order->billing_phone,
									$order->billing_address_1,
									$order->billing_address_2,
									$order->billing_postcode,
									$order->billing_city,
									$order->billing_state,
									$order->billing_country,
									$order->billing_company,
									$order->shipping_firstname,
									$order->shipping_lastname,
									$order->shipping_address_1,
									$order->shipping_address_2,
									$order->shipping_postcode,
									$order->shipping_city,
									$order->shipping_state,
									$order->shipping_country,
									$order->shipping_company,
									$order->customer_note,
									$order->items
								);

								if ( class_exists('WC_EU_VAT_Number') ) {
									$csvdata[] = $order->vat_number;
								}

								$csvdata[] = $order->order_notes;

								foreach ($order_extra as $key => $value) {
									$csvdata[] = $value;
								}

								$csvdata = array_map( 'woocommerce_export_csv_wrap_data', $csvdata );
								$csv .= '"' . implode( '"' . $separator . '"' , $csvdata ) . '"' . "\n";

								break;
							case 'lineperitem':
									$items = $woo_order->get_items();

									foreach ( $items as $key => $value ) {
										$product = $woo_order->get_product_from_item( $value );
										$replace_pattern = array('&#8220;', '&#8221;');
										$order->itemname = str_replace($replace_pattern, '""', $value['name']);

										if ($product->sku) {
											$order->itemsku = $product->sku;
										}
										else {
											$order->itemsku = '';
										}

										$order->itemamount = $value['qty'];

										$item_meta = new WC_Order_Item_Meta( $value['item_meta'] );
										$variation = $item_meta->display( true, true );

										if ($variation) {
											$order->itemvar = str_replace(array("\r", "\r\n", "\n"), '', $variation);
										}
										else {
											$order->itemvar = '';
										}
										$order->itemtotal = $value['line_total'];

										$order_id = $order->orders;
										if ($woo_order->get_order_number()) {
											$order->order_id = ltrim($woo_order->get_order_number(), $removechar);
										}

										// Get order notes from database
										$order_notes = $wpdb->get_results("SELECT DISTINCT comment_content
										FROM (SELECT DISTINCT comment_content
										FROM $wpdb->comments
										WHERE comment_post_ID = " . $woo_order->id . "
										) AS WHATEVER");

										$notes_count = count($order_notes);

										// Add order notes to one column and separate notes with pipe character
										for ( $i = 0; $i < $notes_count; $i++ ) {
											if ( $i == ( $notes_count - 1 ) )
												$order->order_notes .= $order_notes[$i]->comment_content;
											else
											$order->order_notes .= $order_notes[$i]->comment_content . '|';
										}

										// Print data to csv
										$csvdata = array(
											$order->order_id,
											$order->date,
											$order->status,
											$order->shipping,
											$order->shipping_tax,
											$order->tax,
											$order->cart_discount,
											$order->order_discount,
											$order->order_total,
											$order->payment_method,
											$order->shipping_method,
											$order->billing_firstname,
											$order->billing_lastname,
											$order->billing_email,
											$order->billing_phone,
											$order->billing_address_1,
											$order->billing_address_2,
											$order->billing_postcode,
											$order->billing_city,
											$order->billing_state,
											$order->billing_country,
											$order->billing_company,
											$order->shipping_firstname,
											$order->shipping_lastname,
											$order->shipping_address_1,
											$order->shipping_address_2,
											$order->shipping_postcode,
											$order->shipping_city,
											$order->shipping_state,
											$order->shipping_country,
											$order->shipping_company,
											$order->customer_note,
											$order->itemsku,
											$order->itemname,
											$order->itemvar,
											$order->itemamount,
											$order->itemtotal
										);

										if ( class_exists('WC_EU_VAT_Number') ) {
											$csvdata[] = $order->vat_number;
										}

										$csvdata[] = $order->order_notes;

										foreach ($order_extra as $key => $value) {
											$csvdata[] = $value;
										}

										$csv .= '"' . implode( '"' . $separator . '"' , $csvdata ) . '"' . "\n";
									}
								break;
							}
						}
						// Export format compatible with import suite plugin
						else {

							$order_id = $order->orders;
							if ($woo_order->get_order_number()) {
								$order->order_id = ltrim($woo_order->get_order_number(), $removechar);
							}

							// Print data to csv
							$csvdata = array(
								$order->order_id,
								$order->id,
								$order->date,
								$order->status,
								$order->shipping,
								$order->shipping_tax,
								$order->tax,
								$order->cart_discount,
								$order->order_discount,
								$order->order_total,
								$order->payment_method,
								$order->shipping_method,
								$order->billing_email,
								$order->billing_firstname,
								$order->billing_lastname,
								$order->billing_email,
								$order->billing_phone,
								$order->billing_address_1,
								$order->billing_address_2,
								$order->billing_postcode,
								$order->billing_city,
								$order->billing_state,
								$order->billing_country,
								$order->billing_company,
								$order->shipping_firstname,
								$order->shipping_lastname,
								$order->shipping_address_1,
								$order->shipping_address_2,
								$order->shipping_postcode,
								$order->shipping_city,
								$order->shipping_state,
								$order->shipping_country,
								$order->shipping_company,
								$order->customer_note,
							);

							// Get order items
							$items = $woo_order->get_items();

							// Get the amount of order items
							$number_of_items = count( $items );

							// Add items to array so they can be inserted to csv
							foreach ( $items as $key => $value ) {
								$product = $woo_order->get_product_from_item( $value );
								$item = '';
								if ($product->sku) {
									$item .= $product->sku . '|';
								}

								$item .= $value['qty'] . '|';

								$item .= $value['line_total'];

								$csvdata[] = $item;
							}

							// Insert items to csv
							$csvdata = array_map( 'woocommerce_export_csv_wrap_data', $csvdata );
							$csv .= '"' . implode( '"' . $separator . '"' , $csvdata ) . '"';
							
							$csv .= $separator;

							// Add empty items to even the column amount
							for ( $i = $number_of_items; $i < $max_items; $i++ ) {
								$csv .= '""' . $separator;
							}

							// Get order notes from database
							$order_notes = $wpdb->get_results("SELECT DISTINCT comment_content
							FROM (SELECT DISTINCT comment_content
							FROM $wpdb->comments
							WHERE comment_post_ID = " . $woo_order->id . "
							) AS WHATEVER");

							$notes_count = count($order_notes);

							$csv .= '"';

							// Add order notes to csv file and separate notes with pipe character
							for ( $i = 0; $i < $notes_count; $i++ ) {
								if ( $i == ( $notes_count - 1 ) )
									$csv .= $order_notes[$i]->comment_content;
								else
								$csv .= $order_notes[$i]->comment_content . '|';
							}

							$csv .= '"';

							// Add line break
							$csv .= "\n";
						}
					}
				}
				break;
			// We are dealing with clients here
			case 'clients':

				$columns = array();

				// Select the columns based on export format
				// Export format is plugins own format
			  if ($export_format=='csv-export-format-clients') {
				  // Set and insert column headers for CSV file
					$columns = array(
						__( 'ID', 'wc-export-csv' ),
						__( 'First Name', 'wc-export-csv' ),
						__( 'Last Name', 'wc-export-csv' ),
						__( 'Email', 'wc-export-csv' ),
						__( 'Phone', 'wc-export-csv' ),
						__( 'Address', 'wc-export-csv' ),
						__( 'Address 2', 'wc-export-csv' ),
						__( 'Post code', 'wc-export-csv' ),
						__( 'City', 'wc-export-csv' ),
						__( 'State', 'wc-export-csv' ),
						__( 'Country', 'wc-export-csv' ),
						__( 'Company', 'wc-export-csv' ),
					);
				}

				// Export format compatible with import suite plugin
				else {
					// Set and insert column headers for CSV file
					$columns = array(
						'username',
						'email',
						'date_registered',
						'billing_first_name',
						'billing_last_name',
						'billing_company',
						'billing_address_1',
						'billing_address_2',
						'billing_city',
						'billing_state',
						'billing_postcode',
						'billing_country',
						'billing_email',
						'billing_phone',
						'shipping_first_name',
						'shipping_last_name',
						'shipping_company',
						'shipping_address_1',
						'shipping_address_2',
						'shipping_city',
						'shipping_state',
						'shipping_postcode',
						'shipping_country',
					);
				}

				for ( $i = 0; $i < count( $columns ); $i++ ) {
					if ( $i == ( count( $columns ) - 1 ) )
						$csv .= '"' . $columns[$i] . "\"\n";
			    else
						$csv .= '"' . $columns[$i] . '"'. $separator;
				}

				$check_emails = array();
				$client = new stdClass();
				$client_id = 0;

					if ( $orders ) {
						foreach ( $orders as $order ) {

							// Get order object.
							$woo_order = new WC_Order($order->orders);

							// Get data and compare email addresses to reduce duplicates.
							if (!in_array($woo_order->order_custom_fields['_billing_email']['0'], $check_emails)) {
								$client_id++;
								$check_emails[] = $woo_order->order_custom_fields['_billing_email']['0'];

								$order->customer_user = $woo_order->customer_user;

								$user = get_userdata($order->customer_user);

								$data_fields = array(
									'billing_firstname'  => '_billing_first_name',
									'billing_lastname'   => '_billing_last_name',
									'billing_email'      => '_billing_email',
									'billing_phone'      => '_billing_phone',
									'billing_address_1'  => '_billing_address_1',
									'billing_address_2'  => '_billing_address_2',
									'billing_postcode'   => '_billing_postcode',
									'billing_city'       => '_billing_city',
									'billing_state'      => '_billing_state',
									'billing_country'    => '_billing_country',
									'billing_company'    => '_billing_company',
									'shipping_firstname' => '_shipping_first_name',
									'shipping_lastname'  => '_shipping_last_name',
									'shipping_address_1' => '_shipping_address_1',
									'shipping_address_2' => '_shipping_address_2',
									'shipping_postcode'  => '_shipping_postcode',
									'shipping_city'      => '_shipping_city',
									'shipping_state'     => '_shipping_state',
									'shipping_country'   => '_shipping_country',
									'shipping_company'   => '_shipping_company',
								);

								foreach ($data_fields as $key => $value) {
									$client->$key = isset($woo_order->order_custom_fields[$value]) ? $woo_order->order_custom_fields[$value]['0'] : '';
								}

								// Select the output based on export format
								// Export format is plugins own format
								if ($export_format=='csv-export-format-clients') {

									// Print data to csv
									$csvdata = array(
										$client_id,
										$client->billing_firstname,
										$client->billing_lastname,
										$client->billing_email,
										$client->billing_phone,
										$client->billing_address_1,
										$client->billing_address_2,
										$client->billing_postcode,
										$client->billing_city,
										$client->billing_state,
										$client->billing_country,
										$client->billing_company
									);
								}

								// Export format compatible with import suite plugin
								else {
									// Print data to csv
									$csvdata = array(
									  $user->user_login,
									  $user->user_email,
									  $user->user_registered,
										$client->billing_firstname,
										$client->billing_lastname,
										$client->billing_email,
										$client->billing_phone,
										$client->billing_address_1,
										$client->billing_address_2,
										$client->billing_postcode,
										$client->billing_city,
										$client->billing_state,
										$client->billing_country,
										$client->billing_company,
										$client->shipping_firstname,
										$client->shipping_lastname,
										$client->shipping_address_1,
										$client->shipping_address_2,
										$client->shipping_postcode,
										$client->shipping_city,
										$client->shipping_state,
										$client->shipping_country,
										$client->shipping_company
									);

								}

								$csv .= '"' . implode( '"' . $separator . '"' , $csvdata ) . '"' . "\n";
							}
						}
					}
				break;

			}

			echo $csv;

		}

		/**
		 * woocommerce_export_csv_wrap_data function.
		 *
		 * @access public
		 * @return void
		 */
		function woocommerce_export_csv_wrap_data( $x ) {
			return str_replace( '"', '""', $x );
		}

		/*
		 * Function for creating and executing the db query
		 */
		function woocommerce_export_csv_query($status, $startdate = NULL, $enddate = NULL) {

			global $wpdb;

			$statusquery = '(';

			for ( $i = 0; $i < count( $status ); $i++ ) {
				if ( $i == 0 )
					$statusquery .= $wpdb->prefix . "terms.name = '" . $status[$i] . "'";
				else
					$statusquery .= " OR " . $wpdb->prefix . "terms.name = '" . $status[$i] . "'";
			}
			$statusquery .= ')';

			// Get an array of completed orders (id values)
			$orders_sql = "SELECT DISTINCT " . $wpdb->prefix . "postmeta.post_id AS orders FROM " . $wpdb->prefix . "postmeta LEFT JOIN " . $wpdb->prefix . "posts ON (" . $wpdb->prefix . "postmeta.post_id = " . $wpdb->prefix . "posts.ID) LEFT JOIN " . $wpdb->prefix . "term_relationships ON (" . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "term_relationships.object_id) LEFT JOIN " . $wpdb->prefix . "term_taxonomy ON (" . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "term_taxonomy.term_taxonomy_id) LEFT JOIN " . $wpdb->prefix . "terms ON (" . $wpdb->prefix . "term_taxonomy.term_id = " . $wpdb->prefix . "terms.term_id) WHERE " . $wpdb->prefix . "posts.post_status = 'publish' AND " . $wpdb->prefix . "term_taxonomy.taxonomy = 'shop_order_status' AND " . $statusquery;
			// Insert start date if provided
			if ($startdate!=NULL) {
				$orders_sql .= " AND DATE_FORMAT(" . $wpdb->prefix . "posts.post_date, '%Y-%m-%d') >= '" . $startdate . "'";
			}
			// Insert end date if provided
			if ($enddate!=NULL) {
				$orders_sql .= " AND DATE_FORMAT(" . $wpdb->prefix . "posts.post_date, '%Y-%m-%d') <= '" . $enddate . "'";
			}

			$orders_sql .= " ORDER BY orders DESC";

			return $wpdb->get_results( $orders_sql );

		}

		/*
		 * Function for printing the page header
		 */
		function woocommerce_export_csv_template_header() {

			global $woocommerce; ?>

			<div class="wrap">
				<div id="icon-tools" class="icon32"><br /></div>
				<h2><?php _e( 'Export csv', 'wc-export-csv' ); ?></h2>
				<p><?php _e( 'When you click the export button below the plugin will create a CSV file for you to save to your computer.', 'wc-export-csv' ); ?></p>
			<?php
		}

		/*
		 * Function for printing the page footer
		 */
		function woocommerce_export_csv_template_footer() { ?>
			</div>
			<?php
		}

		/*
		 * Function for printing the page form
		 */
		function woocommerce_export_csv_template_form() { ?>

			<div id="content">
				<form id="woocommerce-export-form" method="post">
					<div id="poststuff">
						<div class="postbox">
							<h3 class="hndle"><?php _e( 'Select what to export', 'wc-export-csv' ); ?></h3>
							<div class="inside export-target">
								<table class="form-table">
									<tr>
										<th>
												<label for="orders"><?php _e( 'Orders', 'wc-export-csv' ); ?></label>
										</th>
										<td>
											<input type="radio" name="export" value="orders" id="export-orders" class="input-radio" />
										</td>
									</tr>
									<tr>
										<th>
												<label for="clients"><?php _e( 'Clients', 'wc-export-csv' ); ?></label>
										</th>
										<td>
											<input type="radio" name="export" value="clients" id="export-clients" class="input-radio" />
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div id="poststuff" style="display: none;" class="info export-orders">
						<div class="postbox">
							<h3 class="hndle"><?php _e( 'Select export format', 'wc-export-csv' ); ?></h3>
							<div class="inside export-style-orders">
								<table class="form-table">
									<tr>
										<th>
												<label for="csv-export-format-orders"><?php _e( 'CSV export regular format', 'wc-export-csv' ); ?></label>
										</th>
										<td>
											<input type="radio" name="export-format-orders" value="csv-export-format" id="export-orders-info" class="input-radio" />
										</td>
									</tr>
									<tr>
										<th>
												<label for="csv-import-format-orders"><?php _e( 'CSV Import suite compatible format', 'wc-export-csv' ); ?></label>
										</th>
										<td>
											<input type="radio" name="export-format-orders" value="csv-import-format-orders" id="export-orders-info" class="input-radio" />
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div id="poststuff" style="display: none;" class="info export-clients">
						<div class="postbox">
							<h3 class="hndle"><?php _e( 'Select export format', 'wc-export-csv' ); ?></h3>
							<div class="inside export-style-clients">
								<table class="form-table">
									<tr>
										<th>
												<label for="export-format-clients"><?php _e( 'CSV export regular format', 'wc-export-csv' ); ?></label>
										</th>
										<td>
											<input type="radio" name="export-format-clients" value="csv-export-format-clients" id="csv-export-format-clients" class="input-radio" />
										</td>
									</tr>
									<tr>
										<th>
												<label for="clients"><?php _e( 'CSV Import suite compatible format', 'wc-export-csv' ); ?></label>
										</th>
										<td>
											<input type="radio" name="export-format-clients" value="csv-import-format-clients" id="csv-import-format-clients" class="input-radio" />
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div id="poststuff" style="display: none;" class="info export-orders-info">
						<div class="postbox">
							<div class="csv-import-format-orders">
						  <h3 class="hndle"><?php _e( 'How to display order items', 'wc-export-csv' ); ?></h3>
							<div class="inside">
							<?php _e( 'Select how to display order items. All order items inside one column or one row for each order item.', 'wc-export-csv' ); ?>
								<table class="form-table">
									<tr>
										<td>
										  <input type="radio" name="orderitems" value="oneline" id="orderitems-oneline" class="input-radio" checked="yes" />
										  <label for="orderitems-oneline"><?php _e( 'All items inside one column', 'wc-export-csv' ); ?></label><br />
										  <input type="radio" name="orderitems" value="lineperitem" id="orderitems-lineperitem" class="input-radio" />
										  <label for="orderitems-lineperitem"><?php _e( 'Each item on own row', 'wc-export-csv' ); ?></label><br />
										</td>
									</tr>
								</table>
							</div>
							</div>
							<h3 class="hndle"><?php _e( 'Date range for orders', 'wc-export-csv' ); ?></h3>
							<div class="inside">
							<?php _e( 'If you don\'t select any dates all orders will be exported.', 'wc-export-csv' ); ?>
								<table class="form-table">
									<tr>
										<td style="width: 30%;">
												<label for="startdate"><?php _e( 'Start date', 'wc-export-csv' ); ?></label><br />
												<input type="text" id="datepicker-field-start" name="startdate" readonly="true" class="export-date" />
										</td>
										<td>
												<label for="enddate"><?php _e( 'End date', 'wc-export-csv' ); ?></label><br />
												<input type="text" id="datepicker-field-end" name="enddate" readonly="true" class="export-date" />
										</td>
									</tr>
								</table>
							</div>
							<h3 class="hndle"><?php _e( 'Order statuses to export', 'wc-export-csv' ); ?></h3>
							<div class="inside">
							<?php _e( 'Select the order statuses you want to be included in export.', 'wc-export-csv' ); ?>
								<table class="form-table">
									<tr>
										<td>
										<?php
			$all_statuses = (array) get_terms('shop_order_status', array('hide_empty' => 0, 'orderby' => 'id'));
			foreach ( $all_statuses as $a_status ) {
				echo '<input type="checkbox" name="status[]" value="' . $a_status->slug . '" id="order-' . $a_status->slug . '" class="input-checkbox" checked="yes" />';
				echo '<label for="' . $a_status->slug . '"> ' . __( $a_status->name, 'woocommerce' ) . '</label><br />';
			}
?>
										</td>
									</tr>
								</table>
							</div>
							<div class="csv-import-format-orders">
							<h3 class="hndle"><?php _e( 'Remove preceding characters from order id', 'wc-export-csv' ); ?></h3>
							<div class="inside">
							<?php _e( 'If you are using some characters in front of order id (default is #1234) you can remove those characters from the export.', 'wc-export-csv' ); ?>
							  <table class="form-table">
							    <tr>
							      <td>
							          <label for="character"><?php _e( 'Character(s) to remove', 'wc-export-csv' ); ?></label><br />
							          <input type="text" id="character" name="character" class="regular-text" value="#" />
							      </td>
							    </tr>
							  </table>
							</div>
							</div>
						</div>
					</div>
					<div id="poststuff">
						<div class="postbox">
							<h3 class="hndle"><?php _e( 'Import Options', 'wc-export-csv' ); ?></h3>
							<div class="inside">
								<table class="form-table">
									<tr>
										<td>
											<label for="bom"><?php _e( 'Add BOM character', 'wc-export-csv' ); ?>: </label>
											<select id="bom" name="bom">
												<option value="TRUE"><?php _e( 'Yes', 'wc-export-csv' ); ?>&nbsp;</option>
												<option value="FALSE" selected="selected"><?php _e( 'No', 'wc-export-csv' ); ?>&nbsp;</option>
											</select><br />
											<span class="description"><?php _e( 'If you are having problems with the export not displaying correctly e.g. in Microsoft Excel you can try adding byte order mark (BOM) in beginning of file.', 'wc-export-csv' ); ?></span>
										</td>
									</tr>
								<?php if ( !ini_get( 'safe_mode' ) ) { ?>
									<tr>
										<td>
											<label for="timeout"><?php _e( 'Script timeout', 'wc-export-csv' ); ?>: </label>
											<select id="timeout" name="timeout">
												<option value="600" selected="selected">10 <?php _e( 'minutes', 'wc-export-csv' ); ?>&nbsp;</option>
												<option value="1800">30 <?php _e( 'minutes', 'wc-export-csv' ); ?>&nbsp;</option>
												<option value="3600">1 <?php _e( 'hour', 'wc-export-csv' ); ?>&nbsp;</option>
												<option value="0"><?php _e( 'Unlimited', 'wc-export-csv' ); ?>&nbsp;</option>
											</select><br />
											<span class="description"><?php _e( 'Script timeout defines how long WooCommerce Exporter is \'allowed\' to process your CSV file, once the time limit is reached the export process halts.', 'wc-export-csv' ); ?></span>
										</td>
									</tr>
									<?php } ?>
								</table>
							</div>
						</div>
					</div>
					<p class="submit">
						<input id="export-submit" type="submit" value="<?php _e( 'Export', 'wc-export-csv' ); ?> &raquo;" class="button" disabled="true" />
					</p>
					<input type="hidden" name="action" value="export" />
					<input type="hidden" id="woocommerce-download-token" name="downloadToken" value="<?php echo time(); ?>" />
				</form>
			</div>
			<?php
		}

	} // End is_admin()
} // End WooCommerce active