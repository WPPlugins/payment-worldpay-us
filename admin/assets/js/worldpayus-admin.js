//Admin functionality
jQuery(document).ready(function(){

jQuery(document).on('change', '#worldpay_billing_type', function(e){
	jQuery('.billing-configuration').slideUp(400);
	var id = '#'+ jQuery(this).val() + '_billing';
	jQuery(id).slideDown(400);
})
jQuery(document).on('change', '#product-type', function(e){
	if(jQuery(this).val() === 'worldpay_variable_subscription'){
		jQuery('.variations_tab').slideDown(400);
	}
});
if(jQuery('#product-type').length && jQuery('#product-type').val() === 'worldpay_variable_subscription'){
	jQuery('.wp_subscriptions_options').css('display', 'block');
}
jQuery(document).on('change', '#product-type', function(e){
	if(jQuery(this).val() === 'worldpay_variable_subscription'){
		jQuery('.wp_subscriptions_options').slideDown(400);
	}
	else {
		jQuery('.wp_subscriptions_options').slideUp(400);
	}
});

jQuery(document).on('change', '#sandbox_environment, #production_environment', function(e){
	if(jQuery(this).attr('id') === 'sandbox_environment'){
		jQuery('#production_environment').attr('checked', false);
	}
	else {
		jQuery('#sandbox_environment').attr('checked', false);
	}
});

jQuery(document).on('change', '#enable_woosubscriptions, #enable_subscriptions', function(e){
	if(jQuery(this).attr('id') === 'enable_woosubscriptions'){
		jQuery('#enable_subscriptions').attr('checked', false);
	}
	else {
		jQuery('#enable_woosubscriptions').attr('checked', false);
	}
});

jQuery(document).on('change', '#production_paypal, #sandbox_paypal', function(e){
	if(jQuery(this).attr('id') === 'sandbox_paypal'){
		jQuery('#production_paypal').attr('checked', false);
	}
	else {
		jQuery('#sandbox_paypal').attr('checked', false);
	}
});

/*Update the subscription length intervals if the subscription_period is changed.*/
jQuery(document).on('change', '#_subscription_period, #_subscription_period_interval', function(){
	var array = subscription_period_json[jQuery('#_subscription_period').val()];
	var html = null;
	var increment = parseInt(jQuery('#_subscription_period_interval').val());
	jQuery.each(array, function(index, value){
		index = parseInt(index);
		if(index % increment == 0){
			html += '<option value=' + index + '>' + value + '</option>';
		}
	});
	jQuery('#_subscription_length').html(html);
});

jQuery(document).on('change', '#_subscription_period', function(){
	var array = subscription_intervals_json[jQuery(this).val()];
	var html = null;
	jQuery.each(array, function(index, value){
		html += '<option value=' + index + '>' + value + '</option>';
	});
	jQuery('#_subscription_period_interval').html(html);
})

jQuery(document.body).on('click', '.div--tutorialHeader ul li a', function(e){
	e.preventDefault();
	var id = jQuery(this).attr('tutorial-container');
	jQuery('.worldpay-explanation-container').each(function(index){
		jQuery(this).slideUp(400);
	});
	jQuery('#' + id).slideDown(400);
});

})