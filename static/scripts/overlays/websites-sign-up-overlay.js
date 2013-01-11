var zc = zc || {};

/**
 * The /for-websites Sign up/install overlay.
 *
 * @author jbowens
 * @since 2013-01-05
 */
Class('zc.overlays.WebsitesSignUpOverlay', {
    isa: zc.overlays.SimpleDialog,

    have: {
        backdrop: null
    },

    before: {
        initialize: function(ops)
        {
            var extraClasses = ops.extraClasses || [];
            extraClasses.push('websitesSignUpOverlay');
            ops['extraClasses'] = extraClasses;
            this.extraClasses = extraClasses;
            this.width = ops.width || 500;

            // Setup the backdrop
            this.backdrop = new zc.overlays.Backdrop({ backdropOpacity: .80 });
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

            $(this.elmt).append($("<div class=\"websitesSignUpOverlayBody\">" +
                                     "<h3>Setup a chat room</h3>" +
                                     "</div>"));

        },

        hide: function()
        {
            this.backdrop.hide();
        }
    }

});
