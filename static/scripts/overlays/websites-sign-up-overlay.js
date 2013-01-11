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
            this.width = ops.width || 550;

            // Setup the backdrop
            this.backdrop = new zc.overlays.Backdrop({ backdropOpacity: .85, displayImmediately: false });
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

            var _this = this;
            $(this.elmt).append($("<div class=\"websitesSignUpOverlayBody\">" +
                                     "<h3>Set up a chat room</h3>" +
                                     "<ul id=\"signUpForm\">" +
                                        "<li><input type=\"text\" name=\"website_url\" placeholder=\"website url\" class=\"text\" /></li>" +
                                        "<li><input type=\"text\" name=\"email\" placeholder=\"email\" class=\"text email\" /></li>" +
                                     "</ul>" +
                                     "<div><input type=\"submit\" class=\"submit pop\" value=\"Set up\" /> <input type=\"button\" class=\"optionButton cancel\" value=\"Cancel\" /></div>" +
                                     "</div>"));
            $(this.elmt).find(".cancel").click(function(e)
                    {
                        _this.hide();
                    });

        },

        hide: function()
        {
            this.backdrop.hide();
        }
    }

});
