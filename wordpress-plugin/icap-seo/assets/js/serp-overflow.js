( function () {
	try {
		var titleEl = document.getElementById( 'icap-seo-metabox-serp-title' );
		var noteEl = document.getElementById( 'icap-seo-metabox-serp-note' );
		if ( ! titleEl || ! noteEl ) {
			return;
		}
		var canvas = document.createElement( 'canvas' );
		var ctx = canvas.getContext( '2d' );
		if ( ! ctx ) {
			return;
		}
		ctx.font = '400 20px Arial, sans-serif';
		var width = ctx.measureText( titleEl.textContent || '' ).width;
		if ( width > 600 ) {
			titleEl.classList.add( 'is-overflow' );
			noteEl.hidden = false;
		}
	} catch ( err ) {}
} )();
