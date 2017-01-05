<?php
/*
Plugin Name: LearnPress - WooCommerce Payment Methods Integration
Plugin URI: http://thimpress.com/learnpress
Description: Using the payment system provided by WooCommerce
Author: ThimPress
Version: 2.1.1
Author URI: http://thimpress.com
Tags: learnpress,woocommerce
Text Domain: learnpress-woo-payment
Domain Path: /languages/
Requires at least: 3.8
Tested up to: 4.6.1
Last updated: 2015-12-01 3:29pm GMT
*/
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
define( 'LP_ADDON_WOOCOMMERCE_PAYMENT_FILE', __FILE__ );
define( 'LP_ADDON_WOOCOMMERCE_PAYMENT_PATH', dirname( __FILE__ ) );
define( 'LP_ADDON_WOOCOMMERCE_PAYMENT_VER', '2.1' );
define( 'LP_ADDON_WOOCOMMERCE_PAYMENT_REQUIRE_VER', '2.0' );

class LP_Woo_Payment_Init {

	/**
	 * @var int flag to get the error
	 */
	protected static $_error = 0;

	/**
	 * Courses should not display purchase button
	 *
	 * @var array
	 */
	protected $_hide_purchase_buttons = array();
	/**
	 * @var LP_Woo_Payment_Init|null
	 *
	 * Hold the singleton of LP_Woo_Payment_Init object
	 */
	protected static $_instance = null;

	public function __construct() {
		LP_Assets::add_script_tag( $this->_admin_js(), '__all' );
		LP_Assets::add_param( 'woocommerce_cart_option', LP()->settings->get( 'woo_payment_type' ), '__all', 'LP_WooCommerce_Payment' );
		$this->_includes();
	}

	private function _admin_js() {
		if ( !is_admin() ) {
			return '';
		}
		ob_start();
		?>
		<script>
			$('#learn_press_woo_payment_enabled').on('change', function () {
				$('[name="learn_press_woo_payment_type"]').prop('disabled', !this.checked);
			}).trigger('change');
			$(document).on('change', '.woo_payment_type', function (e) {
				e.preventDefault();
				var _this = $(this),
					_value = _this.val(),
					_wrapper = $('.woocommerce_payment_available');
				if (_value === 'payment') {
					_wrapper.removeClass('hide-if-js');
				} else {
					_wrapper.addClass('hide-if-js');
				}
				return false;
			});
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Include files needed
	 */
	public function _includes() {
		// load text domain
		$this->load_textdomain();
		// WooCommerce activated
		if ( $this->woo_actived() && function_exists( 'LP' ) ) {
			// Enabled payment and checkout
			if ( $this->is_enabled() && $this->woo_payment_enabled() || $this->woo_checkout_enabled() ) {
				require_once LP_ADDON_WOOCOMMERCE_PAYMENT_PATH . '/incs/class-wc-product-lp-course.php';
			}
			// init hooks
			$this->init_hooks();
			$payment = LP_ADDON_WOOCOMMERCE_PAYMENT_PATH . '/incs/class-lp-wc-payment.php';
			if ( file_exists( $payment ) ) {
				require_once $payment;
			}

			if ( $this->is_enabled() && $this->woo_checkout_enabled() ) {
				// WooCommerce checkout
				$checkout = require_once LP_ADDON_WOOCOMMERCE_PAYMENT_PATH . '/incs/class-lp-wc-checkout.php';
				if ( file_exists( $checkout ) ) {
					require_once $checkout;
				}
			}
		} else {
			self::$_error = 1;
			add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
		}
	}

	/**
	 * Init hooks
	 */
	public function init_hooks() {
		// return
		if ( !$this->is_enabled() ) {
			return;
		}
		// LearnPress hook
		// add to cart
		add_action( 'learn_press_add_to_cart', array( $this, 'add_course_woo_cart' ), 10, 4 );
		add_filter( 'learn_press_purchase_course_login_redirect', '__return_false' );
		// WooCommerce Empty Cart
		//add_action( 'learn_press_emptied_cart', array( $this, 'empty_woo_cart' ) );
		// remove cart item
		//add_action( 'learn_press_remove_cart_item', array( $this, 'remove_course_woo_cart' ), 10, 4 );
		// trigger create WooCommercer order
		//add_action( 'learn_press_checkout_update_order_meta', array( $this, 'create_woo_order' ), 10, 2 );
		// trigger update WooCommercer order meta when process checkout with LearnPress
		//add_action( 'learn_press_checkout_order_processed', array( $this, 'woo_update_order_meta' ), 10, 2 );
		// trigger update WooCommercer status
		//add_action( 'learn_press_order_status_changed', array( $this, 'woo_update_order_status' ), 10, 3 );

		// product class
		add_filter( 'woocommerce_product_class', array( $this, 'product_class' ), 10, 4 );
		// WooCommerce Empty Cart
		//add_action( 'woocommerce_cart_emptied', array( $this, 'empty_learnpress_cart' ) );
		// Woo Remove Cart item
		//add_action( 'woocommerce_remove_cart_item', array( $this, 'remove_course_learnpress_cart' ), 10, 2 );
		// disabled select box quantity
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'disable_quantity_box' ), 10, 3 );
		// trigger create LearnPress order
		//add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_learnpress_order' ), 10, 2 );
		// update LearnPress order meta when checkout with WooCommerce order
		//add_action( 'woocommerce_checkout_order_processed', array( $this, 'learnpress_update_order_meta' ), 10, 2 );

		// trigger update learnpress status
		add_action( 'woocommerce_order_status_changed', array( $this, 'learnpress_update_order_status' ), 10, 3 );

		//add_filter( 'woocommerce_create_order', array( $this, 'create_order' ) );

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_order_2' ), 10, 2 );

		//if ( !is_admin() ) {
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		//}

		add_action( 'learn_press_before_course_buttons', array( $this, 'purchase_course_notice' ), 10 );
		add_action( 'learn_press_after_course_buttons', array( $this, 'after_course_buttons' ) );

		add_action( 'learn_press_before_purchase_button', array( $this, 'before_purchase_button' ) );
		add_action( 'learn_press_after_purchase_button', array( $this, 'after_purchase_button' ) );
		add_action( 'learn_press_after_purchase_button', array( $this, 'add_to_cart' ) );
		add_action( 'admin_notices', array( $this, 'zzzzz' ), 99 );

		//add_action( 'wp_loaded', array( $this, 'xxxx' ) );

		//add_action('plugins_loaded', array($this, 'do_checkout'));
	}

	public function zzzzz() {
		global $post, $pagenow;
		if ( $pagenow != 'post.php' || empty( $post ) || get_post_type( $post->ID ) != 'shop_order' ) {
			return;
		}
		if ( !$lp_order_id = get_post_meta( $post->ID, '_learn_press_order_id', true ) ) {
			return;
		}
		?>
		<style type="text/css">
			.woo-payment-order-notice p {
				font-size: 24px;
			}
		</style>
		<div class="error woo-payment-order-notice">
			<p>
				<?php printf( __( 'This order is related to LearnPress order, so if you want to do anything with LearnPres please edit it <a href="%s">here</a>', 'learnpress-woo-payment' ), get_edit_post_link( $lp_order_id ) ); ?>
			</p>
		</div>
		<?php
	}

	function xxxx() {
		if ( empty( $_REQUEST['empty-cart'] ) ) {
			return;
		}
		WC()->cart->empty_cart();
		return;
		if ( is_admin() ) {
			return;
		}
		$cart    = WC()->cart;
		$session = WC()->session;
		if ( empty( $_REQUEST['lp-checkout-course'] ) ) {
			if ( $cart->is_empty() && $tmp_cart = WC()->session->get( 'tmp_cart_content' ) )
				$cart->cart_contents = $tmp_cart;

			if ( $course_id = $session->get( 'lp-checkout-course' ) ) {
				foreach ( $cart->get_cart() as $cart_item_key => $cart_data ) {
					print_r( $cart_data );
					echo $course_id;
					if ( $cart_data['product_id'] == $course_id ) {
						$cart->remove_cart_item( $cart_item_key );
						break;
					}
				}
				$session->set( 'lp-checkout-course', 0 );
				die();
			}
			return;
		}
		$course_id = $_REQUEST['lp-checkout-course'];
		$session->set( 'lp-checkout-course', $course_id );
		$session->set( 'tmp_cart_content', $cart->get_cart() );
		$cart->empty_cart();
		$this->add_course_to_cart( $course_id, 1, array() );

	}

	/**
	 * Create LP order after creating new WC order
	 * by checking the meta key is added
	 *
	 * @param $mid
	 * @param $object_id
	 * @param $meta_key
	 * @param $_meta_value
	 */
	public function create_order( $mid, $object_id, $meta_key, $_meta_value ) {
		// WC order key
		if ( '_order_key' != $meta_key ) {
			return;
		}
		// WC order post type
		if ( 'shop_order' != get_post_type( $object_id ) ) {
			return;
		}

		// Get wc order
		$wc_order = wc_get_order( $object_id );
		if ( !$wc_order ) {
			return;
		}

		// Get wc order items
		$wc_items = $wc_order->get_items();
		if ( !$wc_items ) {
			return;
		}

		// Find LP courses in WC order and preparing to create LP Order
		$courses = array();
		foreach ( $wc_items as $item ) {
			$course_id = $item['product_id'];
			// ignore item is not a course post type
			if ( LP_COURSE_CPT != get_post_type( $course_id ) ) {
				continue;
			}
			$courses[] = $item;
		}

		// If there is no course in wc order
		if ( !$courses ) {
			return;
		}

		// Create LP Order
		$order_data = array(
			'create_via' => 'wc'
		);
		$order      = learn_press_create_order( $order_data );
	}

	public function create_order_2( $wc_order_id, $posted ) {
		// Get LP order key related with WC order
		if ( get_post_meta( $wc_order_id, '_lp_order_id' ) ) {
			return;
		}

		// Get wc order
		$wc_order = wc_get_order( $wc_order_id );
		if ( !$wc_order ) {
			return;
		}

		// Get wc order items
		$wc_items = $wc_order->get_items();
		if ( !$wc_items ) {
			return;
		}

		// Find LP courses in WC order and preparing to create LP Order
		$courses = array();
		foreach ( $wc_items as $item ) {
			$course_id = $item['product_id'];
			// ignore item is not a course post type
			if ( LP_COURSE_CPT != get_post_type( $course_id ) ) {
				continue;
			}
			$courses[] = $item;
		}

		// If there is no course in wc order
		if ( !$courses ) {
			return;
		}

		// Create LP Order
		$order_data = array(
			'create_via' => 'wc',
			'status'     => $wc_order->get_status(),
			'user_note'  => $wc_order->customer_note,

		);

		$order = learn_press_create_order( $order_data );
		if ( !$order || !$order->id ) {
			return;
		}
		$order_id = $order->id;
		update_post_meta( $order_id, '_order_currency', get_post_meta( $wc_order_id, '_order_currency', true ) );
		update_post_meta( $order_id, '_prices_include_tax', 'no' );
		update_post_meta( $order_id, '_user_ip_address', learn_press_get_ip() );
		update_post_meta( $order_id, '_user_agent', isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' );
		update_post_meta( $order_id, '_user_id', get_post_meta( $wc_order_id, '_customer_user', true ) );
		update_post_meta( $order_id, '_order_total', $wc_order->get_total() );
		update_post_meta( $order_id, '_order_subtotal', $wc_order->get_subtotal() );
		update_post_meta( $order_id, '_order_key', apply_filters( 'learn_press_generate_order_key', uniqid( 'order' ) ) );
		update_post_meta( $order_id, '_payment_method', get_post_meta( $wc_order_id, '_payment_method', true ) );
		update_post_meta( $order_id, '_payment_method_title', get_post_meta( $wc_order_id, '_payment_method_title', true ) );
		update_post_meta( $order_id, '_create_via', 'wc' );
		update_post_meta( $order_id, '_woo_order_id', $wc_order_id );
		update_post_meta( $wc_order_id, '_learn_press_order_id', $order_id );

		foreach ( $courses as $course_data ) {
			$item = array(
				'order_item_name' => $course_data['name'],
				'item_id'         => $course_data['product_id'],
				'quantity'        => $course_data['qty'],
				'subtotal'        => $course_data['line_subtotal'],
				'total'           => $course_data['line_total']
			);
			$order->add_item( $item, $course_data['qty'] );
		}
	}

	/**
	 * Display message if a course has already added into WooCommerce cart
	 */
	public function purchase_course_notice() {
		/*$order = wc_get_order( 1797 );
		$items = $order->get_items();
		print_r( $order );
		//print_r($order->get)
		print_r( $items );

		$this->create_order_2( 1797, '' );
		die();*/

		$course = LP()->global['course'];
		if ( !$this->is_added_in_cart( $course->id ) ) {

			return;
		}

		//remove_action( 'learn_press_before_course_buttons', array( $this, 'purchase_course_notice' ), 10 );

		wc_add_to_cart_message( array( $course->id => 1 ) );
		wc_print_notices();
		echo '<div class="hide-if-js">';
	}

	public function after_course_buttons() {
		$course = LP()->global['course'];
		if ( !$this->is_added_in_cart( $course->id ) ) {
			return;
		}

		//remove_action( 'learn_press_after_course_buttons', array( $this, 'after_course_buttons' ) );

		echo '</div>';
	}

	/**
	 * Show Add-to-cart button if is enabled
	 */
	public function add_to_cart() {
		if ( LP()->settings->get( 'woo_purchase_button' ) == 'single' ) {
			return;
		}
		?>
		<button class="button button-add-to-cart" data-action="add-to-cart" data-block-content="yes"><?php _e( 'Add to cart', 'learnpress-woo-payment' ); ?></button>
		<?php
	}

	public function before_purchase_button() {
		if ( LP()->settings->get( 'woo_purchase_button' ) != 'cart' ) {
			return;
		}
		echo '<div class="hide-if-js">';
	}

	public function after_purchase_button() {
		if ( LP()->settings->get( 'woo_purchase_button' ) != 'cart' ) {
			return;
		}
		echo '</div>';
	}

	/**
	 * Return true if a course is already added into WooCommerce cart
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	public function is_added_in_cart( $course_id ) {

		if ( !empty( $this->_hide_purchase_buttons[$course_id] ) ) {
			return true;
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$_product = $values['data'];
			if ( $course_id == $_product->id ) {
				$this->_hide_purchase_buttons[$course_id] = true;
				return true;
			}
		}
		return false;
	}

	function scripts() {
		LP_Assets::enqueue_script( 'learn-press-woocommerce', plugins_url( '/', LP_ADDON_WOOCOMMERCE_PAYMENT_FILE ) . 'assets/script.js' );
	}

	/**
	 * Get the product class name.
	 *
	 * @param string
	 * @param string
	 * @param string
	 * @param int
	 *
	 * @return string
	 */
	public function product_class( $classname, $product_type, $post_type, $product_id ) {
		if ( LP_COURSE_CPT == $post_type ) {
			$classname = 'WC_Product_LP_Course';
		}

		return $classname;
	}

	/**
	 * Trigger empty LearnPress Cart
	 *
	 * @return mixed
	 */
	public function empty_learnpress_cart() {
		remove_action( 'woocommerce_cart_emptied', array( $this, 'empty_learnpress_cart' ) );
		//LP()->cart->empty_cart();
		add_action( 'woocommerce_cart_emptied', array( $this, 'empty_learnpress_cart' ) );
	}

	/**
	 * Trigger Empty WooCommerce Cart
	 *
	 * @return mixed
	 */
	public function empty_woo_cart() {
		remove_action( 'learn_press_emptied_cart', array( $this, 'empty_woo_cart' ) );
		//WC()->cart->empty_cart();
		add_action( 'learn_press_emptied_cart', array( $this, 'empty_woo_cart' ) );
	}

	/**
	 * Trigger add to WooCommerce Cart
	 *
	 * @param int   $course_id
	 * @param int   $quantity
	 * @param array $item_data
	 * @param mixed $checkout
	 */
	public function add_course_woo_cart( $course_id, $quantity, $item_data, $checkout ) {
		$cart        = WC()->cart;
		$is_checkout = false;
		if ( !empty( $_REQUEST['checkout'] ) && $is_checkout = ( $_REQUEST['checkout'] == 'yes' ) ) {
			$cart->empty_cart();
		}
		$cart_item_key = $this->add_course_to_cart( $course_id, $quantity, $item_data );
		$json_data     = array(
			'cart_item_key' => $cart_item_key
		);
		if ( !$is_checkout ) {
			ob_start();
			wc_add_to_cart_message( array( $course_id => 1 ) );
			wc_print_notices();
			$message               = ob_get_clean();
			$json_data ['message'] = $message;
		} else {
			$json_data['redirect'] = wc_get_checkout_url();
		}
		learn_press_send_json( $json_data );

	}

	public function do_checkout() {

		$course_id = $_REQUEST['purchase-course'];
		$course    = LP_Course::get_course( $course_id );
		$cart      = WC()->cart;
		//WC()->session->set( 'tmp_cart_content', $cart->get_cart() );

		//$cart->empty_cart();
		//$this->add_course_to_cart( $course_id, 1, array() );

		learn_press_send_json(
			array(
				'redirect' => add_query_arg( 'lp-checkout-course', $course_id, $cart->get_checkout_url() )
			)
		);
	}

	public function add_course_to_cart( $course_id, $quantity, $item_data ) {
		$cart          = WC()->cart;
		$cart_id       = $cart->generate_cart_id( $course_id, 0, array(), $item_data );
		$cart_item_key = $cart->find_product_in_cart( $cart_id );
		if ( $cart_item_key ) {
			$cart->remove_cart_item( $cart_item_key );
		}
		$cart_item_key = $cart->add_to_cart( absint( $course_id ), absint( $quantity ), 0, array(), $item_data );
		return $cart_item_key;
	}

	public function remove_course( $id ) {
		$cart = WC()->cart;
		if ( $cart_items = $cart->get_cart() ) {
			foreach ( $cart_items as $cart_item_key => $cart_item ) {
				if ( $id == $cart_item['product_id'] ) {
					$cart->remove_cart_item( $cart_item_key );
				}
			}
		}
	}

	/**
	 * Remove Course From Woo Cart items
	 *
	 * @param type $item_id
	 * @param type $cart
	 *
	 * @return mixed
	 */
	public function remove_course_woo_cart( $item_id, $cart ) {
		remove_action( 'learn_press_remove_cart_item', array( $this, 'remove_course_woo_cart' ), 10, 4 );
		$item         = $cart->get_item( $item_id );
		$woo_cart_key = WC()->cart->generate_cart_id( $item['item_id'], 0, array(), $item['data'] );
		WC()->cart->remove_cart_item( $woo_cart_key );
		add_action( 'learn_press_remove_cart_item', array( $this, 'remove_course_woo_cart' ), 10, 4 );
	}

	/**
	 * Remove Course item in LearnPress cart
	 *
	 * @param type $item_key
	 * @param type $woo_cart
	 *
	 * @return mixed
	 */
	public function remove_course_learnpress_cart( $item_key, $woo_cart ) {
		remove_action( 'woocommerce_remove_cart_item', array( $this, 'remove_course_learnpress_cart' ), 10, 2 );
		$item = $woo_cart->get_cart_item( $item_key );
		LP()->cart->remove_item( $item['product_id'] );
		add_action( 'woocommerce_remove_cart_item', array( $this, 'remove_course_learnpress_cart' ), 10, 2 );
	}

	/**
	 * Create WooCommerce order
	 *
	 * @param type $order_id
	 * @param type $request
	 *
	 * @return mixed
	 */
	/*public function create_woo_order( $order_id, $request ) {
		remove_action( 'learn_press_checkout_update_order_meta', array( $this, 'create_woo_order' ), 10, 2 );
		remove_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_learnpress_order' ), 10, 2 );
		$woo_order_id = WC()->checkout()->create_order();
		// Store Order ID in session so it can be re-used after payment failure
		WC()->session->order_awaiting_payment = $woo_order_id;
		// set post meta
		update_post_meta( $woo_order_id, '_learn_press_order_id', $order_id );
		update_post_meta( $order_id, '_woo_order_id', $woo_order_id );
		add_action( 'learn_press_checkout_update_order_meta', array( $this, 'create_woo_order' ), 10, 2 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_learnpress_order' ), 10, 2 );
	}*/

	/**
	 * Create LearnPress order
	 *
	 * @param type $order_id
	 * @param type $posted
	 *
	 * @return mixed
	 */
	/*public function create_learnpress_order( $order_id, $posted ) {
		remove_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_learnpress_order' ), 10, 2 );
		remove_action( 'learn_press_checkout_update_order_meta', array( $this, 'create_woo_order' ), 10, 2 );
		$lp_order_id                          = LP()->checkout()->create_order();
		LP()->session->order_awaiting_payment = $lp_order_id;
		// set post meta
		update_post_meta( $order_id, '_learn_press_order_id', $lp_order_id );
		update_post_meta( $lp_order_id, '_woo_order_id', $order_id );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_learnpress_order' ), 10, 2 );
		add_action( 'learn_press_checkout_update_order_meta', array( $this, 'create_woo_order' ), 10, 2 );
	}*/

	/**
	 * Update WooCommerce order item meta when User checkout with LearnPress
	 *
	 * @param type $order_id
	 * @param type $checkout
	 *
	 * @return mixed
	 */
	public function woo_update_order_meta( $order_id, $checkout ) {
		$woo_order_id = get_post_meta( $order_id, '_woo_order_id', true );
		if ( $woo_order_id ) {
			foreach ( $this->get_meta_map() as $key => $name ) {
				update_post_meta( $woo_order_id, $name, get_post_meta( $order_id, $key, true ) );
			}
		}
	}

	/**
	 * Update LearnPress order meta
	 *
	 * @param type $order_id
	 * @param type $posted
	 */
	public function learnpress_update_order_meta( $order_id, $posted ) {
		$lp_order_id = get_post_meta( $order_id, '_learn_press_order_id', true );
		if ( $lp_order_id ) {
			foreach ( $this->get_meta_map() as $key => $name ) {
				update_post_meta( $lp_order_id, $key, get_post_meta( $order_id, $name, true ) );
			}
		}
	}

	/**
	 * Map meta keys from LearnPress order and WooCommerce order
	 *
	 * @return array
	 */
	public function get_meta_map() {
		// map LP order key with WC order key
		$map_keys = array(
			'_order_currency'       => '_order_currency',
			'_user_id'              => '_customer_user',
			'_order_subtotal'       => '_order_total',
			'_order_total'          => '_order_total',
			'_payment_method_id'    => '_payment_method',
			'_payment_method_title' => '_payment_method_title'
		);

		return apply_filters( 'learnpress_woo_meta_caps', $map_keys );
	}

	/**
	 * Trigger update WooCommercer order status when LearnPress order updated
	 *
	 * @param type $order_id
	 * @param type $old_status
	 * @param type $new_status
	 */
	public function woo_update_order_status( $order_id, $old_status, $new_status ) {
		remove_action( 'learn_press_order_status_changed', array( $this, 'woo_update_order_status' ), 10, 3 );
		$woo_order_id = get_post_meta( $order_id, '_woo_order_id', true );
		if ( $woo_order_id ) {
			$woo_order = wc_get_order( $woo_order_id );
			$woo_order->update_status( $new_status );
		}
		add_action( 'learn_press_order_status_changed', array( $this, 'woo_update_order_status' ), 10, 3 );
	}

	/**
	 * Update LearnPress order status when WooCommerce updated status
	 *
	 * @param type $order_id
	 * @param type $old_status
	 * @param type $new_status
	 */
	public function learnpress_update_order_status( $order_id, $old_status, $new_status ) {
		remove_action( 'woocommerce_order_status_changed', array( $this, 'learnpress_update_order_status' ), 10, 3 );
		$lp_order_id = get_post_meta( $order_id, '_learn_press_order_id', true );
		if ( $lp_order_id ) {
			$lp_order = learn_press_get_order( $lp_order_id );
			$lp_order->update_status( $new_status );
		}
		add_action( 'woocommerce_order_status_changed', array( $this, 'learnpress_update_order_status' ), 10, 3 );
	}

	/**
	 * Disable select quantity product has post_type 'lp_course'
	 *
	 * @param int    $product_quantity
	 * @param string $cart_item_key
	 * @param array  $cart_item
	 *
	 * @return mixed
	 */
	public function disable_quantity_box( $product_quantity, $cart_item_key, $cart_item ) {
		return ( $cart_item['data']->post->post_type === 'lp_course' ) ? sprintf( '<span style="text-align: center; display: block">%s</span>', $cart_item['quantity'] ) : $product_quantity;
	}

	public function is_enabled() {
		return LP()->settings->get( 'woo_payment_enabled' ) === 'yes';
	}

	/**
	 * If use woo checkout
	 * @return boolean
	 */
	public function woo_checkout_enabled() {
		return true;//$this->woo_actived() && LP()->settings->get( 'woo_payment_type' ) === 'checkout';
	}

	/**
	 * Payment is enabled
	 * @return boolean
	 */
	public function woo_payment_enabled() {
		return true;//LP()->settings->get( 'woo_payment_type' ) == 'payment' && $this->woo_actived();
	}

	/**
	 * WooCommercer is actived
	 * @return boolean
	 */
	public function woo_actived() {
		if ( !function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		if ( function_exists( 'learn_press_load_plugin_text_domain' ) ) {
			learn_press_load_plugin_text_domain( LP_ADDON_WOOCOMMERCE_PAYMENT_PATH, true );
		}
	}

	/**
	 * Add Admin notices
	 */
	public static function admin_notice() {
		switch ( self::$_error ) {
			case 1:
				echo '<div class="error">';
				echo '<p>' . sprintf( __( 'WooCommerce Payment Gateways require <a href="%s">WooCommerce</a> is installed. Please install and active it before you can using this addon.', 'learnpress-woo-payment' ), 'http://wordpress.org/plugins/woocommerce' ) . '</p>';
				echo '</div>';
				break;
			case 2:
				?>
				<div class="error">
					<p><?php printf( __( '<strong>WooCommerce</strong> addon version %s requires LearnPress version %s or higher', 'learnpress-paid-membership-pro' ), LP_ADDON_WOOCOMMERCE_PAYMENT_VER, LP_ADDON_WOOCOMMERCE_PAYMENT_REQUIRE_VER ); ?></p>
				</div>
				<?php
		}

	}

	/**
	 * Get singleton instance of LP_Woo_Payment_Init class
	 * Check compatibility with LP version
	 *
	 * @return bool|LP_Woo_Payment_Init|null
	 */
	public static function instance() {
		if ( !defined( 'LEARNPRESS_VERSION' ) || ( version_compare( LEARNPRESS_VERSION, LP_ADDON_WOOCOMMERCE_PAYMENT_REQUIRE_VER, '<' ) ) ) {
			self::$_error = 2;
			add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
			return false;
		}
		if ( !self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

add_action( 'plugins_loaded', array( 'LP_Woo_Payment_Init', 'instance' ) );