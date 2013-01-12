package com.zippychat.ws;

import java.net.InetSocketAddress;
import java.util.concurrent.Executors;
import org.jboss.netty.bootstrap.ServerBootstrap;
import org.jboss.netty.channel.ChannelFactory;
import org.jboss.netty.channel.socket.nio.NioServerSocketChannelFactory;

/**
 * Main class for the WebSocket server.
 *
 * @author jbowens
 * @since 2013-01-11
 */
public class WebSocketServer
{

    // The default port to use if no port is provided
    private static final int DEFAULT_PORT = 8080;

    // The port on which the WebSocket server listens
    private final int port;

    public WebSocketServer(int p)
    {
        this.port = p;
    }

    /**
     * Starts up the server.
     */
    public void start() {

        ChannelFactory factory = new NioServerSocketChannelFactory(
                Executors.newCachedThreadPool(),
                Executors.newCachedThreadPool());

        ServerBootstrap bootstrap = new ServerBootstrap(factory);
        bootstrap.setPipelineFactory(new WebSocketServerPipelineFactory());

        bootstrap.bind(new InetSocketAddress(this.port));
        System.out.println("Chat WebSockets Server: Listening on port " + this.port);

    }

    public static void main( String[] args )
    {
        // Determine the port to listen on
        int port;
        if( args.length > 0 ) {
            port = Integer.parseInt( args[0] );
        } else {
            port = WebSocketServer.DEFAULT_PORT;
        }

        // Create and start the server
        WebSocketServer server = new WebSocketServer( port );
        server.start();
    }

}
