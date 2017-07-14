var WorldpayUS = function(){
	jQuery('form.checkout').attr("id", "checkout");
	this.setForm();
	this.clientToken = document.getElementById('worldpay_client_token').value;
	this.setupEvents();
}

WorldpayUS.checkForm = function(){
	jQuery('#paymentplugins-container').find('iFrame').each(function(index, element){
		if(index > 0){
			$(element).remove();
		}
	})
}

var createWorldpayUS = function(){
	worldpayUS = new WorldpayUS();
	worldpayUS.setup();
}

WorldpayUS.prototype.setupEvents = function(){
	jQuery('form.checkout').on('checkout_place_order', this.checkoutPlaceOrder);
	jQuery(document.body).on('checkout_error', this.checkoutError);
	//jQuery(document.body).on('change', 'input[name="payment_method"]', this.paymentGatewayChange);
}

WorldpayUS.prototype.setForm = function(){
	if(jQuery('form.checkout').length > 0){
		this.form = '#'+jQuery('form.checkout').attr('id');
	}
	else{
		this.form = '#'+jQuery('#order_review').attr('id');
		jQuery(this.form).on('submit', this.submit);
	}
}

WorldpayUS.prototype.setup = function(){
	paymentplugins.setup(worldpayUS.clientToken, {
		"form": jQuery('form.checkout').length > 0 ? 'checkout' : 'order_review', 
		"container":"paymentplugins-container",
		"onPaymentMethodReceived": function(response){
			worldpayUS.onPaymentMethodReceived(response);
		}
	});
}

WorldpayUS.prototype.onPaymentMethodReceived = function(response){
	worldpayUS.paymentMethodReceived = true;
	jQuery(worldpayUS.form).submit();
}

WorldpayUS.prototype.checkoutError = function(){
	worldpayUS.paymentMethodReceived = false;
}

WorldpayUS.prototype.checkoutPlaceOrder = function(){
	if(worldpayUS.isGatewaySelected()){
		if(worldpayUS.paymentMethodReceived){
			return true;
		}
		return false;
	}
}

WorldpayUS.prototype.isGatewaySelected = function(){
	return document.getElementById('payment_method_worldpay_us').checked;
}

WorldpayUS.prototype.submit = function(){
	if(worldpayUS.isGatewaySelected()){
		if(worldpayUS.paymentMethodReceived){
			return true;
		}
		return false;
	}
}

var worldpayUS = null;
jQuery(document.body).on('worldpay_form_ready', createWorldpayUS);

setInterval(WorldpayUS.checkForm, 1000);