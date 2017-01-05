<?php
/**
 * Template for displaying archive collection course content
 *
 * @author  ThimPress
 * @package LearnPress/Templates
 * @version 1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="learn-press-collections learn-press-courses" id="learn-press-collection-<?php echo $id; ?>">
	<?php
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) : $post = $query->the_post();
			LP_Addon_Collections::$in_loop     = true;
			LP_Addon_Collections::get_template( 'content-collection-course.php' );
		endwhile;
		LP_Addon_Collections::$in_loop = false;
		learn_press_paging_nav(
			array(
				'num_pages'     => $query->max_num_pages,
				'wrapper_class' => 'navigation pagination',
				'paged'         => get_query_var( 'collection_page' )
			)
		);
	} else {
		learn_press_display_message( __( 'No course found!', 'learnpress-collections' ) );
	}
	?>

</div>