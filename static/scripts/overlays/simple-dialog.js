var zc = zc || {};

/**
 * A simple centered dialog.
 *
 * @author jbowens
 * @since 2012-08-30
 */
Class('zc.overlays.SimpleDialog', {
    isa: zc.overlays.Overlay,

    before: {
        initialize: function(ops) {
            this.top = 250;
            this.left = "50%";
        }
    },

    after: {
        initialize: function(ops) {
            $(this.elmt).css('margin-left', '-' + parseInt(this.width/2) + 'px');
        }
    }

});
