jQuery(document).ready(function($) {

	var $body = $('body');

	$body.on('change', '#billing_country', function() {
		taxamocheckipmatch();
	});
});

function taxamocheckipmatch() {
	var iplocation = jQuery( "#edd-country").val();
    var billinglocation = jQuery("#billing_country").val();

    if (window.console) console.log('IP Location: ' + iplocation);
    if (window.console) console.log('Billing Location: ' + billinglocation);
    
    if (iplocation == billinglocation) {
    	jQuery( "#edd-confirmation-checkbox" ).hide(1);
    } else {
    	jQuery( "#edd-confirmation-checkbox" ).show(1);
    }
}