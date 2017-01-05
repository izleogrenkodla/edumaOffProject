<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode Heading
 *
 * @param $atts
 *
 * @return string
 */
function thim_shortcode_list_post( $atts ) {

	$instance = shortcode_atts( array(
		'title'            => '',
		'cat_id'           => '',
		'image_size'       => 'none',
		'show_description' => false,
		'number_posts'     => '4',
		'orderby'          => '',
		'order'            => '',
		'link'             => '',
		'text_link'        => '',
		'style'            => '',
	), $atts );


	$args                 = array();
	$args['before_title'] = '<h3 class="widget-title">';
	$args['after_title']  = '</h3>';

	$widget_template       = THIM_DIR . 'inc/widgets/list-post/tpl/base.php';
	$child_widget_template = THIM_CHILD_THEME_DIR . 'inc/widgets/list-post/base.php';
	if ( file_exists( $child_widget_template ) ) {
		$widget_template = $child_widget_template;
	}

	ob_start();
	echo '<div class="thim-widget-list-post">';
	include $widget_template;
	echo '</div>';
	$html_output = ob_get_contents();
	ob_end_clean();

	return $html_output;
}

add_shortcode( 'thim-list-post', 'thim_shortcode_list_post' );


