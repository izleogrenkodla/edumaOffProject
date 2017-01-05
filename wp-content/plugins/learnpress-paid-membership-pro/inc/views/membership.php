<?php
/**
 * Display settings for checkout
 *
 * @author  ThimPress
 * @package LearnPress/Admin/Views
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$settings = LP()->settings;

?>
<table class="form-table">
	<tbody>
	<?php do_action( 'learn_press_before_' . $this->id . '_' . $this->section['id'] . '_settings_fields', $this ); ?>
	<?php foreach ( $this->get_settings() as $field ) { ?>
		<?php $this->output_field( $field ); ?>
	<?php } ?>

	</tbody>

</table>
<hr>
<h3>For developer</h3>

<p>Create file in your theme with path: <kbd>your_theme/learnpress/addons/paid-membership-pro/courses.php</kbd> to override template.</p>