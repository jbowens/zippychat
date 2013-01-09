var zc = zc || {};
zc.pages = zc.pages || {};

/* Called on DOM ready */
zc.init = function() {
    if( $("#HOME") ) {
        zc.pages.home.init();
    }
    
    // Determine if flash is available, if required.
    if( typeof zc_run_flash_check !== 'undefined' && zc_run_flash_check )
    {
        zc.flashCheck();
    }
};

zc.pages.home = {
    init: function()
    {
        var titleField = $("#chat_room_title");
        titleField.addClass('uninitialized');
        titleField.data('uninitializedText', titleField.val());
        titleField.focus(zc.pages.home.initTitleField);
        titleField.blur(zc.pages.home.uninitTitleField);

        var expandOptions = $(".expand-additional-options");
        expandOptions.click(zc.pages.home.showAdditionalOptions);
    },
    initTitleField: function(e)
    {
        if( $(this).hasClass('uninitialized') ) {
            $(this).removeClass('uninitialized');
            $(this).val('');
        }
    },
    uninitTitleField: function(e)
    {
        if( ! $(this).hasClass('uninitialized') && $(this).val() == '' ) {
            $(this).addClass('uninitialized');
            $(this).val( $(this).data('uninitializedText') );
        }
    },
    showAdditionalOptions: function(e)
    {
        $(this).closest(".expand-opts-cont").detach();
        $(".additional-options").removeClass('hidden');
    }
};

/**
 * Determines if flash is enabled in the user's browser.
 */
zc.flashCheck = function() {
    // Make sure swfobject exists
    if( typeof swfobject === 'undefined' ) {
        esprit.recordError( new Error("swfobject is undefined, but zc_run_flash_check is enabled.") );
    } else
    {
        // Perform a flash check
        var flashActive = swfobject.getFlashPlayerVersion().major !== 0;
        $.post('/flash-check', {
            flash: flashActive
        });
    }
}


$(document).ready(function(e) {
    zc.init();
});
