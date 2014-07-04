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

			// add loading class, stops scroll loading
			this.$choices.children('.list').html('<p>' + acf._e('relationship', 'loading') + '...</p>');

			// vars
			var data = {
				action		: 'acf/fields/widget_field/query',
				field_key	: this.$el.attr('data-key'),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
			};
			
			
			// merge in wrap data
			$.extend(data, this.o);

			
			// abort XHR if this field is already loading AJAX data
			if( this.$el.data('xhr') )
			{
				this.$el.data('xhr').abort();
			}
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'json',
				type		: 'get',
				cache		: true,
				data		: data,
				success			:	function( json ){
					
					// render
					_this.set({ $el : $el }).render( json );
					
				}
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
		}
//		if it's not our widget field, use default method
		else {

			this.default_fetch();

		}

	};

	acf.add_action('ready append', function( $el ){
		
		acf.get_fields({ type : 'widget_field'}, $el).each(function(){
			
			acf.fields.relationship.set({ $el : $(this) }).init();
			
		});
		
	});

})(jQuery);



