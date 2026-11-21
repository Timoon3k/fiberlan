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
			&& ! fl_layout_has_class( $child, 'fl-photo-placeholder' ) ) {
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
	return $blocks;
}

function fl_layout_section_has_heading( $block, $labels ) {
	if ( 'core/heading' === ( $block['blockName'] ?? '' )
		&& in_array( trim( wp_strip_all_tags( $block['innerHTML'] ?? '' ) ), $labels, true ) ) {
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
