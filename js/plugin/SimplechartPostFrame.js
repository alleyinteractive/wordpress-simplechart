const postFrame = wp.media.view.MediaFrame.Post;

wp.media.view.MediaFrame.Post = postFrame.extend({

  initialize: function() {
    postFrame.prototype.initialize.apply(this, arguments);

    const id = 'simplechart';
    const controller = {
      id: id,
      toolbar: `${id}-toolbar`,
      menu: 'default',
      title: 'Insert Chart - Simplechart',
      priority: 100,
      content: 'simplechart-content-all',
    };

    this.on('content:render:simplechart-content-all', _.bind(this.contentRender, this));

    this.states.add([
      new wp.media.controller.Simplechart(controller)
    ]);

    this.on('toolbar:create:simplechart-toolbar', this.simplechartToolbarCreate, this);
  },

  contentRender: function() {
    this.content.set(new wp.media.view.Simplechart({
      service    : 'simplechart',
      controller : this,
      model      : this.state().props.get('all'),
      tab        : 'all',
      className  : 'clearfix attachments-browser simplechart-all'
    }));
  },

  simplechartToolbarCreate: function(toolbar) {
    toolbar.view = new media.view.Toolbar.Simplechart({
      controller: this,
    });
  }

});