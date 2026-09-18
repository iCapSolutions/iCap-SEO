/**
 * Phase 1 live in-editor panel: a Gutenberg sidebar showing a static (computed
 * on last save, not live-as-you-type) SERP preview, social card preview, and a
 * read-only "quick check" score. Data comes entirely from icapSeoEditorPanel,
 * localized by ICap_SEO_Editor_Panel::enqueue_assets() - this file does no
 * network calls of its own, only rendering plus a client-side pixel-width
 * measurement (which has to happen in the browser, against real font metrics).
 *
 * No build step / JSX, same convention as assets/js/admin.js - written against
 * the wp.* globals WordPress core already registers for the block editor.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.editPost || ! wp.element || ! wp.components || ! wp.i18n ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginSidebar = wp.editPost.PluginSidebar;
	var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
	var Panel = wp.components.Panel;
	var PanelBody = wp.components.PanelBody;
	var __ = wp.i18n.__;

	var data = window.icapSeoEditorPanel || {};
	var checks = data.checks || [];
	// wp_localize_script casts every value to a string, so data.score arrives
	// as e.g. "88", not 88 - parse it rather than type-checking for 'number'.
	var parsedScore = parseInt( data.score, 10 );
	var score = isNaN( parsedScore ) ? null : parsedScore;

	var pixelWidthCanvas = null;
	function measurePixelWidth( text, font ) {
		try {
			if ( ! pixelWidthCanvas ) {
				pixelWidthCanvas = document.createElement( 'canvas' );
			}
			var ctx = pixelWidthCanvas.getContext( '2d' );
			if ( ! ctx ) {
				return null;
			}
			ctx.font = font;
			return Math.round( ctx.measureText( text || '' ).width );
		} catch ( err ) {
			return null;
		}
	}

	function statusColor( status ) {
		if ( status === 'pass' ) {
			return '#1a7f37';
		}
		if ( status === 'warn' ) {
			return '#9a6700';
		}
		return '#cf222e';
	}

	function statusSymbol( status ) {
		if ( status === 'pass' ) {
			return '✓';
		}
		if ( status === 'warn' ) {
			return '!';
		}
		return '✕';
	}

	function ScoreBadge() {
		if ( score === null ) {
			return null;
		}
		var label = score >= 80 ? __( 'Good', 'icap-seo' ) : score >= 50 ? __( 'Needs work', 'icap-seo' ) : __( 'Poor', 'icap-seo' );
		var color = score >= 80 ? '#1a7f37' : score >= 50 ? '#9a6700' : '#cf222e';

		return el(
			'div',
			{ className: 'icap-seo-quick-score' },
			el( 'div', { className: 'icap-seo-quick-score__circle', style: { borderColor: color, color: color } }, String( score ) ),
			el(
				'div',
				{ className: 'icap-seo-quick-score__label' },
				el( 'strong', null, label ),
				el(
					'div',
					{ className: 'icap-seo-quick-score__hint' },
					__(
						'Quick check based on this page’s saved content only. Run a full scan in iCap SEO for the authoritative score.',
						'icap-seo'
					)
				)
			)
		);
	}

	function ChecklistItem( check ) {
		return el(
			'div',
			{ className: 'icap-seo-quick-check icap-seo-quick-check--' + check.status, key: check.code },
			el( 'span', { className: 'icap-seo-quick-check__icon', style: { color: statusColor( check.status ) } }, statusSymbol( check.status ) ),
			el(
				'span',
				{ className: 'icap-seo-quick-check__body' },
				el( 'strong', null, check.label ),
				el( 'div', { className: 'icap-seo-quick-check__detail' }, check.detail )
			)
		);
	}

	function SerpPreview() {
		var title = data.title || __( '(no title)', 'icap-seo' );
		var description = data.description || __( '(no description)', 'icap-seo' );
		var titleWidth = measurePixelWidth( title, '400 20px Arial, sans-serif' );
		var descriptionWidth = measurePixelWidth( description, '400 14px Arial, sans-serif' );
		var titleOverflow = titleWidth !== null && titleWidth > 600;
		var descriptionOverflow = descriptionWidth !== null && descriptionWidth > 1800;

		return el(
			'div',
			{ className: 'icap-seo-serp-preview' },
			el( 'div', { className: 'icap-seo-serp-preview__url' }, data.url || '' ),
			el(
				'div',
				{ className: 'icap-seo-serp-preview__title' + ( titleOverflow ? ' is-overflow' : '' ) },
				title
			),
			el(
				'div',
				{ className: 'icap-seo-serp-preview__description' + ( descriptionOverflow ? ' is-overflow' : '' ) },
				description
			),
			titleOverflow
				? el( 'p', { className: 'icap-seo-serp-preview__note' }, __( 'Title may be truncated in search results at this pixel width.', 'icap-seo' ) )
				: null
		);
	}

	function SocialPreview() {
		return el(
			'div',
			{ className: 'icap-seo-social-preview' },
			data.imageUrl
				? el( 'img', { className: 'icap-seo-social-preview__image', src: data.imageUrl, alt: '' } )
				: el( 'div', { className: 'icap-seo-social-preview__image icap-seo-social-preview__image--empty' } ),
			el(
				'div',
				{ className: 'icap-seo-social-preview__body' },
				el( 'div', { className: 'icap-seo-social-preview__site' }, ( data.siteName || '' ).toUpperCase() ),
				el( 'div', { className: 'icap-seo-social-preview__title' }, data.title || __( '(no title)', 'icap-seo' ) ),
				data.description ? el( 'div', { className: 'icap-seo-social-preview__description' }, data.description ) : null
			)
		);
	}

	function PanelContent() {
		return el(
			'div',
			{ className: 'icap-seo-editor-panel' },
			el( ScoreBadge ),
			el(
				Panel,
				null,
				el( PanelBody, { title: __( 'Quick checks', 'icap-seo' ), initialOpen: true }, checks.map( ChecklistItem ) ),
				el( PanelBody, { title: __( 'Search preview', 'icap-seo' ), initialOpen: true }, el( SerpPreview ) ),
				el( PanelBody, { title: __( 'Social preview', 'icap-seo' ), initialOpen: false }, el( SocialPreview ) )
			)
		);
	}

	registerPlugin( 'icap-seo-editor-panel', {
		icon: 'chart-line',
		render: function () {
			return el(
				Fragment,
				null,
				el( PluginSidebarMoreMenuItem, { target: 'icap-seo-sidebar', icon: 'chart-line' }, __( 'iCap SEO', 'icap-seo' ) ),
				el(
					PluginSidebar,
					{ name: 'icap-seo-sidebar', title: __( 'iCap SEO', 'icap-seo' ), icon: 'chart-line' },
					el( PanelContent )
				)
			);
		},
	} );
} )( window.wp );
