<?php
/**
 * User Courses tab
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 2.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
$subtab  = !empty( $_REQUEST['section'] ) ? $_REQUEST['section'] : '';

$subtabs = array(
	'learning'  => esc_html__( 'Learning Courses', 'eduma' ),
	'purchased' => esc_html__( 'Purchased Courses', 'eduma' ),
	'finished'  => esc_html__( 'Finished Courses', 'eduma' ),
	'own'       => esc_html__( 'Own Courses', 'eduma' )
);
$subtabs = apply_filters( 'learn_press_profile_tab_courses_subtabs', $subtabs );

if ( !$subtabs ) {
	return;
}
$subkeys = array_keys( $subtabs );
$firstid = current( $subkeys );
$sublink = learn_press_user_profile_link( $user->id, $current );
?>

<?php

foreach ( $subtabs as $subid => $subtitle ) {
	do_action( 'learn_press_profile_tab_courses_' . $subid, $user, $subid );
}

?>