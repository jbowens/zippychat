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
zc.overlays.SimpleDialog = zc.overlays.SimpleDialog || esprit.oop.extend(zc.overlays.Overlay, {

    construct: function(width, ops) {

        ops.width = width;
        ops['top'] = 250;
        ops.left = "50%";

        // Call the parent constructor
        var overlay = zc.overlays.Overlay.construct(ops);
        overlay = esprit.oop.extend(zc.overlays.SimpleDialog, overlay);

        $(overlay.elmt).css('margin-left', '-' + parseInt(width/2) + 'px');

        return overlay;
    }

});
