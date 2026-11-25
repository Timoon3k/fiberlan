<?php
/** Bring the archived English edition up to the Polish editor's published copy. */
defined( 'ABSPATH' ) || exit;

function fl_client_experience_en() {
	$team = fl_heading( 'Direct contact with the experts' ) . fl_paragraph( 'The founders, Andrzej and Norbert, supervise every project personally. From the first discussions to handover, clients work directly with the people making the technical decisions.' );
	$team .= fl_group( 'fl-open-info', fl_heading( '24/7 support across Poland', 3 ) . fl_paragraph( 'Network outages interrupt business. We offer round-the-clock technical support and rapid response throughout Poland and support clients during contracts elsewhere in Europe.' ) );
	$team .= fl_group( 'fl-open-info', fl_heading( 'Quality verified by testing', 3 ) . fl_paragraph( 'After splicing, we measure every network section and provide complete as-built records accepted by most structured cabling component suppliers.' ) );
	$years = fl_group( 'fl-milestone', fl_paragraph( '20', 'fl-milestone-number' ) . fl_paragraph( 'Years in the field', 'fl-milestone-caption' ) . fl_paragraph( '2006–2026', 'fl-timeline' ) );
	return fl_section( $years . fl_group( 'fl-ribbon-copy', $team ), 'fl-split', 'fl-ribbon' );
}

function fl_client_services_en() {
	$sections = array( fl_hero( 'en', 'FIBERLAN SC', 'Services', 'We design, build and diagnose optical networks. We combine precision installation with transmission testing to deliver infrastructure for high bandwidth and reliable long-term operation.' ) );
	$services = array(
		array( 'spawanie', 'Single-mode and multimode fibre splicing', 'We splice fibres in subscriber connections, FTTx networks and carrier backbones, from small server rooms to advanced data centre installations. We keep splice loss low, organise panels and closures carefully, and meet the operator’s installation standards.' ),
		array( 'pomiary', 'OTDR testing and transmission analysis', 'Using EXFO optical time-domain reflectometers, we locate small faults, measure end-to-end loss and certify optical links. We provide the test reports and as-built records required for technical acceptance by network operators.' ),
		array( 'instalacje', 'Server room fit-outs and equipment racks', 'We fit out technical facilities for offices, industrial buildings and property developments. We install and patch equipment racks, organise structured fibre and copper cabling and commission distribution points.' ),
		array( 'data-center', 'MPO/MTP systems and ribbon splicing', 'We specialise in high-density fibre installations for data centres. Our team was an early adopter of ribbon splicing and MPO systems in Poland. We use mass fusion splicing and MPO/MTP connections to keep cabling manageable and leave room for expansion.' ),
		array( 'serwis', '24/7 fibre fault repair', 'Our emergency fibre service covers the whole of Poland around the clock. We use OTDR measurements to locate cable breaks and restore data transmission quickly to minimise downtime.' ),
	);
	foreach ( $services as $index => list( $anchor, $title, $description ) ) {
		$copy = fl_group( 'fl-split-copy', fl_heading( $title ) . fl_paragraph( $description ) . fl_contact_cta( 'en' ) );
		$sections[] = fl_section( $copy . fl_photo_placeholder( 'en', 'fl-section-image', $title ), 'fl-split', $index % 2 ? 'fl-alternate' : '', $anchor );
	}
	return $sections;
}

function fl_client_copper_service( $lang ) {
	$title = fl_text( $lang, 'Sieci komputerowe i miedziane', 'Copper cabling and computer networks' );
	$copy = fl_heading( $title ) . fl_paragraph( fl_text( $lang,
		'Układamy okablowanie strukturalne, opisujemy gniazda i porty oraz badamy tory miedziane analizatorami Fluke Networks. Zakres certyfikacji dobieramy do kategorii instalacji i wymagań odbioru.',
		'We install structured cabling, label outlets and ports, and test copper links using Fluke Networks analysers. We select the certification tests to match the installation category and acceptance requirements.' ) );
	return fl_section( fl_group( 'fl-split-copy', $copy . fl_contact_cta( $lang ) ) . fl_photo_placeholder( $lang, 'fl-section-image', $title ), 'fl-split', 'fl-alternate', 'lan' );
}

function fl_client_equipment_en() {
	$hero = fl_hero( 'en', 'FIBERLAN SC', 'Equipment', 'We use certified Fujikura, Fluke Networks, EXFO and VIAVI equipment to deliver consistent splices and reliable measurements.',
		fl_photo_placeholder( 'en', 'fl-subhero-visual', 'technical equipment' ), 'fl-equipment-hero' );
	$intro = fl_section( fl_heading( 'Our test and installation equipment' )
		. fl_paragraph( 'In fibre and structured cabling, precise installation and testing affect transmission quality for years. We use Fujikura splicers and Fluke Networks, EXFO and VIAVI instruments to check the links we build.' )
		. fl_paragraph( 'We select certified instruments and have them calibrated regularly. Consistent measurements at handover help avoid repairs after the network goes into service.' ), 'fl-reading', 'fl-equipment-intro' );
	$items = array(
		array( 'fiber-splicer-cleaver-client-photo.jpg', 'Fujikura single-fibre and ribbon fusion splicers', 'We have four Fujikura FSM-62S and FSM-70S splicers. We use S-series single-fibre units for conventional links and carrier backbones, and R-series ribbon splicers to join several fibres at once in high-density cloud and data centre networks. Both methods give repeatable, low-loss connections.', 'spawanie' ),
		array( 'fluke-networks-versiv-2-client-photo.jpg', 'Fluke Networks cable testers', 'Our two Fluke Networks analysers certify copper cabling and test optical links at Tier 1. We measure length and loss and supply acceptance reports. The DSX-5000 certifies copper links. The photograph shows the Versiv 2 main and remote units with the FiberInspector Pro inspection screen.', 'lan' ),
		array( 'viavi-mpolx-client-photo.jpg', 'VIAVI SmartClass Fiber MPOLx multi-fibre testers', 'Our two VIAVI instruments test single-mode and multimode cabling through MPO ports. We verify polarity, connector cleanliness and transmission performance across multi-fibre MPO/MTP links in high-density data centres.', 'data-center' ),
		array( '', 'EXFO optical time-domain reflectometers', 'We use three EXFO reflectometers at 1310, 1550 and 1625 nm. Tier 2 testing measures splice and connector loss and pinpoints faults along long carrier routes, including projects where the general contractor requires detailed OTDR records.', 'pomiary' ),
	);
	$catalogue = '';
	foreach ( $items as list( $file, $title, $description, $anchor ) ) {
		$image = $file ? fl_equipment_visual( $file, 'en' ) : fl_photo_placeholder( 'en', 'fl-section-image', $title );
		$copy = fl_heading( $title ) . fl_paragraph( $description ) . fl_button( 'View related service', fl_url( 'services', 'en' ) . '#' . $anchor );
		$catalogue .= fl_group( 'fl-equipment-item', $image . fl_group( 'fl-equipment-copy', $copy ), 'article' );
	}
	return array( $hero, $intro, fl_section( $catalogue, 'fl-equipment' ) );
}

function fl_archived_section_signature( $block ) {
	// Ignore formatting between tags and local media IDs. Retain editorial text, links, filenames and custom styles.
	$markup = str_replace( untrailingslashit( home_url() ), '', serialize_block( $block ) );
	$markup = preg_replace( array( '~>\s+<~', '~wp-image-\d+~', '~"id":\d+~', '~/uploads/\d{4}/\d{2}/~' ), array( '><', 'wp-image-ID', '"id":0', '/uploads/' ), $markup );
	return substr( hash( 'sha256', $markup ), 0, 20 );
}

/** Map only the outdated sections found in the supplied backup. Later editorial changes take precedence. */
function fl_archived_english_sections( $key, $blocks ) {
	if ( 'about' === $key ) {
		$sections = array(
			'46d254970586cdef1a5a' => fl_client_experience_en(),
			'4ac550058f314731f1f0' => '', // The supplied Polish page removed the old sector list.
		);
	} elseif ( 'services' === $key ) {
		$sections = array_combine(
			array( 'dd9547518ae57556b068', '42757f49e571fad19d64', 'e8bea6d17becbe026b43', '03df52e17913b88fa409', 'e2f585ff8ab4c08ddbf5', '164016709f9b12fc4a3b' ),
			fl_client_services_en()
		);
	} else {
		list( $hero, $intro, $inventory ) = fl_client_equipment_en();
		$sections = array( 'd8bc268c3c7085f46146' => $hero . $intro, 'f9db7fbc576e5b1c1204' => $inventory );
	}
	$content = '';
	foreach ( $blocks as $block ) {
		$content .= $sections[ fl_archived_section_signature( $block ) ] ?? serialize_block( $block );
	}
	return $content;
}

function fl_fix_archived_service_anchors( &$block ) {
	$targets = array(
		'instalacje'   => array( 'Badania OTDR i analizy transmisyjne', 'pomiary' ),
		'pomiary'      => array( 'Wyposażenie serwerowni i systemy szaf RACK', 'instalacje' ),
		'lan'          => array( 'Technologie MPO/MTP oraz spawy taśmowe (Ribbon)', 'data-center' ),
		'data-center'  => array( 'Serwis i usuwanie awarii 24/7', 'serwis' ),
	);
	$old = $block['attrs']['anchor'] ?? '';
	if ( isset( $targets[ $old ] ) && fl_layout_section_has_heading( $block, array( $targets[ $old ][0] ) ) ) {
		$new = $targets[ $old ][1];
		$block['attrs']['anchor'] = $new;
		$block['innerContent'][0] = str_replace( 'id="' . $old . '"', 'id="' . $new . '"', $block['innerContent'][0] );
	}
}

function fl_fix_archived_equipment_links( &$block ) {
	$targets = array(
		'Testery okablowania Fluke Networks' => array( 'instalacje', 'lan' ),
		'Mierniki wielowłóknowe Viavi (MPO/MTP)' => array( 'pomiary', 'data-center' ),
		'Reflektometry optyczne (OTDR) marki EXFO' => array( 'spawanie', 'pomiary' ),
	);
	if ( fl_layout_has_class( $block, 'fl-equipment-item' ) ) {
		foreach ( $targets as $title => list( $old, $new ) ) {
			if ( fl_layout_section_has_heading( $block, array( $title ) ) ) {
				$base = esc_url( fl_url( 'services', 'pl' ) );
				$block = parse_blocks( str_replace( 'href="' . $base . '#' . $old . '"', 'href="' . $base . '#' . $new . '"', serialize_block( $block ) ) )[0];
				break;
			}
		}
		return;
	}
	foreach ( $block['innerBlocks'] as &$child ) {
		fl_fix_archived_equipment_links( $child );
	}
}

function fl_group_saved_review_widget( &$section ) {
	if ( ! fl_layout_has_class( $section, 'fl-reviews' ) || fl_layout_find_photo( $section['innerBlocks'], 'fl-reviews-widget' ) ) {
		return;
	}
	foreach ( $section['innerBlocks'] as &$layout ) {
		if ( ! fl_layout_has_class( $layout, 'fl-reviews-layout' ) ) {
			continue;
		}
		foreach ( $layout['innerBlocks'] as &$intro ) {
			if ( ! fl_layout_has_class( $intro, 'fl-reviews-intro' ) ) {
				continue;
			}
			foreach ( $intro['innerBlocks'] as $index => $child ) {
				if ( 'trustindex/block-selector' !== $child['blockName'] ) {
					continue;
				}
				$slots = array_keys( $intro['innerContent'], null, true );
				array_splice( $intro['innerContent'], $slots[ $index ], 1 );
				array_splice( $intro['innerBlocks'], $index, 1 );
				$widget = fl_group( 'fl-reviews-widget', serialize_block( $child ) );
				fl_layout_insert_last( $layout, parse_blocks( $widget )[0] );
				return;
			}
		}
	}
}

function fl_refresh_client_copy( $post, $lang, $key ) {
	$content = $post->post_content;
	$blocks = parse_blocks( $content );
	if ( 'home' === $key ) {
		$labels = array( 'frankfurt' => array( 'Centrum danych', 'Data centre' ), 'bordeaux' => array( 'Fabryka szkła', 'Glass factory' ), 'marine' => array( 'Statki wycieczkowe', 'Cruise ships' ) );
		foreach ( $blocks as &$block ) {
			fl_group_saved_review_widget( $block );
			if ( ! fl_layout_has_class( $block, 'fl-projects' ) ) {
				continue;
			}
			$html = serialize_block( $block );
			foreach ( $labels as $project => $names ) {
				$label = esc_html( fl_text( $lang, ...$names ) );
				$url = fl_url( $project, $lang );
				if ( $url ) {
					$html = str_replace( '<h3 class="wp-block-heading">' . $label . '</h3>', '<h3 class="wp-block-heading"><a href="' . esc_url( $url ) . '">' . $label . '</a></h3>', $html );
				}
			}
			$block = parse_blocks( $html )[0];
		}
		unset( $block );
		return serialize_blocks( $blocks );
	}
	$markers = array(
		'about' => 'Bezpośredni kontakt z ekspertami',
		'services' => 'Spawanie światłowodów (Singlemode / Multimode)',
		'equipment' => 'Testery okablowania Fluke Networks',
	);
	$polish = 'pl' === $lang ? $content : get_post_field( 'post_content', fl_page_id( $key, 'pl' ) );
	if ( ! isset( $markers[ $key ] ) || false === strpos( $polish, $markers[ $key ] ) ) {
		return $content;
	}
	if ( 'en' === $lang ) {
		$content = fl_archived_english_sections( $key, $blocks );
	} elseif ( 'services' === $key ) {
		foreach ( $blocks as &$block ) {
			fl_fix_archived_service_anchors( $block );
		}
		unset( $block );
		$content = serialize_blocks( $blocks );
	} elseif ( 'equipment' === $key ) {
		foreach ( $blocks as &$block ) {
			fl_fix_archived_equipment_links( $block );
		}
		unset( $block );
		$content = serialize_blocks( $blocks );
	} elseif ( 'about' === $key ) {
		$blocks = array_filter( $blocks, static function ( $block ) {
			return '81279ea3667e6efa670f' !== fl_archived_section_signature( $block );
		} );
		$content = serialize_blocks( $blocks );
	}
	if ( 'services' === $key ) {
		// An editor may already have assigned one of the new service anchors to a different section.
		$anchors = array();
		foreach ( parse_blocks( $content ) as $section ) {
			$anchor = $section['attrs']['anchor'] ?? '';
			if ( $anchor && isset( $anchors[ $anchor ] ) ) {
				return $post->post_content;
			}
			$anchors[ $anchor ] = true;
		}
	}
	if ( 'services' === $key && ! isset( $anchors['lan'] ) ) {
		$copper = fl_client_copper_service( $lang );
		$content .= $copper;
	}
	return $content;
}

function fl_update_client_copy() {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ! get_option( 'fl_portfolio_490_complete' )
		|| get_option( 'fl_copy_491_complete' ) || ! function_exists( 'fiberlan_image_block' )
		|| version_compare( wp_get_theme( get_template() )->get( 'Version' ), '4.9.1', '<' ) ) {
		return;
	}
	$complete = true;
	foreach ( array( 'pl', 'en' ) as $lang ) {
		foreach ( array( 'home', 'about', 'services', 'equipment' ) as $key ) {
			$id = fl_page_id( $key, $lang );
			if ( $id && ! fl_save_managed_page( $id, $lang, $key, '_fl_copy_491', 'fl_refresh_client_copy' ) ) {
				$complete = false;
			}
		}
	}
	if ( $complete ) {
		update_option( 'fl_copy_491_complete', true, false );
	}
}
add_action( 'admin_init', 'fl_update_client_copy', 40 );
