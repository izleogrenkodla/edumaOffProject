<?php
/**
 * Created by PhpStorm.
 * User: Tu TV
 * Date: 20/5/2016
 * Time: 10:53 AM
 */

function lp_pmpro_query_course_by_level( $level_id ) {
	$level_id = intval( $level_id );

	$post_type = LP_COURSE_CPT;
	$args      = array(
		'post_type'      => array( $post_type ),
		'post_status'    => array( 'publish' ),
		'posts_per_page' => - 1,
		'meta_query'     => array(
			array(
				'key'   => '_lp_pmpro_levels',
				'value' => $level_id,
			),
		),
	);

	$query = new WP_Query( $args );

	return $query;
}

function lp_pmpro_add_short_code_course_by_level() {
	ob_start();
	learn_press_get_template( 'courses.php', null, learn_press_template_path() . '/addons/paid-membership-pro/', LP_ADDON_PMPRO_PATH . '/templates/' );

	return ob_get_clean();
}

add_shortcode( 'lp_pmpro_courses', 'lp_pmpro_add_short_code_course_by_level' );

function lp_pmpro_get_all_levels() {
	$pmpro_levels = pmpro_getAllLevels( false, true );
	$pmpro_levels = apply_filters( 'lp_pmpro_levels_array', $pmpro_levels );

	return $pmpro_levels;
}