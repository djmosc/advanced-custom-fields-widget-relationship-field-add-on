(function ($) {

	//Assigning proxy function
	var org_relationship_fetch = acf.fields.relationship.fetch;

	acf.fields.relationship.fetch = function () {

		var _this = this,
			$el = this.$el;

		var type = $el.attr('data-post_type');

		//if it's our widget field, use our function
		if (type == 'widget_relationship_field') {

			// add loading class, stops scroll loading
			$el.addClass('loading');

			// get results
			$.ajax({
				url     : acf.o.ajaxurl,
				type    : 'post',
				dataType: 'json',
				data    : $.extend({
					action : 'acf_Widget/get_widget_list',
					post_id: acf.o.post_id,
					nonce  : acf.o.nonce
				}, this.o),
				success : function (json) {

					// render
					_this.set({ $el: $el }).render(json);

				}
			});

		}

		//if not, use the default function
		else {
			org_relationship_fetch();
		}

	};

}) (jQuery);