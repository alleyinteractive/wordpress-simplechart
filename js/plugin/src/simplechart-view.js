/* globals Backbone, simplechart */
// View
export default function () {
  const SimplechartView = wp.media.View.extend({
    events: {
      'click .simplechart-item-area': 'toggleSelectionHandler',
      'click .simplechart-item .check': 'removeSelectionHandler',
      'submit .simplechart-toolbar form': 'updateInput',
    },

    initialize() {
      /* fired when you switch router tabs */

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

      jQuery('.simplechart-pagination').click((event) => {
        this.paginate(event);
      });

      if (this.model.get('fetchOnRender')) {
        this.model.set('fetchOnRender', false);
        this.fetchItems();
      }
    },

    render() {
      /* fired when you switch router tabs */

      const selection = this.getSelection();

      if (this.collection && this.collection.models.length) {
        this.clearItems();

        const container = document.createDocumentFragment();

        this.collection.each((model) => {
          container.appendChild(this.renderItem(model));
        }, this);

        this.$el.find('.simplechart-items').append(container);
      }

      selection.each((model) => {
        const id = `#simplechart-item-simplechart-all-${model.get('id')}`;
        this
          .$el
          .find(id)
          .closest('.simplechart-item')
          .addClass('selected details');
      }, this);

      jQuery('#simplechart-button').prop('disabled', ! selection.length);

      return this;
    },

    renderItem(model) {
      const view = new wp.media.view.SimplechartItem({
        model,
      });

      return view.render().el;
    },

    createToolbar() {
      let html;
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
      // eslint-disable-next-line max-len
      const toolbarTemplate = wp.media.template('simplechart-insert-search-all');
      // eslint-disable-next-line max-len
      html = `<div class="simplechart-toolbar media-toolbar clearfix">${toolbarTemplate(this.model.toJSON())}</div>`;
      this.$el.prepend(html);
    },

    removeSelectionHandler(event) {
      const target = jQuery(`#${event.currentTarget.id}`);
      const id = target.attr('data-id');

      this.removeFromSelection(target, id);

      event.preventDefault();
    },

    toggleSelectionHandler(event) {
      if (event.target.href) {
        return;
      }

      const target = jQuery(`#${event.currentTarget.id}`);
      const id = target.attr('data-id');

      if (this.getSelection().get(id)) {
        this.removeFromSelection(target, id);
      } else {
        this.addToSelection(target, id);
      }
    },

    addToSelection(target, id) {
      target.closest('.simplechart-item').addClass('selected details');

      // eslint-disable-next-line no-underscore-dangle
      this.getSelection().add(this.collection._byId[id]);

      // @TODO why isn't this triggered by the above line?
      this.controller.state().props.trigger('change:selection');
    },

    removeFromSelection(target, id) {
      target.closest('.simplechart-item').removeClass('selected details');

      // eslint-disable-next-line no-underscore-dangle
      this.getSelection().remove(this.collection._byId[id]);

      // @TODO why isn't this triggered by the above line?
      this.controller.state().props.trigger('change:selection');
    },

    clearSelection() {
      this.getSelection().reset();
    },

    getSelection() {
      return this.controller.state().props.get('_all').get('selection');
    },

    clearItems() {
      this.$el.find('.simplechart-item').removeClass('selected details');
      this.$el.find('.simplechart-items').empty();
      this.$el.find('.simplechart-pagination').hide();
    },

    loading() {
      // show spinner
      this.$el.find('.spinner').addClass('is-active');

      // hide messages
      this.$el.find('.simplechart-error').hide().text('');
      this.$el.find('.simplechart-empty').hide().text('');

      // disable 'load more' button
      jQuery('#simplechart-loadmore').attr('disabled', true);
    },

    loaded() {
      // hide spinner
      this.$el.find('.spinner').removeClass('is-active');
    },

    fetchItems() {
      this.trigger('loading');

      const date = new Date();
      const tzOffsetSeconds = date.getTimezoneOffset() * 60;

      /* eslint-disable no-underscore-dangle */
      const data = {
        _nonce: simplechart._nonce,
        service: 'simplechart',
        tab: 'all',
        params: this.model.get('params'),
        page: this.model.get('page'),
        max_id: this.model.get('max_id'),
        tz_off: tzOffsetSeconds,
      };
      /* eslint-enable no-underscore-dangle */

      wp.media.ajax('simplechart_request', {
        context: this,
        success: this.fetchedSuccess,
        error: this.fetchedError,
        data,
      });
    },

    fetchedSuccess(response) {
      if (! this.model.get('page')) {
        if (! response.items) {
          this.fetchedEmpty(response);
          return;
        }

        this.model.set('min_id', response.meta.min_id);
        this.model.set('items', response.items);

        this.collection.reset(response.items);
      } else {
        if (! response.items) {
          this.moreEmpty(response);
          return;
        }

        this.model.set('items', this.model.get('items').concat(response.items));

        const collection = new Backbone.Collection(response.items);
        const container = document.createDocumentFragment();

        this.collection.add(collection.models);

        collection.each((model) => {
          container.appendChild(this.renderItem(model));
        }, this);

        this.$el.find('.simplechart-items').append(container);
      }

      jQuery('#simplechart-loadmore').attr('disabled', false).show();
      this.model.set('max_id', response.meta.max_id);

      this.trigger('loaded loaded:success', response);
    },

    fetchedEmpty(response) {
      this
        .$el
        .find('.simplechart-empty')
        .text('No charts matched your search query.')
        .show();
      this.$el.find('.simplechart-pagination').hide();

      this.trigger('loaded loaded:noresults', response);
    },

    fetchedError(response) {
      this.$el.find('.simplechart-error').text(response.error_message).show();
      jQuery('#simplechart-loadmore').attr('disabled', false).show();
      this.trigger('loaded loaded:error', response);
    },

    moreEmpty(response) {
      this.$el.find('.simplechart-pagination').hide();

      this.trigger('loaded loaded:noresults', response);
    },

    updateInput(event) {
      // triggered when a search is submitted

      const params = this.model.get('params');
      this
        .$el
        .find('.simplechart-toolbar')
        .find(':input')
        .each(function setParams() {
          const n = jQuery(this).attr('name');
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

    paginate(event) {
      if (0 === this.collection.length) {
        return;
      }

      const page = this.model.get('page') || 1;

      this.model.set('page', page + 1);
      this.trigger('change:page');

      event.preventDefault();
    },

    changedPage() {
      // triggered when the pagination is changed
      this.fetchItems();
    },

    changedParams() {
      // triggered when the search parameters are changed
      this.model.set('page', null);
      this.model.set('min_id', null);
      this.model.set('max_id', null);

      this.clearItems();
      this.fetchItems();
    },
  });

  return SimplechartView;
}
