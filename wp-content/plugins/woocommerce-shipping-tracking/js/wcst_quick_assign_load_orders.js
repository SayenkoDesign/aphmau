jQuery(".js-data-orders-ajax").select2(
{
  ajax: {
    url: ajaxurl,
    dataType: 'json',
    delay: 250,
	multiple: false,
    data: function (params) {
      return {
        search_string: params.term, // search term
        page: params.page || 1,
		action: 'wcst_get_order_list'
      };
    },
    processResults: function (data, params) 
	{
	  //console.log(params);
	 
       return {
        results: jQuery.map(data.results, function(obj) {
            return { id: obj.order_id, text: "<b>#"+obj.order_id+"</b> on "+obj.order_date+
										  " - <b>Order status: </b> "+obj.order_status+
										  " - <b>User #"+obj.user_id+": </b> "+obj.user_login+
										  " - <b>Email: </b>"+obj.user_email+
										  " - <b>Bills to: </b> "+obj.billing_name_and_last_name+
										  " - <b>Ships to: </b> "+obj.shipping_name_and_last_name
										  //"<br/><b>Ships to</b><br/>"+obj.shipping_address 
										  }; 
        }),
		pagination: {
                      'more': typeof data.pagination === 'undefined' ? false : data.pagination.more
                    }
		};
    },
    cache: true
  },
  escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
  minimumInputLength: 0,
  templateResult: wcoei_formatRepo, 
  templateSelection: wcoei_formatRepoSelection  
}
);

function wcoei_formatRepo (repo) 
{
	if (repo.loading) return repo.text;
	
	var markup = '<div class="clearfix">' +
			'<div class="col-sm-12">' + repo.text + '</div>';
    markup += '</div>'; 
	
    return markup;
  }

  function wcoei_formatRepoSelection (repo) 
  {
	  return repo.full_name || repo.text;
  }