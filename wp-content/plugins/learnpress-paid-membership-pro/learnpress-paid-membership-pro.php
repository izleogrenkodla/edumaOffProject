<?php
/*
Plugin Name: LearnPress - Paid Membership Pro Integration
Plugin URI: http://thimpress.com/learnpress
Description: Paid Membership Pro add-on for LearnPress
Author: ThimPress
Version: 2.0
Author URI: http://thimpress.com
Tags: learnpress, lms
Text Domain: learnpress-paid-membership-pro
Domain Path: /languages/
*/

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'LP_ADDON_PMPRO_FILE', __FILE__ );
define( 'LP_ADDON_PMPRO_PATH', dirname( __FILE__ ) );
define( 'LP_ADDON_PMPRO_URI', plugins_url( '/', LP_ADDON_PMPRO_FILE ) );
define( 'LP_ADDON_PMPRO_VER', '2.0' );
define( 'LP_ADDON_PMPRO_REQUIRE_VER', '2.0' );


/**
 * Class LP_Addon_PMPRO
 */
class LP_Addon_PMPRO {

	/**
	 * @var null
	 */
	protected static $_instance = null;

	public $pmpro_levels;
	protected $user;
	protected $user_level;

	/**
	 * LP_Addon_PMPRO constructor.
	 */
	function __construct() {
		add_action( 'admin_notices', array( $this, 'notifications' ) );

		if ( self::pmpro_is_active() && self::learnpress_is_active() ) {
			$this->_init_hooks();
		}
	}

	public function init() {
		$this->_require();
		$this->pmpro_levels = pmpro_getAllLevels();
		$this->user         = learn_press_get_current_user();
		if ( $this->user ) {
			$this->user_level = pmpro_getMembershipLevelForUser( $this->user->id );
		}

		if ( is_admin() ) {
			$this->admin_require();
		}
	}

	public function admin_require() {
		$this->add_setting();
	}

	public function _require() {
		require_once LP_ADDON_PMPRO_PATH . '/inc/functions.php';
	}

	public function add_setting() {
		require_once LP_ADDON_PMPRO_PATH . '/inc/classes/setting-membership.php';
	}

	public function notifications() {
		if ( $this->pmpro_is_active() ) {
			return;
		};

		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php
				echo wp_kses( '<strong>Paid Membership Pro add-on for LearnPress</strong> requires <a href="https://wordpress.org/plugins/paid-memberships-pro/" target="_blank">Paid Memberships Pro</a> plugin was installed!', array(
					'a'      => array(
						'href'   => array(),
						'target' => array(),
					),
					'strong' => array(),
				) );
				?></p>
		</div>
		<?php
	}

	function pmpro_can_enroll( $course_id ) {
		$course_levels = get_post_meta( $course_id, '_lp_pmpro_levels', false );
		$has_access    = $this->checkUserHasLevel( $course_levels );

		return $has_access;
	}

	/**
	 * @param array $levels array level_id
	 *
	 * @return bool
	 */
	private function checkUserHasLevel( $levels ) {
		$levels = (array) $levels;

		if ( !$this->user_level ) {
			return false;
		}

		foreach ( $levels as $l ) {
			if ( $l == $this->user_level->ID ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Auto create order when user has membership level
	 */
	function auto_create_order() {
		$user = learn_press_get_current_user();
		global $post;
		$course_id = $post->ID;
		$user_id   = $user->id;

		$has_access = $this->pmpro_can_enroll( $course_id );
		$order      = $user->get_course_order( $course_id, 'object' );

		if ( $has_access ) {
			if ( !$order ) {
				$this->create_order( $course_id, $user_id );
			} else {
				$create_via = get_post_meta( $order->id, '_created_via', true );
				if ( $create_via === 'membership' ) {
					$order->update_status( 'completed' );
				} else {
					$this->create_order( $course_id, $user_id );
				}
			}
		} else {
			if ( $order ) {
				$create_via = get_post_meta( $order->id, '_created_via', true );
				if ( $create_via === 'membership' ) {
					$order->update_status( 'pending' );
				}
			}
		}
	}

	/**
	 * Init hooks
	 */
	private function _init_hooks() {
		add_action( 'learn_press_before_main_content', array( $this, 'auto_create_order' ) );

		add_action( 'learn_press_meta_box_loaded', array( $this, 'add_meta_box' ) );
		add_action( 'init', array( __CLASS__, 'load_text_domain' ) );
		add_action( 'init', array( $this, 'init' ) );

		add_action( 'learn_press_settings_tabs_array', array( $this, 'add_tab' ) );
	}

	public function add_tab( $tabs ) {
		$tabs['membership'] = __( 'Memberships', 'learnpress-paid-membership-pro' );

		return $tabs;
	}

	public function add_meta_box() {
		new RW_Meta_Box( $this->meta_box() );
	}

	function meta_box() {
		$prefix         = '_lp_';
		$options_levels = array();
		foreach ( $this->pmpro_levels as $pmpro_level ) {
			$options_levels[$pmpro_level->id] = $pmpro_level->name;
		}

		$meta_box = array(
			'id'       => 'course_pmpro',
			'title'    => __( 'Course Memberships', 'learnpress-paid-membership-pro' ),
			'priority' => 'high',
			'pages'    => array( 'lp_course' ),
			'fields'   => array(
				array(
					'name'        => __( 'Select Membership Levels', 'learnpress-paid-membership-pro' ),
					'id'          => "{$prefix}pmpro_levels",
					'type'        => 'select_advanced',
					'options'     => $options_levels,
					'multiple'    => true,
					'placeholder' => __( 'Select membership levels', 'learnpress-paid-membership-pro' ),
				),
			)
		);

		return apply_filters( 'learn_press_pmpro_meta_box_args', $meta_box );
	}

	private function create_order( $_course_id, $_user_id ) {
		$order_data = array(
			'status'      => apply_filters( 'learn_press_default_order_status', 'completed' ),// pending, processing, etc
			'user_id'     => $_user_id,// user want to assign this order
			'user_note'   => '',
			'created_via' => 'membership' // any string
		);

		LP()->set_object( 'cart', LP_Cart::instance() );

		$order = learn_press_create_order( $order_data );

		$courses     = array( $_course_id );
		$order_total = $order_subtotal = 0;
		foreach ( $courses as $course_id ) {
			$course   = LP_Course::get_course( $course_id );
			$quantity = 1; // should be always 1 for now
			$subtotal = $course->get_price() * $quantity;
			$total    = $subtotal;// may be calculated with a rate, such as discount
			if ( $item_id = $order->add_item(
				array(
					'item_id'         => $course->id,
					'quantity'        => $quantity,
					'subtotal'        => $subtotal,
					'total'           => $total,
					'order_item_name' => get_the_title( $course->id )
				)
			)
			) {
				$order_subtotal += $subtotal;
				$order_total += $total;
			}
		}

		update_post_meta( $order->id, '_payment_method', 'membership' ); // any string but should be the same with 'created_via'
		update_post_meta( $order->id, '_payment_method_title', 'Membership' ); // any string
		update_post_meta( $order->id, '_order_subtotal', $order_subtotal );
		update_post_meta( $order->id, '_order_total', $order_total );

	}

	/**
	 * Return TRUE if Paid Membership PRO plugin is installed and active
	 *
	 * @return bool
	 */
	static function pmpro_is_active() {
		if ( !function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' );
	}

	/**
	 * Return TRUE if Paid Membership PRO plugin is installed and active
	 *
	 * @return bool
	 */
	static function learnpress_is_active() {
		if ( !function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return is_plugin_active( 'learnpress/learnpress.php' );
	}

	/**
	 * Load plugin text domain
	 */
	public static function load_text_domain() {
		if ( function_exists( 'learn_press_load_plugin_text_domain' ) ) {
			learn_press_load_plugin_text_domain( LP_ADDON_PMPRO_PATH, true );
		}
	}

	public static function admin_notice() {
		?>
		<div class="error">
			<p><?php printf( __( '<strong>Paid Membership Pro</strong> addon version %s requires LearnPress version %s or higher', 'learnpress-paid-membership-pro' ), LP_ADDON_PMPRO_VER, LP_ADDON_PMPRO_REQUIRE_VER ); ?></p>
		</div>
		<?php
	}

	/**
	 * Return unique instance of LP_Addon_BBPress_Forum
	 */
	static function instance() {
		if ( !defined( 'LEARNPRESS_VERSION' ) || ( version_compare( LEARNPRESS_VERSION, LP_ADDON_PMPRO_REQUIRE_VER, '<' ) ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
			return false;
		}
		if ( !self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

add_action( 'learn_press_ready', array( 'LP_Addon_PMPRO', 'instance' ) );