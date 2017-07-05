// Item

const SimplechartItem = wp.Backbone.View.extend({
  tagName: 'li',
  className: 'simplechart-item attachment',

  render() {
    this.template = wp.media.template('simplechart-insert-item-all');
    this.$el.html(this.template(this.model.toJSON()));

    return this;
  },
});

export default SimplechartItem;
