/**
 * iCap SEO sidebar in the block editor. Data comes from icapSeoEditorPanel,
 * localized by ICap_SEO_Editor_Panel::enqueue_assets().
 *
 * - The score is the page's Content Scores number. Check now runs the on-site
 *   checks for this page (saved version) through a local REST route.
 * - Live checks and the previews re-run on the unsaved draft as the user types
 *   (debounced), through ICap_SEO_Editor_Panel::handle_quick_check_request(), so
 *   the rules live only in PHP.
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
	var Button = wp.components.Button;
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
		if ( status === 'tip' ) {
			return '#2271b1';
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
		if ( status === 'tip' ) {
			return 'i';
		}
		return '✕';
	}

	function savedScoreHint( saved ) {
		if ( saved.source === 'scan' ) {
			return __( 'From the last full scan.', 'icap-seo' );
		}
		if ( saved.source === 'local' ) {
			/* translators: %s: date and time the page was last checked */
			return saved.checkedAt ? wp.i18n.sprintf( __( 'Checked %s.', 'icap-seo' ), saved.checkedAt ) : __( 'From the on-site checks.', 'icap-seo' );
		}
		return __( 'Not checked since this page last changed.', 'icap-seo' );
	}

	function ScoreBadge( props ) {
		var saved = props.saved || {};
		var score = parseScore( saved.score );
		// Keep in sync with ICap_SEO_Admin::score_band_colors()/score_band_text_colors()
		// (PHP) - this runs client-side in the block editor, so the palette
		// can't be shared directly and is duplicated here. borderColor uses the
		// light fill variant; color uses the darker, readable-on-white variant
		// (a light yellow in particular is illegible as text).
		var circle = score === null
			? el( 'div', { className: 'icap-seo-quick-score__circle icap-seo-quick-score__circle--empty' }, '–' )
			: el(
					'div',
					{
						className: 'icap-seo-quick-score__circle',
						style: {
							borderColor: score >= 80 ? '#7bc47f' : score >= 50 ? '#f5c518' : '#f28b82',
							color: score >= 80 ? '#2e8f52' : score >= 50 ? '#b7950b' : '#d1443b',
						},
					},
					String( score )
			  );

		return el(
			'div',
			{ className: 'icap-seo-quick-score' },
			circle,
			el(
				'div',
				{ className: 'icap-seo-quick-score__label' },
				el( 'strong', null, __( 'Content Scores', 'icap-seo' ) ),
				el( 'div', { className: 'icap-seo-quick-score__hint' }, props.message || savedScoreHint( saved ) ),
				el(
					'div',
					{ className: 'icap-seo-quick-score__actions' },
					saved.source !== 'scan'
						? el( Button, { variant: 'secondary', isSmall: true, isBusy: props.checking, disabled: props.checking, onClick: props.onCheck }, props.checking ? __( 'Checking…', 'icap-seo' ) : __( 'Check now', 'icap-seo' ) )
						: null,
					saved.detailUrl ? el( 'a', { className: 'icap-seo-quick-score__link', href: saved.detailUrl }, __( 'Open in Content Scores', 'icap-seo' ) ) : null
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

	// Kept in sync with ICap_SEO_Editor_Panel::social_image_note() (PHP).
	function socialImageNote( source ) {
		if ( source === 'featured' ) {
			return '';
		}
		if ( source === 'content' ) {
			return __( 'No featured image, so shares use the first image in this page.', 'icap-seo' );
		}
		if ( source === 'logo' ) {
			return __( 'No featured image or page image, so shares use the site logo.', 'icap-seo' );
		}
		if ( source === 'site_icon' ) {
			return __( 'No featured image or page image, so shares use the site icon.', 'icap-seo' );
		}
		return __( 'Shares of this page show no picture. Set a featured image, or add an image to the page.', 'icap-seo' );
	}

	function SocialPreview( props ) {
		var image = props.image || {};
		var note = socialImageNote( image.source || '' );
		return el(
			Fragment,
			null,
			el(
				'div',
				{ className: 'icap-seo-social-preview' },
				image.url
					? el( 'img', { className: 'icap-seo-social-preview__image', src: image.url, alt: '' } )
					: el( 'div', { className: 'icap-seo-social-preview__image icap-seo-social-preview__image--empty' }, __( 'No picture', 'icap-seo' ) ),
				el(
					'div',
					{ className: 'icap-seo-social-preview__body' },
					el( 'div', { className: 'icap-seo-social-preview__site' }, ( initialData.siteName || '' ).toUpperCase() ),
					el( 'div', { className: 'icap-seo-social-preview__title' }, props.title || __( '(no title)', 'icap-seo' ) ),
					props.description ? el( 'div', { className: 'icap-seo-social-preview__description' }, props.description ) : null
				)
			),
			note ? el( 'p', { className: 'icap-seo-social-preview__note' }, note ) : null
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
			featuredMedia: editor.getEditedPostAttribute( 'featured_media' ) || 0,
		};
	}

	function fieldsChanged( a, b ) {
		return ! a || ! b || a.title !== b.title || a.content !== b.content || a.excerpt !== b.excerpt || a.featuredMedia !== b.featuredMedia;
	}

	function PanelContent() {
		var stateHook = useState( {
			checks: initialData.checks || [],
			tip: initialData.tip || null,
			title: initialData.title || '',
			searchTitle: initialData.searchTitle || '',
			description: initialData.description || '',
			image: initialData.image || { url: '', source: '' },
		} );
		var state = stateHook[ 0 ];
		var setState = stateHook[ 1 ];

		var savedHook = useState( initialData.saved || {} );
		var saved = savedHook[ 0 ];
		var setSaved = savedHook[ 1 ];

		var checkHook = useState( { checking: false, message: '' } );
		var checkState = checkHook[ 0 ];
		var setCheckState = checkHook[ 1 ];

		useEffect( function () {
			if ( ! postId ) {
				return;
			}

			var debounceTimer = null;
			var lastRequested = null;
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
						featured_media: fields.featuredMedia,
					},
				} )
					.then( function ( response ) {
						// A slower request that finishes after a newer one was
						// already sent would otherwise clobber fresher state.
						if ( fields !== lastRequested ) {
							return;
						}
						setState( {
							checks: response.checks || [],
							tip: response.tip || null,
							title: response.title || '',
							searchTitle: response.searchTitle || '',
							description: response.description || '',
							image: response.image || { url: '', source: '' },
						} );
					} )
					.catch( function () {
						// Live refresh is a nice-to-have; keep showing the last
						// good state on error.
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

		function checkNow() {
			var editor = wp.data.select( 'core/editor' );
			if ( editor && editor.isEditedPostDirty() ) {
				setCheckState( { checking: false, message: __( 'Save your changes first. The check reads the saved page.', 'icap-seo' ) } );
				return;
			}
			setCheckState( { checking: true, message: '' } );
			wp.apiFetch( { path: '/icap-seo/v1/editor-panel/recheck', method: 'POST', data: { post_id: postId } } )
				.then( function ( response ) {
					if ( response.saved ) {
						setSaved( response.saved );
					}
					setCheckState( { checking: false, message: response.error || '' } );
				} )
				.catch( function () {
					setCheckState( { checking: false, message: __( 'This page couldn’t be checked right now. Try again, or use Check all pages in Content Scores.', 'icap-seo' ) } );
				} );
		}

		var items = state.tip ? state.checks.concat( [ state.tip ] ) : state.checks;

		return el(
			'div',
			{ className: 'icap-seo-editor-panel' },
			el( ScoreBadge, { saved: saved, checking: checkState.checking, message: checkState.message, onCheck: checkNow } ),
			el(
				Panel,
				null,
				el(
					PanelBody,
					{ title: __( 'Live checks', 'icap-seo' ), initialOpen: true },
					el( 'p', { className: 'icap-seo-quick-check__intro' }, __( 'The basics from Content Scores, updated as you type. Content Scores runs every check on the published page.', 'icap-seo' ) ),
					items.map( ChecklistItem )
				),
				el(
					PanelBody,
					{ title: __( 'Search preview', 'icap-seo' ), initialOpen: true },
					el( 'p', { className: 'icap-seo-quick-check__intro' }, __( 'How this page can look as a Google result.', 'icap-seo' ) ),
					el( SerpPreview, { title: state.searchTitle, description: state.description } )
				),
				el(
					PanelBody,
					{ title: __( 'Social preview', 'icap-seo' ), initialOpen: false },
					el( 'p', { className: 'icap-seo-quick-check__intro' }, __( 'How this page looks when its link is shared on Facebook, LinkedIn or X.', 'icap-seo' ) ),
					el( SocialPreview, { title: state.title, description: state.description, image: state.image } )
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
