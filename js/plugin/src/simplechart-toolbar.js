// Toolbar
export default function () {
  const {
    initialize: parentInitialize,
  } = wp.media.view.Toolbar.prototype;

  const SimplechartToolbar = wp.media.view.Toolbar.extend({
    initialize(...args) {
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
            click: () => {
              this.controller.state().doInsert();
            },
          },
        },
      });

      parentInitialize.apply(this, args);

      this.set('pagination', new wp.media.view.Button({
        tagName: 'button',
        classes: 'simplechart-pagination button button-secondary',
        id: 'simplechart-loadmore',
        text: 'Load More',
        priority: - 20,
      }));
    },

    refresh(...args) {
      const selection = this
        .controller
        .state()
        .props
        .get('_all')
        .get('selection');

      // @TODO i think this is redundant
      this.get('inserter').model.set('disabled', ! selection.length);

      wp.media.view.Toolbar.prototype.refresh.apply(this, args);
    },
  });

  return SimplechartToolbar;
}
