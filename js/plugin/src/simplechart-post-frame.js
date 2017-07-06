// Post Frame
export default function () {
  const postFrame = wp.media.view.MediaFrame.Post;

  const SimplechartPostFrame = postFrame.extend({
    initialize(...args) {
      postFrame.prototype.initialize.apply(this, args);

      const id = 'simplechart';
      const controller = {
        id,
        toolbar: `${id}-toolbar`,
        menu: 'default',
        title: 'Insert Chart',
        priority: 100,
        content: 'simplechart-content-all',
      };

      this.on(
        'content:render:simplechart-content-all',
        this.simplechartContentRender,
        this
      );

      this.states.add([
        new wp.media.controller.Simplechart(controller),
      ]);

      this.on(
        'toolbar:create:simplechart-toolbar',
        this.simplechartToolbarCreate,
        this
      );
    },

    simplechartContentRender() {
      this.content.set(new wp.media.view.Simplechart({
        controller: this,
        model: this.state().props.get('all'),
        className: 'clearfix attachments-browser simplechart-all',
      }));
    },

    simplechartToolbarCreate(toolbar) {
      // eslint-disable-next-line no-param-reassign
      toolbar.view = new wp.media.view.Toolbar.Simplechart({
        controller: this,
      });
    },
  });

  return SimplechartPostFrame;
}
