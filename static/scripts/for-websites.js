var zc = zc || {};

/**
 * Encapsulates all /for-websites/* logic.
 */
zc.pages.forwebsites = zc.pages.forwebsites || {

    /**
     * Called on DOM ready.
     */
    init: function() {
        try
        {
            
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
