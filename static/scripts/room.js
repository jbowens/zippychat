var zc = zc || {};

/**
 * Represents a chat room of Zippy Chat.
 *
 * @author jbowens
 * @since 2012-08-27
 */
zc.Room = zc.Room || {

    roomid: null,
    
    chatSessions: [],

    messages: [],

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

    getRoomId: function() { return this.roomid; },

    /**
     * Initiates a request for the latest data from the
     * server.
     */
    refreshData: function()
    {
        var _this = this;
        $.get('/ping', { r: this.getRoomId() }, function(data) {
            _this.processData( data );
        });
    },

    /**
     * Posts a message to the room from the current session.
     *
     * @param msg  the message to post
     */
    postMessage: function(msg)
    {
        $.post('/post-message', { r: this.getRoomId(), msg: msg }, function(data) {
            // TODO: Respond to the server correctly   
        });
    },

    /**
     * Updates the object with the latest ping data.
     *
     * @param pingData  the data returned by the ping
     */
    processData: function( pingData )
    {
        console.log( pingData );
    }

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
                e.preventDefault();
                zc.pages.room.showChangeUsernameDialog();
            });
            $("#room .inviteOthers").click(function(e) {
                e.preventDefault();
                zc.pages.room.showInviteOthersDialog();
            });
            $("#room #postMessage_submit").click(function(e) {
                e.preventDefault();
                zc.pages.room.postMessage();
            });
            $("#room #postMessage_text").keypress(function(e) {
                // Enter key was pressed
                try {
                    if( e.keyCode == 13 )
                    {
                        e.preventDefault();
                        zc.pages.room.postMessage();
                    }
                } catch(err) {
                    esprit.recordError(err);
                }
            });

            // TODO: initialize chat session

            this.activeRoom.refreshData();
        }
        catch(err)
        {
            esprit.recordError(err);
        }
    },

    /**
     * Posts the currently typed message to the server.
     */
    postMessage: function()
    {
        try {
            var currentMessage = $("#room #postMessage_text").val();
            $("#room #postMessage_text").val("");
            this.activeRoom.postMessage(currentMessage);
        } catch(err)
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
