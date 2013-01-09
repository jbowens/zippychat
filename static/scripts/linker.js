
Class('zc.Linker',
{

    have: {

        linkRegexp:/(https?:\/\/\S+[^.,;?!])/ig

    },

    methods: {

        linkify: function(el)
        {
            // Check if text node?
            if( el.nodeType == 3 )
            {
                var parentNode = el.parentNode;
                txt = el.data;

                var matches = this.linkRegexp.exec(txt);
                if( matches == null )
                    return;

                var url = matches[1];
                // Split the text node at this url.
                var index = txt.indexOf(url);
                var frontNode = document.createTextNode( txt.substring(0, index) );
                var backNode = document.createTextNode( txt.substring(index+url.length) );
                var urlNode = document.createTextNode( url );
                var a = document.createElement("a");
                a.href = url;
                a.target = '_blank';
                a.appendChild(urlNode);
                parentNode.removeChild(el);
                parentNode.appendChild(frontNode);
                parentNode.appendChild(a);
                parentNode.appendChild(backNode);

                return;
            }

            // Recur
            for( var i = 0; i < el.childNodes.length; i++ )
            {
                var child = el.childNodes[i];
                this.linkify(child);
            }
        }

    }

});
