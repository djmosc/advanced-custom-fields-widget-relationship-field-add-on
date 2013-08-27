(function ($) {

	// create proxy method
	acf.fields.relationship.default_fetch = acf.fields.relationship.fetch;

	acf.fields.relationship.fetch = function () {

//		if it's our widget field, use our custom method (only difference is the 'action' data attribute)
		if(this.$el.attr('data-post_type') === 'widget_relationship_field' ) {

			var _this = this,
				$el = this.$el;

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

//		if it's not our widget field, use default method
		else {

			this.default_fetch();

		}

	};

})(jQuery);



