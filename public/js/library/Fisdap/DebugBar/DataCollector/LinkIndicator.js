/**
 * Created by jmortenson on 11/12/14.
 */
var LinkIndicator = PhpDebugBar.DebugBar.Indicator.extend({

    tagName: 'a',

    render: function() {
        LinkIndicator.__super__.render.apply(this);
        this.bindAttr('href', function(href) {
            this.$el.attr('href', href);
        });
        this.$el.attr('target', '_blank');
    }

});