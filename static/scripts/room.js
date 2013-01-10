var zc = zc || {};

/**
 * Represents a message in a chat room.
 *
 * @author jbowens
 * @since 2012-08-27
 */
Class('zc.Message', {

    have: {
        messageId: null,
        username: null,
        timestamp: null,
        content: null,
        elem: null,
        serverConfirmed: false,
        colorFunc: null,
        isSystemMsg: false
    },

    methods: {
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

        isSystemMessage: function()
        {
            return this.isSystemMsg;
        },

        setColorFunction: function(colorFunc)
        {
            this.colorFunc = colorFunc;
        },

        setMessageId: function(newId)
        {
            this.messageId = newId;
        },

        setElement: function(elem)
        {
            this.elem = elem;
        },

        getColor: function()
        {
            if( this.colorFunc ) {
                return this.colorFunc();
            } else {
                return "000000";
            }
        },

        setServerConfirmed: function()
        {
            this.serverConfirmed = true;
        }
    }

});

/**
 * Represents a chat session.
 *
 * @author jbowens
 * @since 2012-08-27
 */
Class("zc.ChatSession", {

    have: {
        chatSessionId: null,
        username: null,
        loginTime: null,
        colorFunc: null,
        elem: null
    },

    methods: {
        getChatSessionId: function() {
            return this.chatSessionId;
        },

        getUsername: function() {
            return this.username;
        },

        getLoginTime: function() {
            return this.loginTime;
        },

        getElement: function() {
            return this.elem;
        },

        setColorFunction: function(colorFunc) {
            this.colorFunc = colorFunc;
        },

        setElement: function(elem) {
            this.elem = elem;
        },

        getColor: function() {
            if( this.colorFunc == null ) {
                return "000000";
            }
            else {
                return this.colorFunc();
            }
        }
    }

});

/**
 * Represents a chat room.
 *
 * @author jbowens
 * @since 2012-08-27
 */
Class("zc.Room", {

    have: {
        roomId: null,
        chatSessions: [],
        messages: [],
        lastMessageId: Number.NEGATIVE_INFINITY,
        initialUpdateTime: null,
        render: null,
        lastUsernameChangeId: null,
        unupdatedSessions: []
    },

    methods: { 
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

        getChatSessions: function()
        {
            return this.chatSessions;
        },

        getUnupdatedSessions: function()
        {
            return this.unupdatedSessions;
        },

        setRender: function(renderFunc)
        {
            this.render = renderFunc;
        },

        /**
         * Initiates a request for the latest data from the
         * server.
         */
        refreshData: function(finishedCallback)
        {
            var _this = this;
            var requestData = { r: this.getRoomId(), changeId: this.lastUsernameChangeId };
            if( isFinite(this.lastMessageId) )
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
                try {
                    // If something terrible went down server side, we might have nonsense here.
                    if( data == null || typeof data != 'object' || ! data.hasOwnProperty('messages') || 
                        ! data.hasOwnProperty('activeUsers') || ! data.hasOwnProperty('usernameChanges') ) {
                        esprit.recordError(new Error("Instead of ping data, received " + data));
                    } else {
                        _this.processData( data );
                    }
                    // Call the callback alerting the caller that we're finished
                    finishedCallback();
                } catch(err) {
                    esprit.recordError(err);
                }
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
            var _this = this;
            var msgObj = new zc.Message( {
                content: msg,
                username: chatSession.getUsername(),
                dateSent: parseInt((new Date).getTime()/1000)
            });

            $.post('/post-message', { r: this.getRoomId(), msg: msg }, function(data) {
                // TODO: Respond to the server correctly including
                //       responding to error codes
                msgObj.setMessageId( data.messageId );
                msgObj.setServerConfirmed();
               
                if( _this.render ) {
                    _this.render();
                }

            }, 'json');
        
            // NOTE: THIS CAN RESULT IN MESSAGES OUT OF CHRONOLOGICAL ORDER. I THINK THAT'S OK, BUT
            // w/ TIMESTAMPS DISPLAYED IT MIGHT BE A POOR USER EXPERIENCE.
            // TODO: potentially address the above note ^... or not.
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
            try {
                // Sync the messages
                var messages = pingData.messages;
             
                var highestId = Number.NEGATIVE_INFINITY;
                for( var j = 0; j < messages.length; j++ )
                {
                    var msg = messages[j];
                    msg.serverConfirmed = true;
                    var msgObj = new zc.Message(msg);

                    // We only want to add this message if it doesn't already exist.
                    var exists = false;
                    for( var i = 0; i < this.messages.length; i++ )
                    {
                        var existingMsg = this.messages[i];

                        // TODO: Take care of the race condition if we haven't heard back yet about a submitted
                        // message's message id
                        if( existingMsg.getMessageId() == msgObj.getMessageId() )
                        {
                            exists = true;
                        }
                    }
                    
                    if( ! exists )
                    {
                        // Append the messages to the internal list of messages
                        this.messages.push( msgObj );
                    }

                    // Update the highest seen id
                    highestId = Math.max( highestId, msg.messageId );

                }
                this.lastMessageId = Math.max( highestId, this.lastMessageId );
               
                // Sync the current users
                var users = pingData.activeUsers;
                for( var i = 0; i < users.length; i++ )
                {
                    var user = users[i];

                    var exists = false;
                    for( var j = 0; j < this.chatSessions.length; j++ )
                    {
                        var existingSession = this.chatSessions[j];

                        if( existingSession.getChatSessionId() == user.chatSessionId )
                            exists = true;
                    }

                    if( ! exists ) {
                        var newObj = new zc.ChatSession( user );
                        this.chatSessions.push(newObj);
                    }
                }
                
                // Now remove any users that didn't appear in the ping data
                for( var i = 0; i < this.chatSessions.length; i++ )
                {
                    var existingSession = this.chatSessions[i];
                    var exists = false;
                    for( var j = 0; j < users.length; j++ )
                    {
                        if( existingSession.getChatSessionId() == users[j].chatSessionId )
                            exists = true;
                    }

                    // This user appears in our chat sessions but not in the server data...
                    // Delete it!
                    if( ! exists ) {
                        this.chatSessions.splice(i, 1);
                    }
                }

                // Handle username changes
                var usernameChanges = pingData.usernameChanges;
                for( var i = 0; i < usernameChanges.length; i++ )
                {
                    var change = usernameChanges[i];
                    
                    var oldUsername = null;
                    for( var j = 0; j < this.chatSessions.length; j++ )
                    {
                        var session = this.chatSessions[j];
                        if( (session.getChatSessionId() == change['chatSessionid']) && (session['username'] != change['newUsername']) ) {
                            oldUsername = session['username'];
                            session['username'] = change['newUsername'];
                            session['oldUsername'] = oldUsername;
                            this.unupdatedSessions.push(session);
                        }
                    }
                    
                    
                    // Update last username change id
                    this.lastUsernameChangeId = Math.max(this.lastUsernameChangeId, change['changeid']);    
                }

                // Re-render
                if( this.render )
                {
                    this.render();
                }
            } catch(err) {
                esprit.recordError(err);
            }
        },

        /**
         * Sets the initial update time.
         */
        setInitialTime: function( initialTime )
        {
            this.initialUpdateTime = initialTime;
        },

        setLastUsernameChangeId: function( newId )
        {
            if( this.lastUsernameChangeId == null )
                this.lastUsernameChangeId = newId;
        },

        createSystemMessage: function( message )
        {
            var ops = {
                content: message,
                isSystemMsg: true,
                serverConfirmed: true,
                dateSent: parseInt((new Date()).getTime()/1000)
            }
            var msgObj = new zc.Message( ops );
            this.messages.push( msgObj );
        }
    }
});

/**
 * Encapsulates all /room logic.
 */
zc.pages.room = zc.pages.room || {

    activeChatSession: null,
    activeRoom: null,
    initialTime: null,
    pingInterval: 3000,
    pingTimeoutHandle: null,
    currentPingStart: null,
    changeUsernameDialog: null,
    passwordBackdrop: null,
    passwordOverlay: null,
    inviteOthersOverlay: null,
    connectingOverlay: null,
    linker: null,

    /**
     * Called on DOM ready.
     */
    init: function() {
        try
        {
            this.initialTime = new Date();

            // Setup the room
            var roomId = parseInt( $("#roomId").val() );
            var room = new zc.Room( {roomId: roomId} );
            room.setRender( zc.pages.room.render );
            this.activeRoom = room;

            // Initialize the linker
            this.linker = new zc.Linker();

            // Setup viglink, if available
            if( typeof vglnk !== 'undefined' )
                vglnk.reaffiliate = true;

            // Set the initial update time
            room.setInitialTime( this.initialTime );

            // Display the connecting overlay
            this.connectingOverlay = new zc.overlays.ConnectingOverlay({});

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
                // TODO: Do something more graceful here
                throw new Error("A user tried to post a message while not logged in");
            }
            var currentMessage = $("#room #postMessage_text").val();
            // Not submitting if it's an empty message
            if( currentMessage == "" )
                return;

            $("#room #postMessage_text").val("");
            zc.pages.room.activeRoom.postMessage(zc.pages.room.activeChatSession, currentMessage);
        } catch(err)
        {
            esprit.recordError(err);
        }
    },

    showChangeUsernameDialog: function()
    {
        try {
            if( ! this.changeUsernameDialog )
            {
                this.changeUsernameDialog = new zc.overlays.SimpleDialog({ width: 300, extraClasses: ['content','boxShadow'] });
                // TODO: Localize the strings in this dialog!
                this.changeUsernameDialog.setHtml('<div class="changeUsernameDialog">' +
                                                  '<h3>Choose a username</h3>' +
                                                  '<p>What would you like your new username to be?</p>' +
                                                  '<form>' +
                                                  '<div><input type="text" class="text newUsername" name="username" maxlength="22" /></div>' +
                                                  '<input type="submit" class="pop smallerPop submit" value="Save" />' +
                                                  '<input type="button" class="cancel button optionButton" value="Cancel" />' + 
                                                  '</form>' +
                                                  '</div>');
                $(this.changeUsernameDialog.getElement()).find(".cancel").click(function(e) {
                    e.preventDefault();
                    zc.pages.room.hideChangeUsernameDialog();
                });
                var room = this.activeRoom;
                var usernameDialog = this.changeUsernameDialog;
                // TODO: Cap username length on both client and server side
                var changeUsernameFunc = function(e) {
                    try {
                        e.preventDefault();
                        var newUsername = $(usernameDialog.getElement()).find(".newUsername").val();
                        // Only update if it's a new username
                        if( zc.pages.room.activeChatSession.getUsername() != newUsername )
                            zc.pages.room.requestNewUsername( $(usernameDialog.getElement()).find(".newUsername").val() );
                        zc.pages.room.hideChangeUsernameDialog();
                    } catch(err) { esprit.recordError(err); }
                };

                $(this.changeUsernameDialog.getElement()).find(".submit").click(changeUsernameFunc);
                $(this.changeUsernameDialog.getElement()).find(".newUsername").keypress(function(e) {
                    // On pressing enter, change the username
                    if( e.keyCode == 13 )
                        changeUsernameFunc(e);
                });
            }

            // Populate the input box with the current username
            var newUsernameField = $(this.changeUsernameDialog.getElement()).find(".newUsername");
            newUsernameField.val(this.activeChatSession.getUsername());

            this.changeUsernameDialog.show();
            newUsernameField.focus();
            newUsernameField.select();

        } catch(err) {
            esprit.recordError(err);
        }
    },

    hideChangeUsernameDialog: function()
    {
        try {
            this.changeUsernameDialog.hide();
        } catch(err) {
            esprit.recordError(err);
        }
    },

    /**
     * Displays an overlay prompting the user for the password.
     */
    requestPassword: function(badPassword)
    {
        // TODO: Update this with translation strings
        try {
            if( ! this.passwordOverlay )
            {
                this.passwordBackdrop = new zc.overlays.Backdrop({backdropOpacity: 0.80});
                this.passwordBackdrop.show();
                this.passwordOverlay = new zc.overlays.SimpleDialog({ width: 350, extraClasses: ['passwordPrompt'] });
                this.passwordOverlay.setHtml('<form id="provideRoomPassword">' +
                                             '<h3>Please enter the password:</h3>' + 
                                             '<div id="passwordOverlayError"></div>' +
                                             '<div><input type="password" name="roomPass" id="roomPassword" /></div>' +
                                             '</div>');
                var passwordOverlay = this.passwordOverlay;
                $(this.passwordOverlay.elmt).find("#roomPassword").keypress(function(e) {
                if( e.keyCode == 13 )
                {
                    // If enter
                    e.preventDefault();
                    zc.pages.room.initializeSession( $(passwordOverlay.elmt).find("#roomPassword").val() );
                }
            });
            }
            this.passwordOverlay.show();
            var errorElmt = $(this.passwordOverlay.elmt).find("#passwordOverlayError");
            if( badPassword )
            {
                errorElmt.hide();
                errorElmt.text("The password was incorrect.");
                errorElmt.fadeIn('fast');
            } else
                errorElmt.hide();

            var passwordElmt = $($(this.passwordOverlay.elmt).find("#roomPassword"));
            passwordElmt.focus();
            passwordElmt.select();
        } catch(err) {
            esprit.recordError(err);
        }
    },

    hidePasswordDialog: function()
    {
        try {
            if( this.passwordOverlay )
                this.passwordOverlay.hide();
            if( this.passwordBackdrop )
                this.passwordBackdrop.hide();
        } catch(err) {
            esprit.recordError(err);
        }
    },

    showInviteOthersDialog: function()
    {
        try {
            if( ! this.inviteOthersOverlay )
            {
                this.inviteOthersOverlay = new zc.overlays.InviteOthersDialog({'room': this.activeRoom});
            }

            this.inviteOthersOverlay.show();

        } catch(err) {
            esprit.recordError(err);
        }
    },

    hideInviteOthersDialog: function()
    {
        try {
            if( this.inviteOthersOverlay )
                this.inviteOthersOverlay.hide();
        } catch(err) {
            esprit.recordError(err);
        }
    },

    /**
     * Contacts the server to get a chat session.
     */
    initializeSession: function(password)
    {
        try {
            var _this = this;
            var data = {
                r: this.activeRoom.getRoomId()
            };
            if( password )
            {
                data['password'] = password;
            }
            // TODO: Add logic to handle password-protected rooms
            $.post('/initialize-session', data, function(data) {
                if( data != null && data['status'] == "ok" )
                {
                    zc.pages.room.hidePasswordDialog();
                    _this.activeChatSession = new zc.ChatSession(data.chatSession);
                    _this.activeRoom.setInitialTime( new Date( data.chatSession.loginTime * 1000 ) );
                    _this.activeRoom.setLastUsernameChangeId( data.usernameChangeId );
                    _this.connectingOverlay.hide();
                    if( data['newSession'] )
                    {
                        // Show the username dialog so that they can set their initial username
                        zc.pages.room.showChangeUsernameDialog();
                    }
                    // Begin pinging for new data
                    zc.pages.room.ping();
                }
                else if( data != null && data['status'] == "unauthenticated" )
                {
                    // The user needs to provide a password
                    var badPassword = !!data['badPassword'];
                    zc.pages.room.requestPassword(badPassword);
                }
                else
                {
                    // TODO: Show error message
                    // Try again soon
                    setTimeout(zc.pages.room.initializeSession, 3000);
                }
            }, 'json');
        } catch(err)
        {
            esprit.recordError(err);
        }
    },

    requestNewUsername: function(newUsername) {
        
        $.post("/change-username", {
            r: this.activeRoom.getRoomId(),
            newUsername: newUsername
        }, function(data) {
            try {
                if( data['error'] ) {
                    // TODO: Handle error case
                } else if ( ! data['success'] ) {
                    // TODO: Handle bad username case
                } else {
                    var activeSession = zc.pages.room.activeChatSession;
                    activeSession.oldUsername = activeSession.getUsername();
                    activeSession.username = newUsername;
                    // Find this user's session
                    var chatSessions = zc.pages.room.activeRoom.getChatSessions();
                    for( var i = 0; i < chatSessions.length; i++ )
                    {
                        var session = chatSessions[i];
                        if( session.getChatSessionId() == zc.pages.room.activeChatSession.getChatSessionId() ) {
                            session.oldUsername = session.username;
                            session.username = newUsername;
                            zc.pages.room.activeRoom.unupdatedSessions.push(session);
                        }
                    }
                    zc.pages.room.activeRoom.render();
                }
            } catch(err) { esprit.recordError( err ); }
        });

    },

    /**
     * Retrieves the appropriate ping interval for this session.
     */
    getPingInterval: function()
    {
        // This uses the function 1.863132673 e ^ (1.675528755 / x) to determine the ping interval based on the number of
        // currently active sessions.
        var users = zc.pages.room.activeRoom.chatSessions.length;
        var val = 1.863132673 * Math.exp(1.675528755 / users);

        // Cap at a ping every 2 seconds
        if( val < 2 )
            val = 2.0;
        
        // Convert to milliseconds and return
        return val * 1000; 
    },

    /**
     * Pings the server for fresh data.
     */
    ping: function()
    {
        try {
            // Make sure there's not an existing ping
            if( !zc.pages.room.currentPingStart || ((new Date()).getTime() - zc.pages.room.currentPingStart) > 10 ) {
                // No recent outstanding pings; good to go
                zc.pages.room.currentPingStart = (new Date()).getTime();
                zc.pages.room.activeRoom.refreshData(function() {
                    zc.pages.room.currentPingStart = null;
                });
            }
        } catch(err) {
            esprit.recordError( err );
        }

        // Set the next ping attempt interval
        try {
            zc.pages.room.pingTimeoutHandle = setTimeout(zc.pages.room.ping, zc.pages.room.getPingInterval());
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

        // Add any new messages
        for( var key in msgs )
        {
            var message = msgs[key];

            // Has this message been rendered yet?
            if( message.getElement() == null )
            {
                // This message hasn't been rendered yet.
                message.setColorFunction( zc.pages.room.calculateUsernameColor );
                if( message.isSystemMessage() )
                {
                    var msgElem = $('<li class="message sysMessage"><span class="messageContent"></span></li>');
                }
                else
                {
                    var msgElem = $('<li class="message"><span class="username"></span>: <span class="messageContent"></span></li>');
                    var color = message.getColor();
                    msgElem.find(".username").css("color", "#" + color );
                    msgElem.find(".username").text( message.getUsername() );
                }
                
                msgElem.find(".messageContent").text( message.getMessage() );

                // Create any links
                zc.pages.room.linker.linkify( msgElem.find(".messageContent")[0] );
                // Affiliate any links if possible
                if( typeof vglnk !== 'undefined' && typeof vglnk.link === 'function' )
                {
                    var as = msgElem.find("a");
                    for( var i = 0; i < as.length; i++ )
                    {
                        vglnk.link(as[i]);
                    }
                }

                if( ! message.isServerConfirmed() )
                {
                    msgElem.addClass('unconfirmed');
                }

                message.setElement( msgElem );
                msgElem.hide();
                msgElem.fadeIn(50);
                $("#messages").append( msgElem );
            } else
            {
                // We might need to udpate the presentation
                if( $(message.getElement()).hasClass("unconfirmed") && message.isServerConfirmed() )
                {
                    $(message.getElement()).removeClass("unconfirmed");
                }
            }
        }

        // Position the scroll bar at the bottom
        // TODO: Update this code to perform incremental movements in scroll height,
        //       maintaining the current scroll position. This would allow users to
        //       maintain their current scroll position, regardless of whether they're
        //       looking at the bottom or the middle.
        $("#message-area").scrollTop($("#message-area")[0].scrollHeight);

        // First remove any users who changed their username
        var changedUsers = this.getUnupdatedSessions();
        for( var i = 0; i < changedUsers.length; i++ )
        {
            var user = changedUsers[i];
            // Remove it from the array
            changedUsers.splice(i, 1);

            if( user.getElement() == null ) {
                // We'll add it in the next loop. No biggie.
                continue;
            }

            // Create a system message
            // TODO: Add a localized string here
            if( user['oldUsername'] )
                this.createSystemMessage( ""+user['oldUsername']+" has changed his username to "+user['username']+"." );

            // Remove the element. It'll get re-added in the right position with
            // the right username and coloring in the next loop
            $(user.getElement()).remove();
            user.setElement(null);
        }
        
        var users = this.getChatSessions();
        var existingSessions = {};
        for( var i = 0; i < users.length; i++ )
        {
            var user = users[i];
            // Save a mapping from chat session id to user for easy lookup when removing stale users
            existingSessions[user.getChatSessionId()] = user;
            // Update the color function
            user.setColorFunction( zc.pages.room.calculateUsernameColor );
            // If we don't have an element for this user, or that element is not on the DOM, then create one
            if( user.getElement() == null || !$(user.getElement()).closest('html').length )
            {
                var userElem = $("<li><span class='username'></span></li>");
                userElem.find(".username").text( user.getUsername() );
                userElem.find(".username").css('color', '#' + user.getColor() );
                userElem.data('sessionObj', user);
                user.setElement(userElem);

                // Make sure we insert it in its alphabetical position
                if( $("#active-users li").length == 0 )
                    $("#active-users").append(userElem);
                else {
                    var lis = $("#active-users li");
                    var inserted = false;
                    for( var j = 0; (j < lis.length) && !inserted; j++ )
                    {
                        var li = lis[j];
                        var username = $(li).find('.username').text();
                        if( username > user.getUsername() )
                        {
                            inserted = true;
                            userElem.insertBefore(li);
                        }
                    }
                    if( ! inserted ) {
                        inserted = true;
                        $("#active-users").append(userElem);
                    }
                }
            }
        }


        // Remove any users who left the chat room
        var activeUsers = $("#active-users li");
        for( var i = 0; i < activeUsers.length; i++ ) {
            var li = activeUsers[i];
            var chatSession = $(li).data('sessionObj');
            // Is this chat session still around?
            if( chatSession && typeof existingSessions[chatSession.getChatSessionId()] != 'object' ) {
                $(li).remove();
                chatSession.setElement(null);
            }
        }
        
    },

    /**
     * This function calculates the color of a username. It looks at the property
     * of this. This function is mixed into new message and chat sesion objects.
     */
    calculateUsernameColor: function()
    {
        var username = this.username;

        var r=0,g=0,b=0;
        for( var i = 0; i < username.length; i++)
        {
            var code = username.charCodeAt(i);
            var value = 0;
            if( code <= 122 && code >= 97 ) {
                value = code - 97;
            } else if( code <= 57 && code >= 48 ) {
                value = code - 22;
            } else {
                value = 37;
            }

            if( i % 3 == 0 )
                r += value;
            else if( i % 3 == 1 )
                g += value;
            else
                b += value;
        }
        var finalR = (r / ((username.length/3)*36) * 255);
        var finalG = (g / ((username.length/3)*36) * 255);
        var finalB = (b / ((username.length/3)*36) * 255);
        
        var hexColor = parseInt(finalR).toString(16) + parseInt(finalG).toString(16) + parseInt(finalB).toString(16);
        return hexColor;
    }
};

$(document).ready(function(e) {
    zc.pages.room.init();
});
