<?php

$levels = lp_pmpro_get_all_levels();
global $current_user;

?>

<div class="lp_pmpro_courses_by_level">
	<?php foreach ( $levels as $level ):
		$current_level = false;

		if ( isset( $current_user->membership_level->ID ) ) {
			if ( $current_user->membership_level->ID == $level->id ) {
				$current_level = true;
			}
		}
		?>
		<div class="lp_pmpro_level">
			<header>
				<h2 class="lp_pmpro_title_level"><?php echo esc_html( $level->name ); ?></h2>
				<?php if ( $level->description ): ?>
					<p class="lp_pmpro_description_level"><?php echo $level->description; ?></p>
				<?php endif ?>

				<div class="lp_pmpro_price_level">
					<?php if ( pmpro_isLevelFree( $level ) ): ?>
						<span><strong><?php esc_html_e( 'Free', 'learnpress-paid-membership-pro' ); ?></strong></span>

					<?php else: ?>
						<?php
						$cost_text       = pmpro_getLevelCost( $level, true, true );
						$expiration_text = pmpro_getLevelExpiration( $level );
						$price_text      = '<strong>' . $cost_text . '</strong>';

						if ( ! empty( $expiration_text ) ) {
							$price_text .= '<br>' . $expiration_text;
						}
						?>
						<span><?php echo $price_text; ?></span>
					<?php endif; ?>
				</div>
			</header>

			<main>
				<?php $the_query = lp_pmpro_query_course_by_level( $level->id ); ?>
				<!-- List courses -->
				<?php if ( $the_query->have_posts() ) : ?>
					<ul>
						<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
							<li><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" target="_blank"><?php the_title(); ?></a></li>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					</ul>
				<?php else : ?>
					<p><?php _e( 'No any course!', 'learnpress-paid-membership-pro' ); ?></p>
				<?php endif; ?>
			</main>

			<footer>
				<?php if ( empty( $current_user->membership_level->ID ) || ! $current_level ) { ?>
					<a class="pmpro_btn pmpro_btn-select" href="<?php echo pmpro_url( 'checkout', '?level=' . $level->id, 'https' ) ?>"><?php _e( 'Select', 'learnpress-paid-membership-pro' ); ?></a>
				<?php } elseif ( $current_level ) { ?>
					<?php
					if ( pmpro_isLevelExpiringSoon( $current_user->membership_level ) && $current_user->membership_level->allow_signups ) {
						?>
						<a class="pmpro_btn pmpro_btn-select"
						   href="<?php echo pmpro_url( 'checkout', '?level=' . $level->id, 'https' ) ?>"><?php _e( 'Renew', 'learnpress-paid-membership-pro' ); ?></a>
						<?php
					} else {
						?>
						<a class="pmpro_btn disabled" href="<?php echo pmpro_url( 'account' ) ?>"><?php _e( 'Your&nbsp;Level', 'learnpress-paid-membership-pro' ); ?></a>
						<?php
					}
					?>

				<?php } ?>
			</footer>
		</div>
	<?php endforeach; ?>

	<nav id="nav-below" class="navigation">
		<div class="nav-previous alignleft">
			<?php if ( ! empty( $current_user->membership_level->ID ) ) { ?>
				<a href="<?php echo pmpro_url( "account" ) ?>"><?php _e( '&larr; Return to Your Account', 'learnpress-paid-membership-pro' ); ?></a>
			<?php } else { ?>
				<a href="<?php echo home_url() ?>"><?php _e( '&larr; Return to Home', 'learnpress-paid-membership-pro' ); ?></a>
			<?php } ?>
		</div>
	</nav>
</div>
