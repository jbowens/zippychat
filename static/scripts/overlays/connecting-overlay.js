var zc = zc || {};

/**
 * The 'Connecting...' overlay / dialog.
 *
 * @author jbowens
 * @since 2013-01-05
 */
Class('zc.overlays.ConnectingOverlay', {
    isa: zc.overlays.SimpleDialog,

    have: {
        backdrop: null
    },

    before: {
        initialize: function(ops)
        {
            var extraClasses = ops.extraClasses || [];
            extraClasses.push('connectingOverlay');
            ops['extraClasses'] = extraClasses;
            this.extraClasses = extraClasses;
            this.width = ops.width || 400;

            // Setup the backdrop
            this.backdrop = new zc.overlays.Backdrop({ backdropOpacity: .50 });
        },

        show: function()
        {
            this.backdrop.show();
        }
    },

    after: {
        initialize: function(ops)
        {
            // Setup the base html

            $(this.elmt).append($("<div class=\"connectingOverlayBody\">" +
                                     "<h3>Connecting…</h3>" +
                                     "</div>"));

        },

        hide: function()
        {
            this.backdrop.hide();
        }
    }

});
