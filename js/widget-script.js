jQuery("#wpew_form").submit(function(event) {

    /* stop form from submitting normally */
    event.preventDefault();

    /* get the action attribute from the form element */
    var url = jQuery( this ).attr( 'action' );
    var subact = jQuery( this ).attr( 'subact');

    /* Send the data using post */
    jQuery.ajax({
        type: 'POST',
        url: url,
        data: {
            action: jQuery('#wpew_action').val(),
	    subact: subact,
            email: jQuery('#wpew_email').val(), 
            nonce: jQuery('#wpew_nonce_field').val()
        },
        success: function (data, textStatus, XMLHttpRequest) {
            alert(data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
        }
    });
  
});

function wpew_AjaxLoadAndReplace(element,subact,payload) {
	var url = jQuery("#wpew_company_form").attr( 'ajaxurl' );
	 jQuery.ajax({
		type: 'POST',
		url: url,
		data: {
			action: jQuery('#wpew_action').val(),
			subact: subact,
			nonce: jQuery('#wpew_nonce_field').val(),
			payload: payload
		},
		success: function (data, textStatus, XMLHttpRequest) {
			if (typeof element === 'function') element(data);
			else element.html(data);
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert(errorThrown);
		}
	});
}

function wpew_company_changed() {
	var companySelected=jQuery("#wpew_company_id").val();
	alert("Company Selected:"+companySelected);
}

function wpew_company_addbutton() {
	var newCompany=jQuery("#wpew_company_newname").val();
	wpew_AjaxLoadAndReplace(wpew_LoadCompanyInitial,'addCompany',newCompany);
}

function wpew_LoadCompanyInitial() {
	// initialize some buttons
	jQuery("#wpew_company_addbutton").click(wpew_company_addbutton);
	jQuery("#wpew_company_id").change(wpew_company_changed);

	return wpew_AjaxLoadAndReplace(jQuery("#wpew_company_id"),'getCompanies',null);
}

