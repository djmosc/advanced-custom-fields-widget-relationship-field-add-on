;
(function ($) {

	//Saving the original func
	var org_relationship_update_results = acf.relationship_update_results;

	//Assigning proxy fucnc
	acf.relationship_update_results = function (div) {
		var post_type = div.attr('data-post_type');

		//if it's a widget, use our function
		if (post_type == 'widget_field') {
			// add loading class, stops scroll loading
			div.addClass('loading');


			// vars
			var s = div.attr('data-s'),
				paged = parseInt(div.attr('data-paged')),
				taxonomy = div.attr('data-taxonomy'),
				post_type = div.attr('data-post_type'),
				lang = div.attr('data-lang'),
				left = div.find('.relationship_left .relationship_list'),
				right = div.find('.relationship_right .relationship_list');
			args = div.attr('data-args');

			// get results
			$.ajax({
				url     :ajaxurl,
				type    :'post',
				dataType:'html',
				data    :{
					'action'    :'acf_get_widget_results',
					'args'      :args,
					's'         :s,
					'paged'     :paged,
					'taxonomy'  :taxonomy,
					'post_type' :post_type,
					'lang'      :lang,
					'field_name':div.parent().attr('data-field_name'),
					'field_key' :div.parent().attr('data-field_key')
				},
				success :function (html) {

					div.removeClass('no-results').removeClass('loading');

					// new search?
					if (paged == 1) {
						left.find('li:not(.load-more)').remove();
					}


					// no results?
					if (!html) {
						div.addClass('no-results');
						return;
					}


					// append new results
					left.find('.load-more').before(html);


					// less than 10 results?
					var ul = $('<ul>' + html + '</ul>');
					if (ul.find('li').length < 10) {
						div.addClass('no-results');
					}


					// hide values
					acf.relationship_hide_results(div);

				}
			});
		}

		//if not, use the default function
		else {
			org_relationship_update_results(div);
		}
	};

	$(function () {
		$('.field-widget_field .relationship_left .widefat').remove();
		$('.field-widget_field .relationship_list').css({'height':'193px', 'border-top':'1px solid rgb(223, 223, 223)'});
	});

})(jQuery);