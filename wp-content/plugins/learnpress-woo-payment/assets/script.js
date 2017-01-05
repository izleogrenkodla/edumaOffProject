;(jQuery(function ($) {
	function get_cart_option() {
		return LP_WooCommerce_Payment.woocommerce_cart_option;
	}

	$('form.purchase-course').submit(function () {
		var $form = $(this),
			$button = $('button.purchase-button', this),
			$view_cart = $('.view-cart-button', this),
			$clicked = $form.find('input:focus, button:focus'),
			addToCart = $clicked.hasClass('button-add-to-cart');
		$button.removeClass('added').addClass('loading');
		$form.find('#learn-press-wc-message').remove();

		$.ajax({
			url     : window.location.href + (!addToCart ? '?checkout=yes' : ''),
			data    : $(this).serialize(),
			error   : function () {
				$button.removeClass('loading');
			},
			dataType: 'text',
			success : function (response) {
				response = LP.parseJSON(response);
				if (response.message) {
					var $message = $(response.message).addClass('woocommerce-message');
					$form.prepend($('<div id="learn-press-wc-message"></div>').append($message));
					LP.unblockContent();
				}
				if (response.redirect) {
					LP.reload(response.redirect);
				} else if (response.cart_item_key) {
					if (get_cart_option() == 'single') {
						$form.find('.purchase-button, .button-add-to-cart').remove();
					}
				}
			}
		});
		return false;
	});
}));