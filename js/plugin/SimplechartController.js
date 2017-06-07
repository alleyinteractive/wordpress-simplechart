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