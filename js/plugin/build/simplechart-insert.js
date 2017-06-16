'use strict';

/* globals simplechart, tinymce */

document.addEventListener('DOMContentLoaded', function () {
  // Post Frame

  var postFrame = wp.media.view.MediaFrame.Post;

  wp.media.view.MediaFrame.Post = postFrame.extend({
    initialize: function initialize() {
      postFrame.prototype.initialize.apply(this, arguments);

      var id = 'simplechart';
      var controller = {
        id: id,
        toolbar: id + '-toolbar',
        menu: 'default',
        title: 'Insert Chart',
        priority: 100,
        content: 'simplechart-content-all'
      };

      this.on('content:render:simplechart-content-all', this.simplechartContentRender, this);

      this.states.add([new wp.media.controller.Simplechart(controller)]);

      this.on('toolbar:create:simplechart-toolbar', this.simplechartToolbarCreate, this);
    },

    simplechartContentRender: function simplechartContentRender() {
      this.content.set(new wp.media.view.Simplechart({
        controller: this,
        model: this.state().props.get('all'),
        className: 'clearfix attachments-browser simplechart-all'
      }));
    },

    simplechartToolbarCreate: function simplechartToolbarCreate(toolbar) {
      toolbar.view = new wp.media.view.Toolbar.Simplechart({
        controller: this
      });
    }
  });

  // Item

  wp.media.view.SimplechartItem = wp.Backbone.View.extend({
    tagName: 'li',
    className: 'simplechart-item attachment',

    render: function render() {
      this.template = wp.media.template('simplechart-insert-item-all');
      this.$el.html(this.template(this.model.toJSON()));

      return this;
    }
  });

  // View

  wp.media.view.Simplechart = wp.media.View.extend({
    events: {
      'click .simplechart-item-area': 'toggleSelectionHandler',
      'click .simplechart-item .check': 'removeSelectionHandler',
      'submit .simplechart-toolbar form': 'updateInput'
    },

    initialize: function initialize() {
      /* fired when you switch router tabs */

      var _this = this;

      this.collection = new Backbone.Collection();

      this.createToolbar();
      this.clearItems();

      if (this.model.get('items')) {
        this.collection = new Backbone.Collection();
        this.collection.reset(this.model.get('items'));

        jQuery('#simplechart-loadmore').attr('disabled', false).show();
      } else {
        jQuery('#simplechart-loadmore').hide();
      }

      this.collection.on('reset', this.render, this);

      this.model.on('change:params', this.changedParams, this);

      this.on('loading', this.loading, this);
      this.on('loaded', this.loaded, this);
      this.on('change:params', this.changedParams, this);
      this.on('change:page', this.changedPage, this);

      jQuery('.simplechart-pagination').click(function (event) {
        _this.paginate(event);
      });

      if (_this.model.get('fetchOnRender')) {
        _this.model.set('fetchOnRender', false);
        _this.fetchItems();
      }
    },

    render: function render() {
      /* fired when you switch router tabs */

      var selection = this.getSelection();

      if (this.collection && this.collection.models.length) {
        this.clearItems();

        var container = document.createDocumentFragment();

        this.collection.each(function (model) {
          container.appendChild(this.renderItem(model));
        }, this);

        this.$el.find('.simplechart-items').append(container);
      }

      selection.each(function (model) {
        var id = '#simplechart-item-simplechart-all-' + model.get('id');
        this.$el.find(id).closest('.simplechart-item').addClass('selected details');
      }, this);

      jQuery('#simplechart-button').prop('disabled', !selection.length);

      return this;
    },

    renderItem: function renderItem(model) {
      var view = new wp.media.view.SimplechartItem({
        model: model
      });

      return view.render().el;
    },

    createToolbar: function createToolbar() {
      var html = void 0;
      // @TODO this could be a separate view:
      html = '<div class="simplechart-error attachments"></div>';
      this.$el.prepend(html);

      // @TODO this could be a separate view:
      html = '<div class="simplechart-empty attachments"></div>';
      this.$el.prepend(html);

      // @TODO this could be a separate view:
      html = '<ul class="simplechart-items attachments clearfix"></ul>';
      this.$el.append(html);

      // @TODO this could be a separate view:
      var toolbar_template = wp.media.template('simplechart-insert-search-all');
      html = '<div class="simplechart-toolbar media-toolbar clearfix">' + toolbar_template(this.model.toJSON()) + '</div>';
      this.$el.prepend(html);
    },

    removeSelectionHandler: function removeSelectionHandler(event) {
      var target = jQuery('#' + event.currentTarget.id);
      var id = target.attr('data-id');

      this.removeFromSelection(target, id);

      event.preventDefault();
    },

    toggleSelectionHandler: function toggleSelectionHandler(event) {
      if (event.target.href) {
        return;
      }

      var target = jQuery('#' + event.currentTarget.id);
      var id = target.attr('data-id');

      if (this.getSelection().get(id)) {
        this.removeFromSelection(target, id);
      } else {
        this.addToSelection(target, id);
      }
    },

    addToSelection: function addToSelection(target, id) {
      target.closest('.simplechart-item').addClass('selected details');

      this.getSelection().add(this.collection._byId[id]);

      // @TODO why isn't this triggered by the above line?
      this.controller.state().props.trigger('change:selection');
    },

    removeFromSelection: function removeFromSelection(target, id) {
      target.closest('.simplechart-item').removeClass('selected details');

      this.getSelection().remove(this.collection._byId[id]);

      // @TODO why isn't this triggered by the above line?
      this.controller.state().props.trigger('change:selection');
    },

    clearSelection: function clearSelection() {
      this.getSelection().reset();
    },

    getSelection: function getSelection() {
      return this.controller.state().props.get('_all').get('selection');
    },

    clearItems: function clearItems() {
      this.$el.find('.simplechart-item').removeClass('selected details');
      this.$el.find('.simplechart-items').empty();
      this.$el.find('.simplechart-pagination').hide();
    },

    loading: function loading() {
      // show spinner
      this.$el.find('.spinner').addClass('is-active');

      // hide messages
      this.$el.find('.simplechart-error').hide().text('');
      this.$el.find('.simplechart-empty').hide().text('');

      // disable 'load more' button
      jQuery('#simplechart-loadmore').attr('disabled', true);
    },

    loaded: function loaded() {
      // hide spinner
      this.$el.find('.spinner').removeClass('is-active');
    },

    fetchItems: function fetchItems() {
      this.trigger('loading');

      var date = new Date();
      var tzOffsetSeconds = date.getTimezoneOffset() * 60;

      var data = {
        _nonce: simplechart._nonce,
        service: 'simplechart',
        tab: 'all',
        params: this.model.get('params'),
        page: this.model.get('page'),
        max_id: this.model.get('max_id'),
        tz_off: tzOffsetSeconds
      };

      wp.media.ajax('simplechart_request', {
        context: this,
        success: this.fetchedSuccess,
        error: this.fetchedError,
        data: data
      });
    },

    fetchedSuccess: function fetchedSuccess(response) {
      if (!this.model.get('page')) {
        if (!response.items) {
          this.fetchedEmpty(response);
          return;
        }

        this.model.set('min_id', response.meta.min_id);
        this.model.set('items', response.items);

        this.collection.reset(response.items);
      } else {
        if (!response.items) {
          this.moreEmpty(response);
          return;
        }

        this.model.set('items', this.model.get('items').concat(response.items));

        var collection = new Backbone.Collection(response.items);
        var container = document.createDocumentFragment();

        this.collection.add(collection.models);

        collection.each(function (model) {
          container.appendChild(this.renderItem(model));
        }, this);

        this.$el.find('.simplechart-items').append(container);
      }

      jQuery('#simplechart-loadmore').attr('disabled', false).show();
      this.model.set('max_id', response.meta.max_id);

      this.trigger('loaded loaded:success', response);
    },

    fetchedEmpty: function fetchedEmpty(response) {
      this.$el.find('.simplechart-empty').text('No charts matched your search query.').show();
      this.$el.find('.simplechart-pagination').hide();

      this.trigger('loaded loaded:noresults', response);
    },

    fetchedError: function fetchedError(response) {
      this.$el.find('.simplechart-error').text(response.error_message).show();
      jQuery('#simplechart-loadmore').attr('disabled', false).show();
      this.trigger('loaded loaded:error', response);
    },

    moreEmpty: function moreEmpty(response) {
      this.$el.find('.simplechart-pagination').hide();

      this.trigger('loaded loaded:noresults', response);
    },

    updateInput: function updateInput(event) {
      // triggered when a search is submitted

      var params = this.model.get('params');
      this.$el.find('.simplechart-toolbar').find(':input').each(function () {
        var n = jQuery(this).attr('name');
        if (n) {
          params[n] = jQuery(this).val();
        }
      });

      this.clearSelection();
      jQuery('#simplechart-button').attr('disabled', 'disabled');
      this.model.set('params', params);
      this.trigger('change:params'); // why isn't this triggering automatically? might be because params is an object

      event.preventDefault();
    },

    paginate: function paginate(event) {
      if (0 === this.collection.length) {
        return;
      }

      var page = this.model.get('page') || 1;

      this.model.set('page', page + 1);
      this.trigger('change:page');

      event.preventDefault();
    },

    changedPage: function changedPage() {
      // triggered when the pagination is changed
      this.fetchItems();
    },

    changedParams: function changedParams() {
      // triggered when the search parameters are changed
      this.model.set('page', null);
      this.model.set('min_id', null);
      this.model.set('max_id', null);

      this.clearItems();
      this.fetchItems();
    }
  });

  // Toolbar

  var parentInitialize = wp.media.view.Toolbar.prototype.initialize;


  wp.media.view.Toolbar.Simplechart = wp.media.view.Toolbar.extend({
    initialize: function initialize() {
      _.defaults(this.options, {
        event: 'inserter',
        close: false,
        items: {
          // See wp.media.view.Button
          inserter: {
            id: 'simplechart-button',
            style: 'primary',
            text: 'Insert into post',
            priority: 80,
            click: function click() {
              this.controller.state().doInsert();
            }
          }
        }
      });

      parentInitialize.apply(this, arguments);

      this.set('pagination', new wp.media.view.Button({
        tagName: 'button',
        classes: 'simplechart-pagination button button-secondary',
        id: 'simplechart-loadmore',
        text: 'Load More',
        priority: -20
      }));
    },

    refresh: function refresh() {
      var selection = this.controller.state().props.get('_all').get('selection');

      // @TODO i think this is redundant
      this.get('inserter').model.set('disabled', !selection.length);

      wp.media.view.Toolbar.prototype.refresh.apply(this, arguments);
    }
  });

  // Controller

  wp.media.controller.Simplechart = wp.media.controller.State.extend({
    initialize: function initialize() {
      this.props = new Backbone.Collection();

      this.props.add(new Backbone.Model({
        id: 'all',
        params: {},
        page: null,
        min_id: null,
        max_id: null,
        fetchOnRender: true
      }));

      this.props.add(new Backbone.Model({
        id: '_all',
        selection: new Backbone.Collection()
      }));

      this.props.on('change:selection', this.refresh, this);
    },

    refresh: function refresh() {
      this.frame.toolbar.get().refresh();
    },

    /**
     * Replicate MEXP function except with shortcodes intead of URLs.
     */
    doInsert: function doInsert() {
      var selection = this.frame.content.get().getSelection();
      var shortcodes = [];

      selection.each(function (model) {
        shortcodes.push('[simplechart id="' + model.get('id') + '"]');
      }, this);

      if ('undefined' === typeof tinymce || null === tinymce.activeEditor || tinymce.activeEditor.isHidden()) {
        wp.media.editor.insert(_.toArray(shortcodes).join('\n\n'));
      } else {
        wp.media.editor.insert('<p>' + _.toArray(shortcodes).join('</p><p>') + '</p>');
      }

      selection.reset();
      this.frame.close();
    }
  });
});