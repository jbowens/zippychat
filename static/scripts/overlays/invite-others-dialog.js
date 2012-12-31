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
        room: null,
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
         
            // Set the room
            this.room = ops.room;
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
            $(this.elmt).find(".toEmail").val('someone@example.com');
            $(this.elmt).find(".toEmail").addClass('uninitialized');
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
                var defaultMessage = "Hey, join me in this chat room.";
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
                $(this.emailTab).find(".toEmail").focus(function(e) {
                    $(e.target).val('');
                    $(e.target).removeClass('uninitialized');
                });
                $(this.emailTab).find(".toEmail").blur(function(e) {
                    if( $(e.target).val() == '' || $(e.target).val() == 'someone@example.com' ) {
                        $(e.target).addClass('uninitialized');
                        $(e.target).val('someone@example.com');
                    }
                });
                var overlay = this;
                var emailTab = this.emailTab;
                $(this.emailTab).find("form.emailInvites").submit(function(e) {
                    e.preventDefault();
                    $(this.emailTab).find(".submit").val("Sending...");
                    $(this.emailTab).find(".cancel").hide();
                    $.post('/email-room-invite', { r: overlay.room.getRoomId(),
                                                  to: $(emailTab).find(".toEmail").val(),
                                             message: $(emailTab).find("#emailInvitations_message").val() }, function(resp) {
                        // TODO: Process the server response... Probably only want to do
                        // anything if sending the email failed for some reason
                        $(this.emailTab).find(".submit").val("Send");
                        $(this.emailTab).find(".cancel").show();
                        overlay.hide();
                    });
                });
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
