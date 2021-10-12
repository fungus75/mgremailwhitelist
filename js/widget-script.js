jQuery("#wpew_form").submit(function(event) {

    /* stop form from submitting normally */
    event.preventDefault();

    /* get the action attribute from the form element */
    var url = jQuery( this ).attr( 'action' );

    /* Send the data using post */
    jQuery.ajax({
        type: 'POST',
        url: url,
        data: {
            action: jQuery('#wpew_action').val(),
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

