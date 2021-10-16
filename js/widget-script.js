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
	wpew_AjaxLoadAndReplace(jQuery("#wpew_company_admins"),'getCompanyAdmins',companySelected);
	wpew_AjaxLoadAndReplace(jQuery("#wpew_company_members"),'getCompanyMembers',companySelected);
}

function wpew_company_addbutton() {
	var newCompany=jQuery("#wpew_company_newname").val();
	wpew_AjaxLoadAndReplace(wpew_LoadCompanyInitial,'addCompany',newCompany);
}

function wpew_company_admins_savebutton() {
	var companySelected=jQuery("#wpew_company_id").val();
	if (isNaN(companySelected) || companySelected == null) {
		alert("Please select company first");
		return;
	}
	var admins=jQuery("#wpew_company_admins").val();
	var payload=companySelected+"=";
	for (var i=0;i<admins.length;i++) {
		if (i>0) payload+=",";
		payload+=admins[i];
	}
	wpew_AjaxLoadAndReplace(wpew_LoadCompanyInitial,'setCompanyAdmins',payload);
}

function wpew_company_member_addbutton() {
	var companySelected=jQuery("#wpew_company_id").val();
	if (isNaN(companySelected) || companySelected == null) {
		alert("Please select company first");
		return;
	}
	var payload=companySelected+"="+jQuery("#wpew_company_member_newmail").val();
	wpew_AjaxLoadAndReplace(wpew_LoadCompanyInitial,'addCompanyMember',payload);
}


function wpew_LoadCompanyInitial() {
	// initialize some buttons
	jQuery("#wpew_company_addbutton").click(wpew_company_addbutton);
	jQuery("#wpew_company_id").change(wpew_company_changed);
	jQuery("#wpew_company_admins_savebutton").click(wpew_company_admins_savebutton);
	jQuery("#wpew_company_member_addbutton").click(wpew_company_member_addbutton);

	return wpew_AjaxLoadAndReplace(jQuery("#wpew_company_id"),'getCompanies',null);
}

