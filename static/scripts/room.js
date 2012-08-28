var zc = zc || {};

/**
 * Represents a message in a chat room.
 *
 * @author jbowens
 * @since 2012-08-27
 */
zc.Message = zc.Message || {

    messageId: null,
    username: null,
    timestamp: null,
    content: null,
    elem: null,
    serverConfirmed: false,

    construct: function(ops)
    {
        return esprit.oop.extend(zc.Message, ops);
    },

    getMessageId: function()
    {
        return this.messageId;
    },

    getUsername: function()
    {
        return this.username;
    },

    getDateSent: function()
    {
        return this.timestamp;
    },

    getMessage: function()
    {
        return this.content;
    },

    getElement: function()
    {
        return this.elem;
    },

    isServerConfirmed: function()
    {
        return this.serverConfirmed;
    },

    setMessageId: function(newId)
    {
        this.messageId = newId;
    },

    setElement: function(elem)
    {
        this.elem = elem;
    }

};

/**
 * Represents a chat session.
 *
 * @author jbowens
 * @since 2012-08-27
 */
zc.ChatSession = zc.ChatSession || {

    chatSessionId: null,
    username: null,
    loginTime: null,

    construct: function(ops) {
        return esprit.oop.extend(zc.ChatSession, ops);
    },

    getUsername: function() {
        return this.username;
    },

    getLoginTime: function() {
        return this.loginTime;
    },

},

/**
 * Represents a chat room.
 *
 * @author jbowens
 * @since 2012-08-27
 */
zc.Room = zc.Room || {

    roomId: null,
    chatSessions: [],
    messages: [],
    lastMessageId: null,
    initialUpdateTime: null,
    render: null,

    /**
     * Constructs a new Room object.
     *
     * @param roomid  the if of the room
     * @return an zc.Room object
     */
    construct: function(roomId)
    {
        return esprit.oop.extend(zc.Room, { 'roomId': roomId });
    },

    /**
     * Gets the id of this room.
     */
    getRoomId: function() { return this.roomId; },

    /**
     * Gets the messages of this room.
     */
    getMessages: function()
    {
        return this.messages;
    },

    setRender: function(renderFunc)
    {
        this.render = renderFunc;
    },

    /**
     * Initiates a request for the latest data from the
     * server.
     */
    refreshData: function()
    {
        var _this = this;
        var requestData = { r: this.getRoomId() };
        if( this.lastMessageId != null )
        {
           requestData['lastMsgId'] = this.lastMessageId; 
        }
        else
        {
            if( this.initialUpdateTime == null )
            {
                esprit.reportError( { 'name': 'no initial update time', 
                                      'message': 'No last message id or initial update time provided' });
                return;
            }
            requestData['fromTime'] = parseInt(this.initialUpdateTime.getTime()/1000);
        }

        $.get('/ping', requestData, function(data) {
            _this.processData( data );
        }, 'json');
    },

    /**
     * Posts a message to the room from the current session.
     *
     * @param chatSession  the chat session sending the message
     * @param msg  the message to post
     */
    postMessage: function(chatSession, msg)
    {
        var msgObj = zc.Message.construct( {
            content: msg,
            username: chatSession.getUsername(),
            dateSent: parseInt((new Date).getTime()/1000)
        });

        $.post('/post-message', { r: this.getRoomId(), msg: msg }, function(data) {
            // TODO: Respond to the server correctly including
            //       responding to error codes
            msgObj.setMessageId( data.messageId );
            this.lastMessageId = Math.max( this.lastMessageId, data.messageId );
        }, 'json');
        
        this.messages.push( msgObj );
        
        if( this.render )
        {
            this.render();
        }
    },

    /**
     * Updates the object with the latest ping data.
     *
     * @param pingData  the data returned by the ping
     */
    processData: function( pingData )
    {
        var messages = pingData.messages;
     
        var highestId = messages[0].messageId;
        for( msg in messages )
        {
            msg.serverConfirmed = true;
            var msgObj = zc.Message.construct(msg);
            var exists = false;
            for( existingMsg in this.messages )
            {
                // TODO: Take care of the race condition if we haven't heard back yet about a submitted
                // message's message id
                if( existingMsg.getMessageId() == msgObj.getMessageId() )
                {
                    exists = true;
                }
            }
            if( !  exists )
            {
                this.messages.push( msgObj );
                highestId = Math.max( highestId, msg.messageId );
            }
        }
        
        this.lastMessageId = Math.max( highestId, this.lastMessageId );

        if( this.render )
        {
            this.render();
        }
    },

    /**
     * Sets the initial update time.
     */
    setInitialTime: function( initialTime )
    {
        this.initialUpdateTime = initialTime;
    }

};

/**
 * Encapsulates all /room logic.
 */
zc.pages.room = zc.pages.room || {

    activeChatSession: null,
    activeRoom: null,
    initialTime: null,
    pingInterval: 10000,
    pingTimeoutHandle: null,

    /**
     * Called on DOM ready.
     */
    init: function() {
        try
        {
            this.initialTime = new Date();

            // Setup the room
            var roomId = parseInt( $("#roomId").val() );
            var room = zc.Room.construct( roomId );
            room.setRender( zc.pages.room.render );
            this.activeRoom = room;

            // Set the initial update time
            room.setInitialTime( this.initialTime );

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

            this.initializeSession();
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
            if( zc.pages.room.activeChatSession == null )
            {
                // TODO: update this line
                throw new Error();
            }
            var currentMessage = $("#room #postMessage_text").val();
            $("#room #postMessage_text").val("");
            zc.pages.room.activeRoom.postMessage(zc.pages.room.activeChatSession, currentMessage);
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
    },

    /**
     * Contacts the server to get a chat session.
     */
    initializeSession: function()
    {
        try {
            var _this = this;
            var data = {
                r: this.activeRoom.getRoomId()
            };
            $.get('/initialize-session', data, function(data) {
                if( data != null && data['status'] == "ok" )
                {
                    _this.activeChatSession = zc.ChatSession.construct(data.chatSession);
                    _this.activeRoom.setInitialTime( new Date( data.chatSession.loginTime * 1000 ) );
                    zc.pages.room.ping();
                }
                else
                {
                    // Try again soon
                    setTimeout(zc.pages.room.initializeSession, 3000);
                }
            }, 'json');
        } catch(err)
        {
            esprit.recordError(err);
        }
    },

    /**
     * Pings the server for fresh data.
     */
    ping: function()
    {
        try {
            zc.pages.room.activeRoom.refreshData();
            zc.pages.room.pingTimeoutHandle = setTimeout(zc.pages.room.ping, zc.pages.room.pingInterval);
        } catch(err) {
            esprit.recordError( err );
        }
    },

    /**
     * Renders the active room on the page.
     */
    render: function()
    {
        // "this" refers to the calling room object.
        var msgs = this.getMessages();

        for( var key in msgs )
        {
            var message = msgs[key];
            if( message.getElement() == null )
            {
                // This message hasn't been rendered yet.
                var msgElem = $('<li class="message"><span class="username"></span>: <span class="messageContent"></span></li>');
                msgElem.find(".username").text( message.getUsername() );
                msgElem.find(".messageContent").text( message.getMessage() );
                message.setElement( msgElem );
                $("#messages").append( msgElem );
            }
        }
    }

};

$(document).ready(function(e) {
    zc.pages.room.init();
});
