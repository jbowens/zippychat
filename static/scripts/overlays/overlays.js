var zc = zc || {};

/**
 * The zc.overlays namespace defines a namespace for any overlays/dialogs
 * displayed on the site.
 *
 * @author jbowens
 * @since 2012-08-28
 */
zc.overlays = zc.overlays || {

    // TODO: Create overlay factory

},

/**
 * The base overlay object.
 *
 * @author jbowens
 * @since 2012-08-30
 */
zc.overlays.Overlay = zc.overlays.Overlay || {
    
    elmt: null,
    parentElmt: null,
    displayImmediately: true,
    zindexCalculator: null,
    width: null,
    height: null,
    extraClasses: [],

    /**
     * Constructs a new overlay
     *
     * @param options  the options with which to create the overlay
     */
    construct: function(options)
    {
        var overlay = esprit.oop.extend(zc.overlays.Overlay, options);

        if( overlay.elmt )
            $(overlay.elmt).addClass('overlay');
        else
            overlay.elmt = $('<div class="overlay"></div>');

        // Add any extra classes provided through options
        for( var i = 0; i < overlay.extraClasses; i++ )
        {
            $(overlay.elmt).addClass(overlay.extraClasses[i]);
        }

        // We don't want the overlay to be visible until we're ready
        $(overlay.elmt).hide();

        // Set dimensions if provided
        if( overlay.width )
            $(overlay.elmt).css('width', overlay.getCssWidth());
        if( overlay.height )
            $(overlay.elmt).css('height', overlay.getCssHeight());

        $(overlay.elmt).css('top', overlay.getCssTopOffset());
        $(overlay.elmt).css('left', overlay.getCssLeftOffset());

        // Calculate the CSS z-index propery
        var zIndex = 5000;
        if( overlay.zindexCalculator )
        {
           zIndex = overlay.zindexCalculator();
        }
        overlay.elmt.css("z-index", zIndex);

        // Add the overlay to the DOM
        if( ! overlay.parentElmt )
            overlay.parentElmt = document.body;
        $(overlay.parentElmt).append( overlay.elmt );

        // Should we show the overlay now?
        if( overlay.displayImmediately ) {
            overlay.show();
        }

        return overlay;
    },

    /**
     * Hides this overlay.
     */
    hide: function() {
        if( this.elmt == null )
        {
            return;
        }
        $(this.elmt).hide();
    },

    /**
     * Shows the overlay, optionally with a fade in.
     *
     * @param animateSpeed  the speed within which to fade in. omit this
     *                      parameter for no fade in
     */
    show: function(animateSpeed) {
        if( this.elmt == null )
        {
            return;
        }

        if( ! animateSpeed || animateSpeed == 0 )
        {
            $(this.elmt).show();
        }
        else
        {
            $(this.elmt).fadeIn(animateSpeed);
        }
    },

    /**
     * Sets the content of this overlay to be the given html.
     */
    setHtml: function(html) {
        var newContent = $(html);
        $(this.elmt).empty();
        $(this.elmt).append(newContent);
    },

    /**
     * Sets the content of this overlay to be the given DOM
     * element.
     */
    setContent: function(element) {
        $(this.elmt).empty();
        $(this.elmt).append(element);
    },

    /**
     * Returns a string that may be used in CSS as the value
     * of the width property.
     */
    getCssWidth: function() {
        // Handle case where we don't have a width
        if( typeof this.width == 'undefined' || this.width == null )
            return 'auto';
        if( typeof this.width == 'number' )
            return this.width + "px";
        // Assume it already includes units
        return this.width;
    },

    /**
     * Returns a string that may be used in CSS as the value
     * of the height property.
     */
    getCssHeight: function() {
        if( typeof this.height == 'undefined' || this.height == null )
            return 'auto';
        if( typeof this.height == 'number' )
            return this.width + 'px';
        // Assume it already includes units
        return this.height;
    },

    /**
     * Returns a string that may be used in CSS as the value of
     * the 'top' property.
     */
    getCssTopOffset: function() {
        if( typeof this['top'] == 'undefined' || this['top'] == null )
            return 'auto';
        if( typeof this['top'] == 'number' )
            return this['top'] + 'px';
        return this['top'];
    },

    /**
     * Returns a string that may be used in CSS as the value of
     * the 'left' property.
     */
    getCssLeftOffset: function() {
        if( typeof this.left == 'undefined' || this.left == null )
            return 'auto';
        if( typeof this.left == 'number' )
            return this.left + 'px';
        return this.left;
    }

};
