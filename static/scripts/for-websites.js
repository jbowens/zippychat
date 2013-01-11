var zc = zc || {};

/**
 * Encapsulates all /for-websites/* logic.
 */
zc.pages.forwebsites = zc.pages.forwebsites || {

    signUpOverlay: null,

    /**
     * Called on DOM ready.
     */
    init: function() {
        try
        {
            if( $(".choosePlan").length )
            {
                var signUpOverlay = new zc.overlays.WebsitesSignUpOverlay({displayImmediately: false});
                this.signUpOverlay = signUpOverlay;
                $(".choosePlan").click(function(e)
                    {
                        signUpOverlay.show();
                    });
            }
        }
        catch(err)
        {
            esprit.recordError(err);
        }
    }

};

$(document).ready(function(e) {
    zc.pages.forwebsites.init();
});
