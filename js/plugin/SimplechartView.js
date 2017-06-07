// VIEW - MEDIA CONTENT AREA

media.view.Simplechart = media.View.extend({

	events: {
		'click .simplechart-item-area'     : 'toggleSelectionHandler',
		'click .simplechart-item .check'   : 'removeSelectionHandler',
		'submit .simplechart-toolbar form' : 'updateInput'
	},

	initialize: function() {

		/* fired when you switch router tabs */
		
		var _this = this;

		this.collection = new Backbone.Collection();
		this.service    = this.options.service;
		this.tab        = this.options.tab;

		this.createToolbar();
		this.clearItems();

		if ( this.model.get( 'items' ) ) {

			this.collection = new Backbone.Collection();
			this.collection.reset( this.model.get( 'items' ) );

			jQuery( '#' + this.service.id + '-loadmore' ).attr( 'disabled', false ).show();
		} else {
			jQuery( '#' + this.service.id + '-loadmore' ).hide();
		}

		this.collection.on( 'reset', this.render, this );

		this.model.on( 'change:params', this.changedParams, this );

		this.on( 'loading',       this.loading, this );
		this.on( 'loaded',        this.loaded, this );
		this.on( 'change:params', this.changedParams, this );
		this.on( 'change:page',   this.changedPage, this );

		jQuery( '.simplechart-pagination' ).click( function( event ) {
			_this.paginate( event );
		} );
		
		if ( _this.model.get( 'fetchOnRender' ) ) {
			_this.model.set( 'fetchOnRender', false );
			_this.fetchItems();
		}

	},

	render: function() {

		/* fired when you switch router tabs */

		var selection = this.getSelection();

		if ( this.collection && this.collection.models.length ) {

			this.clearItems();

			var container = document.createDocumentFragment();

			this.collection.each( function( model ) {
				container.appendChild( this.renderItem( model ) );
			}, this );

			this.$el.find( '.simplechart-items' ).append( container );

		}

		selection.each( function( model ) {
			var id = '#simplechart-item-' + this.service.id + '-' + this.tab + '-' + model.get( 'id' );
			this.$el.find( id ).closest( '.simplechart-item' ).addClass( 'selected details' );
		}, this );

		jQuery( '#simplechart-button' ).prop( 'disabled', !selection.length );

		return this;

	},

	renderItem : function( model ) {

		var view = new media.view.SimplechartItem({
			model   : model,
			service : this.service,
			tab     : this.tab
		});

		return view.render().el;

	},

	createToolbar: function() {

		// @TODO this could be a separate view:
		html = '<div class="simplechart-error attachments"></div>';
		this.$el.prepend( html );

		// @TODO this could be a separate view:
		html = '<div class="simplechart-empty attachments"></div>';
		this.$el.prepend( html );

		// @TODO this could be a separate view:
		html = '<ul class="simplechart-items attachments clearfix"></ul>';
		this.$el.append( html );

		// @TODO this could be a separate view:
		var toolbar_template = media.template( 'simplechart-' + this.service.id + '-search-' + this.tab );
		html = '<div class="simplechart-toolbar media-toolbar clearfix">' + toolbar_template( this.model.toJSON() ) + '</div>';
		this.$el.prepend( html );

	},

	removeSelectionHandler: function( event ) {

		var target = jQuery( '#' + event.currentTarget.id );
		var id     = target.attr( 'data-id' );

		this.removeFromSelection( target, id );

		event.preventDefault();

	},

	toggleSelectionHandler: function( event ) {

		if ( event.target.href )
			return;

		var target = jQuery( '#' + event.currentTarget.id );
		var id     = target.attr( 'data-id' );

		if ( this.getSelection().get( id ) )
			this.removeFromSelection( target, id );
		else
			this.addToSelection( target, id );

	},

	addToSelection: function( target, id ) {

		target.closest( '.simplechart-item' ).addClass( 'selected details' );

		this.getSelection().add( this.collection._byId[id] );

		// @TODO why isn't this triggered by the above line?
		this.controller.state().props.trigger( 'change:selection' );

	},

	removeFromSelection: function( target, id ) {

		target.closest( '.simplechart-item' ).removeClass( 'selected details' );

		this.getSelection().remove( this.collection._byId[id] );

		// @TODO why isn't this triggered by the above line?
		this.controller.state().props.trigger( 'change:selection' );

	},

	clearSelection: function() {
		this.getSelection().reset();
	},

	getSelection : function() {
		return this.controller.state().props.get( '_all' ).get( 'selection' );
	},

	clearItems: function() {

		this.$el.find( '.simplechart-item' ).removeClass( 'selected details' );
		this.$el.find( '.simplechart-items' ).empty();
		this.$el.find( '.simplechart-pagination' ).hide();

	},

	loading: function() {

		// show spinner
		this.$el.find( '.spinner' ).addClass( 'is-active' );

		// hide messages
		this.$el.find( '.simplechart-error' ).hide().text('');
		this.$el.find( '.simplechart-empty' ).hide().text('');

		// disable 'load more' button
		jQuery( '#' + this.service.id + '-loadmore' ).attr( 'disabled', true );
	},

	loaded: function( response ) {

		// hide spinner
		this.$el.find( '.spinner' ).removeClass( 'is-active' );

	},

	fetchItems: function() {

		this.trigger( 'loading' );

		var data = {
			_nonce  : simplechart._nonce,
			service : this.service.id,
			tab     : this.tab,
			params  : this.model.get( 'params' ),
			page    : this.model.get( 'page' ),
			max_id  : this.model.get( 'max_id' )
		};

		media.ajax( 'simplechart_request', {
			context : this,
			success : this.fetchedSuccess,
			error   : this.fetchedError,
			data    : data
		} );

	},

	fetchedSuccess: function( response ) {

		if ( !this.model.get( 'page' ) ) {

			if ( !response.items ) {
				this.fetchedEmpty( response );
				return;
			}

			this.model.set( 'min_id', response.meta.min_id );
			this.model.set( 'items',  response.items );

			this.collection.reset( response.items );

		} else {

			if ( !response.items ) {
				this.moreEmpty( response );
				return;
			}

			this.model.set( 'items', this.model.get( 'items' ).concat( response.items ) );

			var collection = new Backbone.Collection( response.items );
			var container  = document.createDocumentFragment();

			this.collection.add( collection.models );

			collection.each( function( model ) {
				container.appendChild( this.renderItem( model ) );
			}, this );

			this.$el.find( '.simplechart-items' ).append( container );

		}

		jQuery( '#' + this.service.id + '-loadmore' ).attr( 'disabled', false ).show();
		this.model.set( 'max_id', response.meta.max_id );

		this.trigger( 'loaded loaded:success', response );

	},

	fetchedEmpty: function( response ) {

		this.$el.find( '.simplechart-empty' ).text( this.service.labels.noresults ).show();
		this.$el.find( '.simplechart-pagination' ).hide();

		this.trigger( 'loaded loaded:noresults', response );

	},

	fetchedError: function( response ) {

		this.$el.find( '.simplechart-error' ).text( response.error_message ).show();
		jQuery( '#' + this.service.id + '-loadmore' ).attr( 'disabled', false ).show();
		this.trigger( 'loaded loaded:error', response );

	},

	updateInput: function( event ) {

		// triggered when a search is submitted

		var params = this.model.get( 'params' );
		var els = this.$el.find( '.simplechart-toolbar' ).find( ':input' ).each( function( k, el ) {
			var n = jQuery(this).attr('name');
			if ( n )
				params[n] = jQuery(this).val();
		} );
		
		this.clearSelection();
		jQuery( '#simplechart-button' ).attr( 'disabled', 'disabled' );
		this.model.set( 'params', params );
		this.trigger( 'change:params' ); // why isn't this triggering automatically? might be because params is an object

		event.preventDefault();

	},

	paginate : function( event ) {

		if( 0 == this.collection.length )
			return;

		var page = this.model.get( 'page' ) || 1;

		this.model.set( 'page', page + 1 );
		this.trigger( 'change:page' );

		event.preventDefault();

	},

	changedPage: function() {

		// triggered when the pagination is changed

		this.fetchItems();

	},

	changedParams: function() {

		// triggered when the search parameters are changed

		this.model.set( 'page',   null );
		this.model.set( 'min_id', null );
		this.model.set( 'max_id', null );

		this.clearItems();
		this.fetchItems();

	}

});