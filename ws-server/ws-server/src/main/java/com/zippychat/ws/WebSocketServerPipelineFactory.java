package com.zippychat.ws;

import org.jboss.netty.channel.Channels;
import org.jboss.netty.channel.ChannelPipeline;
import org.jboss.netty.channel.ChannelPipelineFactory;
import org.jboss.netty.handler.codec.http.HttpChunkAggregator;
import org.jboss.netty.handler.codec.http.HttpRequestDecoder;
import org.jboss.netty.handler.codec.http.HttpResponseEncoder;

/**
 * Our pipeline for dealing with connections.
 *
 * Adapated from http://kevinwebber.ca/blog/2011/11/2/multiplayer-tic-tac-toe-in-java-using-the-websocket-api-nett.html
 */
class WebSocketServerPipelineFactory implements ChannelPipelineFactory
{
    
    protected static final int CHUNK_SIZE = 65536;

    public ChannelPipeline getPipeline() throws Exception {
        // Create a default pipeline implementation.
        ChannelPipeline pipeline = Channels.pipeline();
        pipeline.addLast("decoder", new HttpRequestDecoder());
        pipeline.addLast("aggregator", new HttpChunkAggregator( WebsocketServerPipelineFactory.CHUNK_SIZE ));
        pipeline.addLast("encoder", new HttpResponseEncoder());
        pipeline.addLast("handler", new ChatServerHandler());
        return pipeline;
    }

}
