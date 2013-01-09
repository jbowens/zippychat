
Class('zc.Linker',
{

    have: {

        linkRegexp:/(http:\/\/[^\S]+)($|(\.?\s))/ig

    },

    methods: {

        linkify: function(el)
        {
            // Check if text node?
            if( el.nodeType == 3 )
            {
                var parentNode = el.parentNode;
                var txt = el.data;

                var matches;
                while( matches = this.linkRegexp.exec(txt) )
                {
                    var url = matches[1];
                    // Split the text node at this url.
                    var index = txt.indexOf(url);
                    var frontNode = document.createTextNode( txt.substring(0, index) );
                    var backNode = document.createTextNode( txt.substring(index+url.length) );
                    var urlNode = document.createTextNode( url );
                    var a = document.createElement("a");
                    a.href = urlNode;
                    a.target = '_blank';
                    a.appendChild(urlNode);
                    parentNode.removeChild(el);
                    parentNode.appendChild(frontNode);
                    parentNode.appendChild(a);
                    parentNode.appendChild(backNode);

                    el = backNode;
                    txt = backNode.txt;
                }
                
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
