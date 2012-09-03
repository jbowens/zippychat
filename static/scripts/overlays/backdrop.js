var zc = zc || {};

/**
 * We can only do shit if we have the rest of the overlay namespace!
 */
if( ! zc.overlays || ! zc.overlays.Overlay )
{
    try {
        esprit.recordError( new Error("no zc.overlays || zc.overlays.Overlay") );
    } catch(err)
    { /* fuck. */ }
}

/**
 * A simple centered dialog.
 *
 * @author jbowens
 * @since 2012-08-30
 */
zc.overlays.Backdrop = zc.overlays.Backdrop || {

    backdropColor: "#000",
    backdropOpacity: 0.5,

    /**
     * Constructs a new backdrop
     *
     * ops : an object of options:
     *  @param backdropColor  the color of the backdrop
     *  @param backdropOpacity  the opacity between 0 and 1 inclusive
     *  [Also accepts any options that zc.overlays accepts]
     */
    construct: function(ops) {
        if( ! ops.zindexCalculator )
        {
            ops.zindexCalculator = function() {
                return 4000;
            };
        }

        ops.left = 0;
        ops.top = 0;

        // Call the parent constructor
        var overlay = zc.overlays.Overlay.construct(ops);
        overlay = esprit.oop.extend(zc.overlays.Backdrop, overlay);

        $(overlay.elmt).addClass('backdrop');
        $(overlay.elmt).css('background-color', overlay.backdropColor);
        $(overlay.elmt).css('zoom', 1);
        $(overlay.elmt).css('filter', 'alpha(opacity=' + parseInt(overlay.backdropOpacity*100) + ')');
        $(overlay.elmt).css('opacity', overlay.backdropOpacity);

        return overlay;
    }

}
