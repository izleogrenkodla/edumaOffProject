<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LP_Settings_PMPro_Membership extends LP_Settings_Base {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id   = 'membership';
		$this->text = __( 'Memberships', 'learnpress-paid-membership-pro' );

		add_action( 'learn_press_settings_save_' . $this->id, array( $this, 'update_content_membership_courses_page' ) );
		parent::__construct();
	}

	/**
	 * Tab's sections
	 *
	 * @return mixed
	 */
	public function get_sections() {
		$sections = array(
			'general' => array(
				'id'    => 'general',
				'title' => __( 'Settings', 'learnpress-paid-membership-pro' )
			),
			'manager' => array(
				'id'    => 'manager',
				'title' => __( 'Managers', 'learnpress-paid-membership-pro' )
			),
		);

		return $sections = apply_filters( 'learn_press_settings_sections_' . $this->id, $sections );
	}

	public function output_section_general() {
		include LP_ADDON_PMPRO_PATH . '/inc/views/membership.php';
	}

	public function get_settings() {
		return apply_filters(
			'learn_press_membership_settings',
			array(
				array(
					'title' => __( 'Paid Membership Pro add-on for LearnPress', 'learnpress-paid-membership-pro' ),
					'type'  => 'title'
				),
				array(
					'title'             => __( 'Use shortcode', 'learnpress-paid-membership-pro' ),
					'id'                => 'membership_shortcode',
					'default'           => '[lp_pmpro_courses]',
					'type'              => 'text',
					'custom_attributes' => array(
						'readonly' => ''
					),
				),
				array(
					'title'   => __( 'Show courses by level', 'learnpress-paid-membership-pro' ),
					'id'      => $this->get_field_name( 'membership_courses_page' ),
					'default' => '',
					'type'    => 'pages-dropdown'
				)
			)
		);
	}

	public function update_content_membership_courses_page() {
		$lp_pmpro_courses_page_id = $this->get_option( 'membership_courses_page' );
		$lp_pmpro_courses_page_id = intval( $lp_pmpro_courses_page_id );

		if ( $lp_pmpro_courses_page_id > 0 ) {
			$page = array(
				'ID'           => $lp_pmpro_courses_page_id,
				'post_content' => '[lp_pmpro_courses]',
			);

			$page_id = wp_update_post( $page );
		}
	}
}

add_filter( 'learn_press_settings_class_membership', 'lp_pmpro_filter_class_setting_membership' );

function lp_pmpro_filter_class_setting_membership() {
	return 'LP_Settings_PMPro_Membership';
}

return new LP_Settings_PMPro_Membership();