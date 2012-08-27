var zc = zc || {};

/**
 * Represents a chat room of Zippy Chat.
 *
 * @author jbowens
 * @since 2012-08-27
 */
zc.Room = zc.Room || {

    roomid: null,

    /**
     * Constructs a new Room object.
     *
     * @param roomid  the if of the room
     * @return an zc.Room object
     */
    construct: function(roomid)
    {
        return esprit.oop.extend(zc.Room, { 'roomid': roomid });
    },

    getRoomId: function() { return this.roomid; }

};

/**
 * Encapsulates all /room logic.
 */
zc.pages.room = zc.pages.room || {

    /**
     * The room currently being served
     */
    activeRoom: null,

    /**
     * Called on DOM ready.
     */
    init: function() {
        try
        {
            // Setup the room
            var roomId = parseInt( $("#roomId").val() );
            var room = zc.Room.construct( roomId );
            this.activeRoom = room;

            // Setup listeners
            $("#room .changeUsername").click(function(e) {
                zc.pages.room.showChangeUsernameDialog();
            });
            $("#room .inviteOthers").click(function(e) {
                zc.pages.room.showInviteOthersDialog();
            });
        }
        catch(err)
        {
            esprit.recordError(err);
        }
    },

    showChangeUsernameDialog: function()
    {
        // TODO: Implement
    },

    hideChangeUsernameDialog: function()
    {
        // TODO: Implement
    },

    showInviteOthersDialog: function()
    {
        // TODO: Implement
    },

    hideInviteOthersDialog: function()
    {
        // TODO: Implement
    }

};

$(document).ready(function(e) {
    zc.pages.room.init();
});
