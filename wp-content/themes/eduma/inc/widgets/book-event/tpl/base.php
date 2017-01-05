<?php
if( version_compare( get_option( 'event_auth_version' ), '1.0.4', '>=' ) ) {
	tpe_auth_addon_get_template( 'form-book-event.php', array( 'event_id' => get_the_ID() ) );
}else{
	TP_Event_Authentication()->loader->load_module( '\TP_Event_Auth\Events\Event' )->book_event_template();
}
