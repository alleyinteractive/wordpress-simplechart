wp.media.view.SimplechartItem = wp.Backbone.View.extend({
    tagName   : 'li',
    className : 'sc-item attachment',

    render: function() {
    	this.template = wp.media.template(`simplechart-item-all`);
       	this.$el.html(this.template(this.model.toJSON()));

        return this;
    }
});