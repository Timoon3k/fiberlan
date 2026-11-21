<?php
/**
 * Opt-in-safe one-time refinement of existing PL/EN Gutenberg pages.
 *
 * The public site stores actual copy in wp_posts, not PHP patterns. Applying
 * this scoped update is therefore essential for already-installed sites.
 * Only managed pages are touched; WordPress revisions retain the old text.
 * When a page is being edited, the update is postponed instead of competing
 * with the editor. No arbitrary content or third-party reviews are replaced.
 *
 * @package FiberlanCore
 */
defined( 'ABSPATH' ) || exit;

function fl_layout_has_class( $block, $class ) {
	return in_array( $class, preg_split( '/\s+/', $block['attrs']['className'] ?? '' ), true );
}

function fl_layout_add_class( &$block, $new_class ) {
	$old_class = trim( $block['attrs']['className'] ?? '' );
	if ( fl_layout_has_class( $block, $new_class ) ) {
		return;
	}
	$block['attrs']['className'] = trim( $old_class . ' ' . $new_class );
	foreach ( $block['innerContent'] as &$fragment ) {
		if ( ! is_string( $fragment ) || false === strpos( $fragment, 'class=' ) ) {
			continue;
		}
		$from = 'class="wp-block-group ' . esc_attr( $old_class ) . '"';
		$to = 'class="wp-block-group ' . esc_attr( $block['attrs']['className'] ) . '"';
		$fragment = str_replace( $from, $to, $fragment, $count );
		if ( $count ) {
			break;
		}
	}
	unset( $fragment );
}

function fl_layout_insert_first( &$parent, $new_block ) {
	array_unshift( $parent['innerBlocks'], $new_block );
	// The first fragment opens the group wrapper; null marks a nested block.
	array_splice( $parent['innerContent'], 1, 0, array( null ) );
}

function fl_layout_insert_last( &$parent, $new_block ) {
	$parent['innerBlocks'][] = $new_block;
	$last = count( $parent['innerContent'] ) - 1;
	array_splice( $parent['innerContent'], max( 1, $last ), 0, array( null ) );
}

function fl_layout_placeholder_block( $lang, $class, $subject ) {
	$parts = parse_blocks( fl_photo_placeholder( $lang, $class, $subject ) );
	return $parts[0];
}

function fl_layout_summary( $block, $lang ) {
	$inner = $block['innerHTML'] ?? '';
	if ( ! preg_match( '~<summary\b[^>]*>(.*?)</summary>~isu', $inner, $matches ) ) {
		foreach ( $block['innerContent'] ?? array() as $fragment ) {
			if ( is_string( $fragment ) && preg_match( '~<summary\b[^>]*>(.*?)</summary>~isu', $fragment, $matches ) ) {
				break;
			}
		}
	}
	return ! empty( $matches[1] ) ? trim( wp_strip_all_tags( $matches[1] ) )
		: fl_text( $lang, 'Informacje', 'Details' );
}

function fl_layout_open_details( $blocks, $lang, &$changed ) {
	foreach ( $blocks as $index => $block ) {
		if ( 'core/details' === ( $block['blockName'] ?? '' ) ) {
			$summary = fl_layout_summary( $block, $lang );
			$details = serialize_blocks( $block['innerBlocks'] ?? array() );
			if ( '' === trim( $details ) && preg_match( '~</summary>(.*?)</details>~isu', $block['innerHTML'] ?? '', $copy ) ) {
				$plain = trim( wp_strip_all_tags( $copy[1] ) );
				$details = $plain ? fl_paragraph( $plain ) : '';
			}
			$content = fl_heading( $summary, 3 ) . $details;
			$replacement = parse_blocks( fl_group( 'fl-open-info', $content ) );
			$blocks[ $index ] = $replacement[0];
			$changed = true;
		} elseif ( ! empty( $block['innerBlocks'] ) ) {
			$blocks[ $index ]['innerBlocks'] = fl_layout_open_details( $block['innerBlocks'], $lang, $changed );
		}
	}
	return $blocks;
}

function fl_layout_find_photo( $blocks, $class ) {
	foreach ( $blocks as $block ) {
		if ( fl_layout_has_class( $block, $class ) ) {
			return true;
		}
		if ( ! empty( $block['innerBlocks'] ) && fl_layout_find_photo( $block['innerBlocks'], $class ) ) {
			return true;
		}
	}
	return false;
}

function fl_layout_about( $blocks, $lang, &$changed ) {
	// The old text-only intro had a forced two-column layout with an empty left
	// column. Add a genuine, replaceable image block in that precise location.
	if ( ! fl_layout_find_photo( $blocks, 'fl-team-photo' ) ) {
		foreach ( $blocks as &$section ) {
			if ( ! fl_layout_has_class( $section, 'fl-section' ) || fl_layout_has_class( $section, 'fl-ribbon' ) ) {
				continue;
			}
			foreach ( $section['innerBlocks'] as &$shell ) {
				if ( ! fl_layout_has_class( $shell, 'fl-reading' ) && ! fl_layout_has_class( $shell, 'fl-about-story' ) ) {
					continue;
				}
				fl_layout_add_class( $shell, 'fl-about-story' );
				fl_layout_insert_first( $shell,
					fl_layout_placeholder_block( $lang, 'fl-team-photo',
						fl_text( $lang, 'zespół FIBERLAN', 'FIBERLAN team' ) ) );
				$changed = true;
				break 2;
			}
			unset( $shell );
		}
		unset( $section );
	}
	// All native expandable details on the About page become normal visible
	// headings and paragraphs, including client-added 24/7 / quality panels.
	$blocks = fl_layout_open_details( $blocks, $lang, $changed );
	if ( 'en' === $lang ) {
		$blocks = fl_layout_about_en( $blocks, $changed );
	}
	return $blocks;
}

/** Bring the existing English About copy in line with the supplied PL copy. */
function fl_layout_about_en( $blocks, &$changed ) {
	$translations = array(
		'From installation to acceptance testing' => 'Our team and expertise',
		'Our work covers fibre splicing, cable route installation and optical testing. We agree the scope with the client and document acceptance tests against the project requirements.'
			=> 'FIBERLAN is a specialised team of engineers and technicians. For two decades, we have built, tested and maintained critical telecommunications infrastructure. Since 2006, we have supported businesses, developers, network operators and data centres with reliable fibre optic and structured Ethernet networks.',
		'We work on new networks and in operational facilities. For upgrades, we plan around access to cable routes and agreed maintenance windows.'
			=> 'We work on both new installations and operational facilities. During upgrades, we plan around cable route access and agreed maintenance windows.',
	);
	foreach ( $blocks as &$section ) {
		if ( ! fl_layout_has_class( $section, 'fl-section' ) || fl_layout_has_class( $section, 'fl-ribbon' ) ) {
			continue;
		}
		foreach ( $section['innerBlocks'] as &$shell ) {
			if ( ! fl_layout_has_class( $shell, 'fl-about-story' ) ) {
				continue;
			}
			foreach ( $shell['innerBlocks'] as &$block ) {
				if ( ! in_array( $block['blockName'] ?? '', array( 'core/paragraph', 'core/heading' ), true ) ) {
					continue;
				}
				$plain = trim( wp_strip_all_tags( $block['innerHTML'] ?? '' ) );
				if ( isset( $translations[ $plain ] ) ) {
					$markup = 'core/heading' === $block['blockName']
						? fl_heading( $translations[ $plain ], (int) ( $block['attrs']['level'] ?? 2 ) )
						: fl_paragraph( $translations[ $plain ] );
					$block = parse_blocks( $markup )[0];
					$changed = true;
				}
			}
			unset( $block );
			break 2;
		}
		unset( $shell );
	}
	unset( $section );
	return $blocks;
}

function fl_layout_services( $blocks, $lang, &$changed ) {
	foreach ( $blocks as &$section ) {
		if ( fl_layout_has_class( $section, 'fl-subhero' ) ) {
			continue;
		}
		fl_layout_replace_images( $section, $lang, $changed );
	}
	unset( $section );
	return $blocks;
}

function fl_layout_replace_images( &$block, $lang, &$changed ) {
	if ( empty( $block['innerBlocks'] ) ) {
		return;
	}
	// Iterate the actual nested array by reference: ?? would create a copy and
	// silently discard replaced image blocks.
	foreach ( $block['innerBlocks'] as &$child ) {
		if ( 'core/image' === ( $child['blockName'] ?? '' )
			&& empty( $child['attrs']['id'] )
			&& preg_match( '~/assets/(?:diagrams/service-(?:splice|lan)\.svg|scenes/(?:optical-fibres|patch-panel)\.webp)"~', $child['innerHTML'] ) ) {
			$alt = wp_strip_all_tags( $child['attrs']['alt'] ?? '' );
			$child = fl_layout_placeholder_block( $lang, 'fl-section-image',
				$alt ?: fl_text( $lang, 'fotografia usługi', 'service photograph' ) );
			$changed = true;
			continue;
		}
		fl_layout_replace_images( $child, $lang, $changed );
	}
	unset( $child );
}

function fl_layout_lead( &$blocks, $lang, &$changed, &$original ) {
	foreach ( $blocks as &$block ) {
		if ( 'core/paragraph' === ( $block['blockName'] ?? '' ) && fl_layout_has_class( $block, 'fl-lead' ) ) {
			$plain = trim( wp_strip_all_tags( $block['innerHTML'] ) );
			if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $plain ) : strlen( $plain ) ) > 360 ) {
				$original = $plain;
				$summary = fl_text( $lang,
					'Pracujemy na certyfikowanym sprzęcie Fujikura, Fluke Networks, EXFO oraz VIAVI. Dbamy o dokładność spawów i rzetelność pomiarów.',
					'We use certified equipment from Fujikura, Fluke Networks, EXFO and VIAVI to deliver precise fibre splicing and reliable test results.' );
				$block = parse_blocks( fl_paragraph( $summary, 'fl-lead' ) )[0];
				$changed = true;
			}
			return;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			fl_layout_lead( $block['innerBlocks'], $lang, $changed, $original );
			if ( $original ) {
				return;
			}
		}
	}
	unset( $block );
}


/**
 * EXFO currently uses a stock illustration in the fourth equipment row.
 * Replace only this particular image; keep the client-supplied Fujikura,
 * Fluke and VIAVI photographs.
 */
function fl_layout_equipment_exfo_photo( &$blocks, $lang, &$changed ) {
	foreach ( $blocks as &$block ) {
		if ( fl_layout_has_class( $block, 'fl-equipment-item' )
			&& false !== stripos( serialize_block( $block ), 'EXFO' ) ) {
			fl_layout_replace_images( $block, $lang, $changed );
			continue;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			fl_layout_equipment_exfo_photo( $block['innerBlocks'], $lang, $changed );
		}
	}
	unset( $block );
}

function fl_layout_equipment( $blocks, $lang, &$changed ) {
	$intro = '';
	foreach ( $blocks as &$section ) {
		if ( ! fl_layout_has_class( $section, 'fl-subhero' ) ) {
			continue;
		}
		if ( ! fl_layout_has_class( $section, 'fl-equipment-hero' ) ) {
			fl_layout_add_class( $section, 'fl-equipment-hero' );
			$changed = true;
		}
		fl_layout_lead( $section['innerBlocks'], $lang, $changed, $intro );
		foreach ( $section['innerBlocks'] as &$shell ) {
			if ( ! fl_layout_has_class( $shell, 'fl-reading' ) ) {
				continue;
			}
			if ( ! fl_layout_find_photo( $shell['innerBlocks'], 'fl-subhero-visual' ) ) {
				fl_layout_insert_last( $shell,
					fl_layout_placeholder_block( $lang, 'fl-subhero-visual',
						fl_text( $lang, 'fotografia zaplecza technicznego', 'technical equipment' ) ) );
				$changed = true;
			}
		}
		unset( $shell );
		break;
	}
	unset( $section );
	if ( $intro ) {
		// Preserve the longer original user-written text below the concise hero.
		$sentences = preg_split( '/(?<=[.!?])\s+(?=\p{Lu})/u', $intro, -1, PREG_SPLIT_NO_EMPTY );
		$paragraphs = '';
		if ( is_array( $sentences ) && count( $sentences ) >= 3 ) {
			$mid = (int) ceil( count( $sentences ) / 2 );
			$paragraphs .= fl_paragraph( implode( ' ', array_slice( $sentences, 0, $mid ) ) );
			$paragraphs .= fl_paragraph( implode( ' ', array_slice( $sentences, $mid ) ) );
		} else {
			$paragraphs = fl_paragraph( $intro );
		}
		$details = fl_section( fl_heading( fl_text( $lang, 'Nasze zaplecze techniczne', 'Our technical approach' ) )
			. $paragraphs, 'fl-reading', 'fl-equipment-intro' );
		$inserted = parse_blocks( $details );
		array_splice( $blocks, 1, 0, $inserted );
		$changed = true;
	}
	fl_layout_equipment_exfo_photo( $blocks, $lang, $changed );
	return $blocks;
}

function fl_layout_section_has_heading( $block, $labels ) {
	if ( 'core/heading' === ( $block['blockName'] ?? '' )
		&& in_array( trim( html_entity_decode( wp_strip_all_tags( $block['innerHTML'] ?? '' ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ), $labels, true ) ) {
		return true;
	}
	foreach ( $block['innerBlocks'] ?? array() as $child ) {
		if ( fl_layout_section_has_heading( $child, $labels ) ) {
			return true;
		}
	}
	return false;
}

function fl_layout_projects( $blocks, $lang, $title, &$changed ) {
	$known = array( 'Zakres usług', 'Zakresy wdrożeń', 'Zakresy usług', 'Types of installation', 'Scope of services' );
	$blocks = array_values( array_filter( $blocks, static function ( $block ) use ( $known, &$changed ) {
		if ( fl_layout_has_class( $block, 'fl-section' ) && fl_layout_section_has_heading( $block, $known ) ) {
			$changed = true;
			return false;
		}
		return true;
	} ) );
	// Match the provided project introduction in both languages, without
	// replacing the remaining editorial text or custom project categories.
	$intro = fl_text( $lang,
		'Od 2006 roku zrealizowaliśmy setki projektów na terenie Polski i całej Europy. Jako podwykonawcy wspieramy zarówno generalnych wykonawców, dużych operatorów telekomunikacyjnych takich jak T-Mobile, Orange czy PLUS, jak i sektor MŚP oraz deweloperów. Poniżej prezentujemy wybrane obszary naszych wdrożeń.',
		'Since 2006, we have delivered hundreds of projects across Poland and Europe. As a subcontractor, we support general contractors, major telecom operators such as T-Mobile, Orange and PLUS, as well as SMEs and property developers. Explore selected areas of our work below.' );
	foreach ( $blocks as &$section ) {
		if ( ! fl_layout_has_class( $section, 'fl-subhero' ) ) {
			continue;
		}
		fl_layout_set_intro( $section['innerBlocks'], $intro, $changed );
		break;
	}
	unset( $section );
	$existing_showcase = fl_layout_find_photo( $blocks, 'fl-project-showcase' );
	$existing_gallery = fl_layout_find_photo( $blocks, 'fl-project-photos' );
	if ( ! $existing_showcase || ! $existing_gallery ) {
		$fresh = parse_blocks( fl_projects_content( $lang, $title ) );
		foreach ( $fresh as $section ) {
			if ( fl_layout_has_class( $section, 'fl-project-showcase' ) && ! $existing_showcase
				|| fl_layout_has_class( $section, 'fl-project-photos' ) && ! $existing_gallery ) {
				$blocks[] = $section;
				$changed = true;
			}
		}
	}
	return $blocks;
}

function fl_layout_set_intro( &$blocks, $intro, &$changed ) {
	foreach ( $blocks as &$block ) {
		if ( 'core/paragraph' === ( $block['blockName'] ?? '' ) && fl_layout_has_class( $block, 'fl-lead' ) ) {
			if ( trim( wp_strip_all_tags( $block['innerHTML'] ) ) !== $intro ) {
				$block = parse_blocks( fl_paragraph( $intro, 'fl-lead' ) )[0];
				$changed = true;
			}
			return;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			fl_layout_set_intro( $block['innerBlocks'], $intro, $changed );
		}
	}
	unset( $block );
}

function fl_layout_update_existing( $post, $lang, $page_key ) {
	$blocks = parse_blocks( $post->post_content );
	$changed = false;
	switch ( $page_key ) {
		case 'about':
			$blocks = fl_layout_about( $blocks, $lang, $changed );
			break;
		case 'services':
			$blocks = fl_layout_services( $blocks, $lang, $changed );
			break;
		case 'equipment':
			$blocks = fl_layout_equipment( $blocks, $lang, $changed );
			break;
		case 'projects':
			$blocks = fl_layout_projects( $blocks, $lang, $post->post_title, $changed );
			break;
	}
	return $changed ? serialize_blocks( $blocks ) : $post->post_content;
}

function fl_layout_refresh_existing_pages() {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' )
		|| ! get_option( 'fl_stage_two_ready' ) || get_option( 'fl_layout_refresh_471_complete' )
		|| ! function_exists( 'fl_photo_placeholder' )
		|| version_compare( wp_get_theme( get_template() )->get( 'Version' ), '4.7.1', '<' ) ) {
		return;
	}
	$pending = false;
	foreach ( array( 'pl', 'en' ) as $lang ) {
		foreach ( array( 'about', 'services', 'equipment', 'projects' ) as $key ) {
			$id = fl_page_id( $key, $lang );
			$post = $id ? get_post( $id ) : null;
			if ( ! $post || 'page' !== $post->post_type ) {
				continue;
			}
			if ( get_post_meta( $id, '_fl_layout_refresh_471', true ) ) {
				continue;
			}
			if ( wp_check_post_lock( $id ) ) {
				$pending = true;
				continue;
			}
			$new = fl_layout_update_existing( $post, $lang, $key );
			// An optimistic check preserves edits that landed after the read.
			if ( $new !== $post->post_content ) {
				if ( get_post_field( 'post_content', $id ) !== $post->post_content ) {
					$pending = true;
					continue;
				}
				$saved = wp_update_post( wp_slash( array(
					'ID' => $id,
					'post_content' => $new,
				) ), true );
				if ( is_wp_error( $saved ) ) {
					$pending = true;
					continue;
				}
			}
			update_post_meta( $id, '_fl_layout_refresh_471', '1' );
		}
	}
	if ( ! $pending ) {
		update_option( 'fl_layout_refresh_471_complete', true, false );
	}
}
add_action( 'admin_init', 'fl_layout_refresh_existing_pages', 30 );

/** Group legacy loose text blocks so the photograph and all copy share one grid row. */
function fl_group_story_copy( &$parent ) {
	if ( fl_layout_has_class( $parent, 'fl-about-story' ) && ! fl_layout_find_photo( $parent['innerBlocks'], 'fl-about-story-copy' ) ) {
		$photo = null;
		$copy = array();
		foreach ( $parent['innerBlocks'] as $child ) {
			if ( ! $photo && fl_layout_has_class( $child, 'fl-team-photo' ) ) {
				$photo = $child;
			} else {
				$copy[] = $child;
			}
		}
		if ( $photo && $copy ) {
			$fragments = $parent['innerContent'];
			$first = array_search( null, $fragments, true );
			$last = array_search( null, array_reverse( $fragments, true ), true );
			// Preserve wrappers and any freeform markup between the nested blocks.
			if ( false !== $first && false !== $last && '' === trim( implode( '', array_slice( $fragments, $first, $last - $first + 1 ) ) ) ) {
				$parent['innerBlocks'] = array( $photo, parse_blocks( fl_group( 'fl-about-story-copy', serialize_blocks( $copy ) ) )[0] );
				array_splice( $fragments, $first, $last - $first + 1, array( null, "\n", null ) );
				$parent['innerContent'] = $fragments;
			}
		}
	}
	foreach ( $parent['innerBlocks'] as &$child ) {
		fl_group_story_copy( $child );
	}
}

/** Target the two named service visuals, including images selected in an earlier WordPress installation. */
function fl_refresh_service_slots( &$block, $lang, $home ) {
	$labels = array(
		'Spawanie światłowodów', 'Fibre optic splicing',
		'Zastosowania światłowodów', 'Zastosowanie światłowodów', 'Fibre optic applications', 'Fibre applications', 'Applications of fibre optics',
		'Sieci komputerowe i miedziane', 'Sieci komputerowe oraz miedziane', 'Sieci komputerowe i okablowanie miedziane',
		'Copper cabling and computer networks', 'Computer and copper networks',
	);
	$class = $home ? 'fl-service-card' : 'fl-section';
	if ( fl_layout_has_class( $block, $class ) && fl_layout_section_has_heading( $block, $labels ) ) {
		fl_replace_service_slot( $block, $lang, $home ? 'fl-card-image' : 'fl-section-image' );
		return;
	}
	foreach ( $block['innerBlocks'] as &$child ) {
		fl_refresh_service_slots( $child, $lang, $home );
	}
}

function fl_replace_service_slot( &$block, $lang, $class ) {
	if ( 'core/image' === $block['blockName'] ) {
		if ( false === strpos( $block['innerHTML'], '/assets/diagrams/photo-placeholder.svg' ) ) {
			$block = fl_layout_placeholder_block( $lang, $class, fl_text( $lang, 'fotografia usługi', 'service photograph' ) );
		}
		return true;
	}
	foreach ( $block['innerBlocks'] as &$child ) {
		if ( fl_replace_service_slot( $child, $lang, $class ) ) {
			return true;
		}
	}
	return false;
}

function fl_expand_project_gallery( &$block, $lang ) {
	if ( fl_layout_has_class( $block, 'fl-project-gallery' ) ) {
		$extra = fl_gallery_extra_slots( $lang );
		foreach ( $extra as $slot ) {
			// An existing customised gallery may have fewer slots than the startup layout.
			if ( count( $block['innerBlocks'] ) >= 6 ) {
				break;
			}
			if ( false === strpos( serialize_block( $block ), trim( $slot ) ) ) {
				fl_layout_insert_last( $block, parse_blocks( $slot )[0] );
			}
		}
		return;
	}
	foreach ( $block['innerBlocks'] as &$child ) {
		fl_expand_project_gallery( $child, $lang );
	}
}

/** Update named sections, including installations that have already run the 4.7 migration. */
function fl_refresh_portfolio_content( $post, $lang, $key ) {
	$content = $post->post_content;
	$blocks = parse_blocks( $content );
	if ( in_array( $key, array( 'home', 'services' ), true ) ) {
		foreach ( $blocks as &$block ) {
			fl_refresh_service_slots( $block, $lang, 'home' === $key );
		}
		unset( $block );
		$content = serialize_blocks( $blocks );
	}
	if ( 'about' === $key ) {
		if ( ! fl_layout_find_photo( $blocks, 'fl-about-story' ) ) {
			array_splice( $blocks, 1, 0, parse_blocks( fl_about_story( $lang ) ) );
		} else {
			foreach ( $blocks as &$block ) {
				fl_group_story_copy( $block );
			}
			unset( $block );
		}
		// Update only the two verbatim starter paragraphs. Preserve client-authored copy.
		$paragraphs = array_map( 'fl_paragraph', fl_about_paragraphs( $lang ) );
		$old_intro = fl_text( $lang,
			'Od 2006 roku projektujemy, instalujemy i mierzymy infrastrukturę światłowodową w Polsce i za granicą. Nasz zespół pracuje przy wdrożeniach operatorskich, przemysłowych i centrach danych.',
			'Since 2006 our team has installed and tested fibre optic infrastructure in Poland and abroad, supporting telecom operators, industrial facilities and data centres.' );
		$old_work = fl_text( $lang,
			'Realizujemy również modernizacje działających sieci, planując prace tak, aby ograniczyć przestoje.',
			'We also upgrade live networks, planning our work to minimise downtime.' );
		return str_replace( array( trim( fl_paragraph( $old_intro ) ), trim( fl_paragraph( $old_work ) ) ),
			array_map( 'trim', $paragraphs ), serialize_blocks( $blocks ) );
	}
	if ( 'report' === $key && ! fl_layout_find_photo( $blocks, 'fl-report-gallery' ) ) {
		return $content . fl_report_gallery( $lang );
	}
	if ( 'projects' === $key ) {
		if ( ! fl_layout_find_photo( $blocks, 'fl-project-operators' ) ) {
			foreach ( $blocks as $index => $block ) {
				if ( has_block( 'fiberlan/projects', serialize_block( $block ) ) ) {
					array_splice( $blocks, $index + 1, 0, parse_blocks( fl_operator_section( $lang ) ) );
					break;
				}
			}
			if ( ! fl_layout_find_photo( $blocks, 'fl-project-operators' ) ) {
				$blocks[] = parse_blocks( fl_operator_section( $lang ) )[0];
			}
		}
		foreach ( $blocks as &$block ) {
			fl_expand_project_gallery( $block, $lang );
		}
		unset( $block );
		$content = serialize_blocks( $blocks );
		$editor_note = fl_text( $lang, 'Miejsce na fotografie z kolejnych projektów. Zdjęcia można zastąpić w edytorze Gutenberg.', 'Space for photographs from upcoming projects. Replace these images in the Gutenberg editor.' );
		return str_replace( trim( fl_paragraph( $editor_note ) ), '', $content );
	}
	if ( 'services' === $key ) {
		$labels = array( 'Testery okablowania Fluke Networks', 'Fluke Networks cable testers', 'Fluke Networks cabling testers' );
		foreach ( $blocks as $index => $section ) {
			if ( ! fl_layout_section_has_heading( $section, $labels ) ) {
				continue;
			}
			$photo = fl_equipment_visual( 'fluke-networks-versiv-2-client-photo.jpg', $lang );
			fl_restore_fluke_image( $blocks[ $index ], $photo );
			return serialize_blocks( $blocks );
		}
		return $content . fl_fluke_service( $lang );
	}
	return $content;
}

function fl_restore_fluke_image( &$block, $photo ) {
	if ( 'core/image' === $block['blockName'] && false !== strpos( $block['innerHTML'], '/assets/diagrams/photo-placeholder.svg' ) ) {
		$block = parse_blocks( $photo )[0];
		return true;
	}
	foreach ( $block['innerBlocks'] as &$child ) {
		if ( fl_restore_fluke_image( $child, $photo ) ) {
			return true;
		}
	}
	return false;
}

function fl_refresh_portfolio_pages() {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ! get_option( 'fl_layout_refresh_471_complete' )
		|| get_option( 'fl_portfolio_490_complete' ) || ! function_exists( 'fiberlan_image_block' )
		|| version_compare( wp_get_theme( get_template() )->get( 'Version' ), '4.9.0', '<' ) ) {
		return;
	}
	$pending = false;
	foreach ( array( 'pl', 'en' ) as $lang ) {
		$pages = array();
		foreach ( array( 'home', 'about', 'services', 'projects' ) as $key ) {
			$id = fl_page_id( $key, $lang );
			if ( $id ) {
				$pages[ $id ] = $key;
			}
		}
		$archive = fl_page_id( 'projects', $lang );
		if ( $archive ) {
			$reports = get_posts( array( 'post_type' => 'page', 'post_parent' => $archive, 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future' ), 'fields' => 'ids', 'numberposts' => -1 ) );
			foreach ( $reports as $id ) {
				$pages[ $id ] = 'report';
			}
		}
		foreach ( $pages as $id => $key ) {
			if ( get_post_meta( $id, '_fl_portfolio_490', true ) ) {
				continue;
			}
			$post = get_post( $id );
			if ( ! $post ) {
				continue;
			}
			if ( wp_check_post_lock( $id ) ) {
				$pending = true;
				continue;
			}
			$content = fl_refresh_portfolio_content( $post, $lang, $key );
			if ( $content !== $post->post_content && ( get_post_field( 'post_content', $id ) !== $post->post_content
				|| is_wp_error( wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true ) ) ) ) {
				$pending = true;
				continue;
			}
			update_post_meta( $id, '_fl_portfolio_490', '1' );
		}
	}
	if ( ! $pending ) {
		update_option( 'fl_portfolio_490_complete', true, false );
	}
}
add_action( 'admin_init', 'fl_refresh_portfolio_pages', 35 );
