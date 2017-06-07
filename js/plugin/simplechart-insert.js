wp.media.controller.Simplechart = wp.media.controller.State.extend({

  initialize: function() {

    this.props = new Backbone.Collection();

    this.props.add(new Backbone.Model({
      id     : 'all',
      params : {},
      page   : null,
      min_id : null,
      max_id : null,
      fetchOnRender : true,
    }));

    this.props.add( new Backbone.Model({
      id        : '_all',
      selection : new Backbone.Collection()
    }));

    this.props.on('change:selection', this.refresh, this);

  },

  refresh: function() {
    this.frame.toolbar.get().refresh();
  },

  /**
   * Replicate MEXP function except with shortcodes intead of URLs.
   */
  doInsert: function() {
    const selection = this.frame.content.get().getSelection();
    let shortcodes = [];

    selection.each( (model) => {
      shortcodes.push(`[simplechart id="${model.get('id')}"]`);
    }, this);

    if ('undefined' === typeof(tinymce) || null === tinymce.activeEditor || tinymce.activeEditor.isHidden()) {
      wp.media.editor.insert(_.toArray(shortcodes).join("\n\n"));
    } else {
      wp.media.editor.insert(`<p>${_.toArray(shortcodes).join("</p><p>")}</p>`);
    }

    selection.reset();
    this.frame.close();
  }

});

const postFrame = wp.media.view.MediaFrame.Post;

wp.media.view.MediaFrame.Post = postFrame.extend({

  initialize: function() {
    postFrame.prototype.initialize.apply(this, arguments);

    const id = 'simplechart';
    const controller = {
      id: id,
      toolbar: `${id}-toolbar`,
      menu: 'default',
      title: 'Insert Chart TEST TEST',
      priority: 100,
      content: 'simplechart-content-all',
    };

    // this.on('content:render:simplechart-content-all', _.bind(this.contentRender, this));

    this.states.add([
      new wp.media.controller.Simplechart(controller)
    ]);

    this.on('toolbar:create:simplechart-toolbar', this.simplechartToolbarCreate, this);
  },

  // contentRender: function() {
  //   this.content.set(new wp.media.view.Simplechart({

  //   }));
  // },

  simplechartToolbarCreate: function(toolbar) {
    toolbar.view = new media.view.Toolbar.Simplechart({
      controller: this,
    });
  }

});

const {
  initialize: parentInitialize,
  refresh: parentRefresh,
} = wp.media.view.Toolbar.prototype;

wp.media.view.Toolbar.Simplechart = wp.media.view.Toolbar.extend({

  initialize: function() {
    console.info('init');

    _.defaults(this.options, {
        event : 'inserter',
        close : false,
      items : {
          // See wp.media.view.Button
          inserter     : {
              id       : 'sc-button',
              style    : 'primary',
              text     : 'Insert into post TEST',
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

    parentRefresh.apply(this, arguments);

  }

});