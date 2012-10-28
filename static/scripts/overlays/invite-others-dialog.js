var zc = zc || {};

/**
 * The 'Invite Others' dialog box.
 *
 * @author jbowens
 * @since 2012-09-10
 */
Class('zc.overlays.InviteOthersDialog', {
    isa: zc.overlays.SimpleDialog,

    have: {
        tabContainer: null,
        emailTab: null,
        backdrop: null
    },

    before: {
        initialize: function(ops)
        {
            var extraClasses = ops.extraClasses || [];
            extraClasses.push('content');
            extraClasses.push('inviteOthersDialog');
            extraClasses.push('boxShadow');
            ops['extraClasses'] = extraClasses;
            this.extraClasses = extraClasses;
            this.width = ops.width || 400;

            // Setup the backdrop
            this.backdrop = new zc.overlays.Backdrop({ backdropOpacity: .30 });
            
        }
    },

    after: {
        initialize: function(ops)
        {
            // Setup the base html

            $(this.elmt).append($("<div class=\"overlayHeader\">" +
                                     "<h3>Invite someone</h3>" +
                                     "</div>"));
            this.tabContainer = $("<div class=\"tabContainer\"></div>");
            $(this.elmt).append(this.tabContainer);

            this.showEmailTab();

        },

        show: function() 
        {
            this.backdrop.show();
        },

        hide: function()
        {
            this.backdrop.hide();
        }
    },

    methods: {

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
        }
    }

});