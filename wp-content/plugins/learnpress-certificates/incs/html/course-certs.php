<?php
global $post;
$certs       = get_posts(
	array(
		'post_type' => 'lp_cert'
	)
);
$course_cert = get_post_meta( $post->ID, '_lp_cert', true );
?>
<?php if ( $certs ): ?>
	<ul id="learn-press-certs-browse">
		<?php foreach ( $certs as $cert ): ?>
			<li class="<?php echo $course_cert == $cert->ID ? 'selected' : ''; ?>">
				<div class="cert-wrap">
					<img src="<?php echo get_post_meta( $cert->ID, '_lp_cert_preview', true ); ?>" />
					<p class="cert-name">
						<span><?php echo get_the_title( $cert->ID ); ?></span>
						<?php
						$is_editable = false;
						if(learn_press_get_current_user_id() == $cert->post_author || learn_press_get_current_user()->is_admin()){
							$editlink = get_edit_post_link( $cert->ID );
							$is_editable = true;
						}else{
							$editlink = 'javascript:void();';
						}
						?>
						<a href="<?php echo $editlink; ?>" class="button<?php echo !$is_editable ? ' disabled': '';?>"><?php _e( 'Edit', 'learnpress-certificates' ); ?></a>
						<span class="dashicons dashicons-yes"></span>
					</p>
					<div class="overlay"></div>
				</div>
				<input class="learn-press-cert-checkbox" type="radio" name="learn-press-cert" value="<?php echo $cert->ID; ?>" <?php checked( $course_cert == $cert->ID ); ?> />
			</li>
		<?php endforeach; ?>
	</ul>
<?php else: ?>
	<p><?php esc_html_e( 'No certificates found', 'learnpress' ); ?></p>
<?php endif; ?>
<script type="text/javascript">
	jQuery(function ($) {
		$('#learn-press-certs-browse').on('click', 'li', function () {
			var $clicked = $(this);
			$clicked.toggleClass('selected')
				.siblings('li').removeClass('selected');
			$clicked.find('input[name="learn-press-cert"]').prop('checked', $clicked.hasClass('selected'));
		})
	})
</script>