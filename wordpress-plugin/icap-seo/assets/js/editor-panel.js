/**
 * Live in-editor panel. Phase 1 shipped a static SERP preview, social card
 * preview, and read-only "quick check" score computed once from the post's
 * last-saved content (data comes entirely from icapSeoEditorPanel, localized
 * by ICap_SEO_Editor_Panel::enqueue_assets()). Phase 2 adds live-typing: a
 * debounced subscription to the block editor's own data store re-runs the
 * exact same scoring rules (via a local REST call to
 * ICap_SEO_Editor_Panel::handle_quick_check_request(), not a second JS
 * implementation of the rules) against the CURRENT draft - title/content/
 * excerpt the user hasn't saved yet. The image/URL/site-name parts of the
 * SERP and social previews stay as the last-saved snapshot: neither changes
 * from typing, and refreshing them live would mean also live-tracking
 * featured-image changes, which is out of Phase 2's scope.
 *
 * No build step / JSX, same convention as assets/js/admin.js - written against
 * the wp.* globals WordPress core already registers for the block editor.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.editPost || ! wp.element || ! wp.components || ! wp.i18n || ! wp.data || ! wp.apiFetch ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginSidebar = wp.editPost.PluginSidebar;
	var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
	var Panel = wp.components.Panel;
	var PanelBody = wp.components.PanelBody;
	var __ = wp.i18n.__;

	var initialData = window.icapSeoEditorPanel || {};
	var postId = parseInt( initialData.postId, 10 ) || 0;
	var LIVE_DEBOUNCE_MS = 700;

	function parseScore( value ) {
		// wp_localize_script and the REST response both may carry this as a
		// string - parse rather than type-check for 'number'.
		var parsed = parseInt( value, 10 );
		return isNaN( parsed ) ? null : parsed;
	}

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

	function ScoreBadge( props ) {
		var score = props.score;
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
					props.isLive
						? __( 'Quick check, updated as you type. Run a full scan in iCap SEO for the authoritative score.', 'icap-seo' )
						: __(
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

	function SerpPreview( props ) {
		var title = props.title || __( '(no title)', 'icap-seo' );
		var description = props.description || __( '(no description)', 'icap-seo' );
		var titleWidth = measurePixelWidth( title, '400 20px Arial, sans-serif' );
		var descriptionWidth = measurePixelWidth( description, '400 14px Arial, sans-serif' );
		var titleOverflow = titleWidth !== null && titleWidth > 600;
		var descriptionOverflow = descriptionWidth !== null && descriptionWidth > 1800;

		return el(
			'div',
			{ className: 'icap-seo-serp-preview' },
			el( 'div', { className: 'icap-seo-serp-preview__url' }, initialData.url || '' ),
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

	function SocialPreview( props ) {
		return el(
			'div',
			{ className: 'icap-seo-social-preview' },
			initialData.imageUrl
				? el( 'img', { className: 'icap-seo-social-preview__image', src: initialData.imageUrl, alt: '' } )
				: el( 'div', { className: 'icap-seo-social-preview__image icap-seo-social-preview__image--empty' } ),
			el(
				'div',
				{ className: 'icap-seo-social-preview__body' },
				el( 'div', { className: 'icap-seo-social-preview__site' }, ( initialData.siteName || '' ).toUpperCase() ),
				el( 'div', { className: 'icap-seo-social-preview__title' }, props.title || __( '(no title)', 'icap-seo' ) ),
				props.description ? el( 'div', { className: 'icap-seo-social-preview__description' }, props.description ) : null
			)
		);
	}

	/**
	 * Reads the current in-progress draft (not the last-saved row) from the
	 * block editor's own data store. Content/excerpt come back serialized the
	 * same way a saved post's fields would, so the shared PHP checks (regexing
	 * for <img>/<h1-6> tags, word-counting stripped text) work unmodified.
	 */
	function readDraftFields() {
		var editor = wp.data.select( 'core/editor' );
		if ( ! editor ) {
			return null;
		}
		return {
			title: editor.getEditedPostAttribute( 'title' ) || '',
			content: editor.getEditedPostAttribute( 'content' ) || '',
			excerpt: editor.getEditedPostAttribute( 'excerpt' ) || '',
		};
	}

	function fieldsChanged( a, b ) {
		return ! a || ! b || a.title !== b.title || a.content !== b.content || a.excerpt !== b.excerpt;
	}

	/**
	 * Phase 3 (schema-only v1): maps the cloud quick-scan endpoint's issues
	 * (same shape as the full-scan catalog) into one checklist row, the same
	 * way the other quick checks read. Doesn't factor into the score badge -
	 * that stays purely the client-computable checks quick-check already
	 * scores, avoiding two independent async responses fighting over one
	 * number.
	 */
	function issuesToStructuredDataCheck( issues ) {
		if ( ! issues || ! issues.length ) {
			return {
				code: 'structured_data',
				label: __( 'Structured data', 'icap-seo' ),
				status: 'pass',
				detail: __( 'No structured-data issues found for this draft.', 'icap-seo' ),
			};
		}
		return {
			code: 'structured_data',
			label: __( 'Structured data', 'icap-seo' ),
			status: issues[ 0 ].severity === 'high' ? 'fail' : 'warn',
			detail: issues[ 0 ].description || __( 'Structured data issue detected.', 'icap-seo' ),
		};
	}

	function PanelContent() {
		var initialScore = parseScore( initialData.score );
		var initialState = {
			score: initialScore,
			checks: initialData.checks || [],
			title: initialData.title || '',
			description: initialData.description || '',
			isLive: false,
			schemaCheck: null,
		};

		var stateHook = useState( initialState );
		var state = stateHook[ 0 ];
		var setState = stateHook[ 1 ];

		useEffect( function () {
			if ( ! postId ) {
				return;
			}

			var debounceTimer = null;
			var lastRequested = null;
			var lastRequestedSchema = null;
			var lastSent = readDraftFields();
			var unsubscribed = false;

			function requestQuickCheck( fields ) {
				lastRequested = fields;
				wp.apiFetch( {
					path: '/icap-seo/v1/editor-panel/quick-check',
					method: 'POST',
					data: {
						post_id: postId,
						title: fields.title,
						content: fields.content,
						excerpt: fields.excerpt,
					},
				} )
					.then( function ( response ) {
						// A slower request that finishes after a newer one was
						// already sent would otherwise clobber fresher state.
						if ( fields !== lastRequested ) {
							return;
						}
						// Merge via the previous-state updater, not a full replace -
						// schemaCheck is set independently by requestQuickScan below,
						// and a full-object setState here would wipe it out whenever
						// this (usually faster, local-only) response lands after it.
						setState( function ( prev ) {
							return Object.assign( {}, prev, {
								score: parseScore( response.score ),
								checks: response.checks || [],
								title: response.title || '',
								description: response.description || '',
								isLive: true,
							} );
						} );
					} )
					.catch( function () {
						// Live refresh is a nice-to-have; keep showing the last
						// good state (or the Phase 1 static snapshot) on error.
					} );
			}

			function requestQuickScan( fields ) {
				lastRequestedSchema = fields;
				wp.apiFetch( {
					path: '/icap-seo/v1/editor-panel/quick-scan',
					method: 'POST',
					data: {
						post_id: postId,
						title: fields.title,
						content: fields.content,
						excerpt: fields.excerpt,
					},
				} )
					.then( function ( response ) {
						if ( fields !== lastRequestedSchema ) {
							return;
						}
						setState( function ( prev ) {
							return Object.assign( {}, prev, {
								schemaCheck: issuesToStructuredDataCheck( response.issues || [] ),
							} );
						} );
					} )
					.catch( function () {
						// Same nice-to-have posture as quick-check above.
					} );
			}

			var unsubscribe = wp.data.subscribe( function () {
				if ( unsubscribed ) {
					return;
				}
				var current = readDraftFields();
				if ( ! fieldsChanged( current, lastSent ) ) {
					return;
				}
				lastSent = current;
				if ( debounceTimer ) {
					clearTimeout( debounceTimer );
				}
				debounceTimer = setTimeout( function () {
					requestQuickCheck( current );
					requestQuickScan( current );
				}, LIVE_DEBOUNCE_MS );
			} );

			return function () {
				unsubscribed = true;
				if ( debounceTimer ) {
					clearTimeout( debounceTimer );
				}
				unsubscribe();
			};
		}, [] );

		var allChecks = state.schemaCheck ? state.checks.concat( [ state.schemaCheck ] ) : state.checks;

		return el(
			'div',
			{ className: 'icap-seo-editor-panel' },
			el( ScoreBadge, { score: state.score, isLive: state.isLive } ),
			el(
				Panel,
				null,
				el( PanelBody, { title: __( 'Quick checks', 'icap-seo' ), initialOpen: true }, allChecks.map( ChecklistItem ) ),
				el(
					PanelBody,
					{ title: __( 'Search preview', 'icap-seo' ), initialOpen: true },
					el( SerpPreview, { title: state.title, description: state.description } )
				),
				el(
					PanelBody,
					{ title: __( 'Social preview', 'icap-seo' ), initialOpen: false },
					el( SocialPreview, { title: state.title, description: state.description } )
				)
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
