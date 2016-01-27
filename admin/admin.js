jQuery(document).ready(function($) 
{
	jQuery('.featured-item').on('click', function(e) 
	{
		e.preventDefault();
		elmt = jQuery(this);

		var url  = ajax_object.ajax_url;
		var data = { 
			post_id: elmt.data('post-id'), 
			smc_fp_link_nonce: elmt.data('nonce'),
			action: elmt.data('action') 
		};

		jQuery.post(url, data, function(response) {

			if (response.status == 1 && elmt.data('action') == 'add_featured')
			{
				elmt.html("Yes");
				elmt.data("action", "del_featured");
			}
			else if (response.status == 1 && elmt.data('action') == 'del_featured')
			{
				elmt.html("No");
				elmt.data("action", "add_featured");
			}

		}, "json");
	});
});