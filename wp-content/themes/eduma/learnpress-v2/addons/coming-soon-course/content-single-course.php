<?php
/**
 * Template for displaying content of landing course
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php do_action( 'learn_press_before_content_coming_soon' ); ?>

<?php the_title( '<h1 class="entry-title" itemprop="name">', '</h1>' ); ?>

<div class="thim-top-course">
	<?php do_action( 'learn_press_content_coming_soon_countdown' ); ?>
	<?php learn_press_get_template( 'single-course/thumbnail.php', array() ); ?>
</div>

<?php do_action( 'learn_press_content_coming_soon_message' ); ?>

<?php do_action( 'learn_press_after_content_coming_soon' ); ?>
