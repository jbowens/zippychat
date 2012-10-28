var zc = zc || {};

/**
 * A simple centered dialog.
 *
 * @author jbowens
 * @since 2012-08-30
 */
Class('zc.overlays.Backdrop', {
    isa: zc.overlays.Overlay,

    have: {
        backdropColor: "#000",
        backdropOpacity: 0.5
    },

    before: {
        initialize: function(ops) {
            this.left = 0;
            this.top = 0;
        }
    },

    after: {
        /**
         * Initializes a new backdrop
         *
         * ops : an object of options:
         *  @param backdropColor  the color of the backdrop
         *  @param backdropOpacity  the opacity between 0 and 1 inclusive
         *  [Also accepts any options that zc.overlays accepts]
         */
        initialize: function(ops) {
            if( ! this.zindexCalculator )
            {
                this.zindexCalculator = function() {
                    return 4000;
                };
            }

            this.left = 0;
            this.top = 0;

            $(this.elmt).addClass('backdrop');
            $(this.elmt).css('background-color', this.backdropColor);
            $(this.elmt).css('zoom', 1);
            $(this.elmt).css('filter', 'alpha(opacity=' + parseInt(this.backdropOpacity*100) + ')');
            $(this.elmt).css('opacity', this.backdropOpacity);
        }
    }

});
