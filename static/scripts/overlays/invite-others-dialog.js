var zc = zc || {};

/**
 * We can only do shit if we have the rest of the overlay namespace!
 */
if( ! zc.overlays || ! zc.overlays.Overlay )
{
    try {
        esprit.recordError( new Error("no zc.overlays || zc.overlays.Overlay") );
    } catch(err)
    { /* fuck. */ }
}

/**
 * The 'Invite Others' dialog box.
 *
 * @author jbowens
 * @since 2012-09-10
 */
zc.overlays.InviteOthersDialog = zc.overlays.InviteOthersDialog || {

    tabContainer: null,
    emailTab: null,
    backdrop: null,

    construct: function(ops) {

        ops['extraClasses'] = ops['extraClasses'] || [];
        ops['extraClasses'].push('content');
        ops['extraClasses'].push('inviteOthersDialog');
        ops['extraClasses'].push('boxShadow');

        var overlay = zc.overlays.SimpleDialog.construct(500, ops);
        overlay = esprit.oop.extend(zc.overlays.InviteOthersDialog, overlay);

        // Setup the base html

        $(overlay.elmt).append($("<div class=\"overlayHeader\">" +
                                 "<h3>Invite someone</h3>" +
                                 "</div>"));
        overlay.tabContainer = $("<div class=\"tabContainer\"></div>");
        $(overlay.elmt).append(overlay.tabContainer);

        // Setup the backdrop
        overlay.backdrop = zc.overlays.Backdrop.construct({ backdropOpacity: .30 });

        overlay.showEmailTab();
        
        return overlay;
    },

    /**
     * Sets the content of the dialog to be the email tab with the form for
     * sending chat room invitations through the mail.
     */
    showEmailTab: function()
    {
        // We might need to create the html if we haven't rendered this tab yet.
        if( ! this.emailTab )
        {
            // TODO: Add in translation strings
            var defaultMessage = "Hey yo, join this chat room! It's real cool.";
            this.emailTab = $("<div class=\"emailTab\">" +
                              "<form class=\"emailInvites\">" +
                              "<div class=\"toField field\">" +
                              "<label>To</label>" +
                              "<div class=\"toFieldsContainer\">" +
                              "<input type=\"text\" class=\"toEmail\" name=\"to\" />" +
                              "<div class=\"clear\"></div>" +
                              "</div>" +
                              "</div>" +
                              "<div class=\"messageField field\">" +
                              "<label>Message</label> <textarea id=\"emailInvitations_message\" class=\"textarea\"></textarea>" +
                              "<div class=\"clear\"></div>" +
                              "</div>" + 
                              "<div><input type=\"submit\" class=\"submit pop utilPop button\" id=\"emailInvitations_submit\" value=\"Send\" /><input type=\"button\" class=\"button option optionButton utilOptionButton cancel\" value=\"Cancel\" /></div>" +
                              "</form>" +
                              "</div>");
            $(this.emailTab).find("#emailInvitations_message").val(defaultMessage);
            var overlay = this;
            $(this.emailTab).find(".cancel").click(function(e) {
                e.preventDefault();
                overlay.hide();
            });
        }

        // Kill any existing tabs and adopt this tab
        $(this.tabContainer).empty();
        $(this.tabContainer).append( this.emailTab );
    },

    show: function() 
    {
        this.backdrop.show();
        this.uber.show();
    },

    hide: function()
    {
        this.backdrop.hide();
        this.uber.hide();
    }

}
