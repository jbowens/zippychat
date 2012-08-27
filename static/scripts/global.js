var zc = zc || {};
zc.pages = zc.pages || {};

/* Called on DOM ready */
zc.init = function() {
    if( $("#HOME") ) {
        zc.pages.home.init();
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


$(document).ready(function(e) {
    zc.init();
});
