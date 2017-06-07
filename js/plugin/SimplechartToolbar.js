const {
	initialize: parentInitialize,
	refresh: parentRefresh,
} = wp.media.view.Toolbar.prototype;

wp.media.view.Toolbar.Simplechart = wp.media.view.Toolbar.extend({

	initialize: function() {

		_.defaults(this.options, {
		    event : 'inserter',
		    close : false,
			items : {
			    // See wp.media.view.Button
			    inserter     : {
			        id       : 'sc-button',
			        style    : 'primary',
			        text     : 'Insert into post',
			        priority : 80,
			        click    : function() {
					    this.controller.state().doInsert();
					}
			    }
			}
		});

		parentInitialize.apply(this, arguments);

		const serviceName = 'simplechart';

		this.set('pagination', new media.view.Button({
			tagName: 'button',
			classes: 'sc-pagination button button-secondary',
			id: serviceName + '-loadmore',
			text: "Load More",
			priority: -20,
		}));
	},

	refresh: function() {

		const selection = this.controller.state().props.get('_all').get('selection');

		// @TODO i think this is redundant
		this.get('inserter').model.set('disabled', !selection.length);

		media.view.Toolbar.prototype.refresh.apply(this, arguments);

	}

});