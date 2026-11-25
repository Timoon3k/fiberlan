<?php
defined( 'ABSPATH' ) || exit;

function fl_pages_catalogue() {
	return array(
		'home'     => array( 'strona-glowna', 'Strona główna', 'en', 'Home' ),
		'about'    => array( 'o-nas', 'O nas', 'about', 'About' ),
		'services' => array( 'uslugi', 'Usługi', 'services', 'Services' ),
		'equipment'=> array( 'sprzet', 'Sprzęt', 'equipment', 'Equipment' ),
		'projects' => array( 'realizacje', 'Realizacje', 'projects', 'Projects' ),
		'contact'  => array( 'kontakt', 'Kontakt', 'contact', 'Contact' ),
		'privacy'  => array( 'polityka-prywatnosci', 'Polityka prywatności', 'privacy-policy', 'Privacy policy' ),
		'cookies'  => array( 'polityka-cookies', 'Polityka cookies', 'cookie-policy', 'Cookie policy' ),
	);
}

function fl_create_page( $slug, $title, $parent, $lang, $key ) {
	$path = $parent ? get_page_uri( $parent ) . '/' . $slug : $slug;
	$existing = get_page_by_path( $path );
	if ( $existing ) {
		return get_post_meta( $existing->ID, '_fl_page_key', true ) === $key
			? $existing->ID
			: new WP_Error( 'fl_path_exists', 'Zachowano istniejącą stronę. Adres jest już zajęty: ' . $path );
	}
	return wp_insert_post( wp_slash( array(
		'post_type'   => 'page',
		'post_status' => 'draft',
		'post_name'   => $slug,
		'post_title'  => $title,
		'post_parent' => $parent,
		'meta_input'  => array(
			'_fl_page_key' => $key,
			'_wp_page_template' => 'en' === $lang ? 'page-en' : 'page-fiberlan',
		),
	) ), true );
}

function fl_seed_terms() {
	$labels = array(
		'data-center' => array( 'Centra danych', 'Data centres' ),
		'industry' => array( 'Przemysł', 'Industry' ),
		'telecom' => array( 'Instalacje telekomunikacyjne', 'Telecommunications' ),
		'marine' => array( 'Statki i stocznie', 'Ships and shipyards' ),
		'international' => array( 'Realizacje zagraniczne', 'International projects' ),
	);
	foreach ( $labels as $key => list( $pl, $en ) ) {
		foreach ( array( 'pl' => $pl, 'en' => $en ) as $lang => $label ) {
			if ( ! term_exists( $lang . '-' . $key, 'fl_sector' ) ) {
				$result = wp_insert_term( $label, 'fl_sector', array( 'slug' => $lang . '-' . $key ) );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}
	}
	return true;
}

function fl_install_stage_two() {
	if ( ! function_exists( 'fiberlan_install_starter' ) || ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'fl_theme', 'Wymagany jest aktywny motyw FIBERLAN i uprawnienia administratora.' );
	}
	if ( get_option( 'fl_stage_two_ready' ) ) {
		return true;
	}
	if ( ! add_option( 'fl_stage_two_installing', time(), '', false ) ) {
		return new WP_Error( 'fl_busy', 'Instalacja jest w toku. Spróbuj ponownie po zakończeniu.' );
	}
	try {
		return fl_seed_website();
	} finally {
		delete_option( 'fl_stage_two_installing' );
	}
}

function fl_seed_website() {
	$home = fiberlan_install_starter();
	if ( is_wp_error( $home ) ) {
		return $home;
	}
	$ids = array( 'pl' => array( 'home' => $home ), 'en' => array() );
	update_post_meta( $home, '_fl_page_key', 'home' );
	$catalogue = fl_pages_catalogue();

	foreach ( array( 'pl', 'en' ) as $lang ) {
		foreach ( $catalogue as $key => list( $slug_pl, $title_pl, $slug_en, $title_en ) ) {
			if ( 'home' === $key && 'pl' === $lang ) {
				continue;
			}
			$id = fl_create_page( fl_text( $lang, $slug_pl, $slug_en ), fl_text( $lang, $title_pl, $title_en ), 'en' === $lang ? ( $ids['en']['home'] ?? 0 ) : 0, $lang, $key );
			if ( is_wp_error( $id ) ) {
				return $id;
			}
			$ids[ $lang ][ $key ] = $id;
		}
		$parent = $ids[ $lang ]['projects'];
		foreach ( fl_project_examples() as $key => $project ) {
			$id = fl_create_page( $key, fl_text( $lang, $project[2], $project[3] ), $parent, $lang, $key );
			if ( is_wp_error( $id ) ) {
				return $id;
			}
			$ids[ $lang ][ $key ] = $id;
		}
	}

	$terms = fl_seed_terms();
	if ( is_wp_error( $terms ) ) {
		return $terms;
	}
	update_option( 'fl_site_pages', $ids );
	$GLOBALS['fl_building_site'] = true;
	$pages = array(
		'home' => 'fl_home_content',
		'about' => 'fl_about_content',
		'services' => 'fl_services_content',
		'equipment' => 'fl_equipment_content',
		'projects' => 'fl_projects_content',
		'contact' => 'fl_contact_content',
		'privacy' => 'fl_legal_content',
		'cookies' => 'fl_legal_content',
	);
	try {
		foreach ( $ids as $lang => $translations ) {
			foreach ( $translations as $key => $id ) {
				$post = get_post( $id );
				if ( 'publish' === $post->post_status && ( 'home' !== $key || 'en' === $lang ) ) {
					continue;
				}
				$save = array( 'ID' => $id, 'post_status' => 'publish' );
				if ( isset( $pages[ $key ] ) ) {
					$save['post_content'] = 'home' === $key
						? fl_home_content( $lang, 'pl' === $lang ? $post->post_content : '' )
						: $pages[ $key ]( $lang, $post->post_title, $key );
				} else {
					list( $image, $sectors, , , $loc_pl, $loc_en, $desc_pl, $desc_en ) = fl_project_examples()[ $key ];
					$place = fl_text( $lang, $loc_pl, $loc_en );
					$desc = fl_text( $lang, $desc_pl, $desc_en );
					$save['post_excerpt'] = $place;
					$save['post_content'] = fl_diagram( $image, 'fl-case-illustration' )
						. fl_hero( $lang, $place, $post->post_title, $desc )
						. fl_report_gallery( $lang )
						. fl_section( fl_button( fl_text( $lang, 'Wszystkie realizacje', 'All projects' ), fl_url( 'projects', $lang ) ) );
					foreach ( $sectors as $sector ) {
						wp_set_object_terms( $id, $lang . '-' . $sector, 'fl_sector', true );
					}
					$industry = get_term_by( 'slug', $lang . '-' . $sectors[0], 'fl_sector' );
					if ( $industry ) {
						wp_set_object_terms( $id, $industry->name, 'fl_industry' );
					}
				}
				$saved = wp_update_post( wp_slash( $save ), true );
				if ( is_wp_error( $saved ) ) {
					return $saved;
				}
			}
		}
	} finally {
		unset( $GLOBALS['fl_building_site'] );
	}
	update_option( 'wp_page_for_privacy_policy', $ids['pl']['privacy'] );
	update_option( 'fl_stage_two_ready', true );
	return true;
}

// Make the client copy available in Gutenberg on sites that already have edited pages.
add_action( 'init', static function () {
	if ( ! current_user_can( 'edit_pages' ) || ! get_option( 'fl_stage_two_ready' ) || ! function_exists( 'fiberlan_image_block' ) ) {
		return;
	}
	foreach ( array( 'about', 'services', 'equipment', 'projects' ) as $key ) {
		foreach ( array( 'pl', 'en' ) as $lang ) {
			$id = fl_page_id( $key, $lang );
			if ( ! $id ) {
				continue;
			}
			$title = get_the_title( $id );
			$build = 'fl_' . $key . '_content';
			register_block_pattern( 'fiberlan/client-' . $key . '-' . $lang, array(
				'title'      => sprintf( 'FIBERLAN · %s (%s)', $title, strtoupper( $lang ) ),
				'categories' => array( 'fiberlan' ),
				'postTypes'  => array( 'page' ),
				'content'    => $build( $lang, $title ),
			) );
		}
	}
} );

add_action( 'admin_menu', static function () {
	add_theme_page( 'FIBERLAN PL/EN', 'FIBERLAN PL/EN', 'manage_options', 'fiberlan-site', static function () {
		?>
		<div class="wrap">
			<h1>FIBERLAN PL/EN</h1>
			<p>Instalator przygotowuje strony, realizacje i formularze w dwóch językach. Na istniejącej stronie głównej aktualizuje domyślne adresy przycisków i dodaje sekcję Ribbon.</p>
			<?php if ( get_option( 'fl_stage_two_ready' ) ) : ?>
				<p>Strony są gotowe do edycji.</p>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="fl_install_stage_two">
					<?php wp_nonce_field( 'fl_install_stage_two' ); ?>
					<?php submit_button( 'Przygotuj strony PL i EN' ); ?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	} );
} );

add_action( 'admin_post_fl_install_stage_two', static function () {
	check_admin_referer( 'fl_install_stage_two' );
	$result = fl_install_stage_two();
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ), 'FIBERLAN PL/EN', array( 'back_link' => true ) );
	}
	wp_safe_redirect( admin_url( 'admin.php?page=fiberlan-manager' ) );
	exit;
} );

add_filter( 'wp_robots', static function ( $robots ) {
	if ( in_array( get_post_meta( get_queried_object_id(), '_fl_page_key', true ), array( 'privacy', 'cookies' ), true ) && ! get_option( 'fl_legal_approved' ) ) {
		$robots['noindex'] = true;
	}
	return $robots;
} );

add_filter( 'wp_sitemaps_posts_query_args', static function ( $args, $post_type ) {
	if ( 'page' === $post_type && ! get_option( 'fl_legal_approved' ) ) {
		$ids = $args['post__not_in'] ?? array();
		foreach ( array( 'pl', 'en' ) as $lang ) {
			$ids[] = fl_page_id( 'privacy', $lang );
			$ids[] = fl_page_id( 'cookies', $lang );
		}
		$args['post__not_in'] = array_filter( array_unique( $ids ) );
	}
	return $args;
}, 10, 2 );

// Replace the original homepage invitation on existing sites without overwriting edited blocks.
function fl_update_home_reviews() {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ! get_option( 'fl_stage_two_ready' ) || get_option( 'fl_home_reviews_updated' ) ) {
		return;
	}
	$registry = WP_Block_Patterns_Registry::get_instance();
	$legacy = $registry->get_registered( 'fiberlan/home-contact' )['content'] ?? '';
	if ( ! $legacy || ! fl_home_reviews_content( 'pl' ) ) {
		return;
	}

	$stock = preg_replace( '~>\s+<~', '><', trim( $legacy ) );
	$mailto = 'mailto:info@fiberlan.pl?subject=Projekt%20instalacji%20%C5%9Bwiat%C5%82owodowej';
	$complete = true;
	foreach ( array( 'pl', 'en' ) as $lang ) {
		$post = get_post( fl_page_id( 'home', $lang ) ?: -1 );
		if ( ! $post ) {
			continue;
		}
		$reference = 'en' === $lang ? strtr( $stock, array(
			'Szukasz sprawdzonego partnera do realizacji instalacji światłowodowej?' => 'Looking for a partner for your fibre optic installation?',
			'Porozmawiajmy o projekcie' => 'Discuss your project',
		) ) : $stock;
		$references = array( $reference, str_replace( $mailto, esc_url( fl_url( 'contact', $lang ) ), $reference ) );
		foreach ( parse_blocks( $post->post_content ) as $block ) {
			if ( 'fl-contact' !== ( $block['attrs']['className'] ?? '' ) ) {
				continue;
			}
			$html = serialize_block( $block );
			if ( ! in_array( preg_replace( '~>\s+<~', '><', $html ), $references, true ) ) {
				continue;
			}
			// Leave the current editing session alone and retry on a later admin request.
			if ( wp_check_post_lock( $post->ID ) || get_post_field( 'post_content', $post->ID ) !== $post->post_content ) {
				$complete = false;
				break;
			}
			$content = str_replace( $html, trim( fl_home_reviews_content( $lang ) ), $post->post_content, $count );
			if ( $count && is_wp_error( wp_update_post( wp_slash( array( 'ID' => $post->ID, 'post_content' => $content ) ), true ) ) ) {
				$complete = false;
			}
			break;
		}
	}
	if ( $complete ) {
		update_option( 'fl_home_reviews_updated', true, false );
	}
}
add_action( 'admin_init', 'fl_update_home_reviews' );

// Bring the original photos and shared contact details up to date in already installed content.
function fl_update_equipment_details() {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ! get_option( 'fl_stage_two_ready' ) || get_option( 'fl_equipment_details_updated' ) ) {
		return;
	}

	$replacements = array(
		'pl' => array(
			'Spawarka światłowodowa z otwartą osłoną i włączonym ekranem.' => 'Spawarka światłowodowa Fujikura FSM-62S z otwartą osłoną i włączonym ekranem.',
			'Spawarka światłowodowa z obcinarką i akcesoriami' => 'Spawarka światłowodowa Fujikura FSM-62S z obcinarką i akcesoriami',
			'Dwa mierniki VIAVI z torbą transportową.' => 'Dwa mierniki VIAVI SmartClass Fiber MPOLx z torbą transportową.',
			'Dwa mierniki VIAVI MPOLx obok torby transportowej' => 'Dwa mierniki VIAVI SmartClass Fiber MPOLx obok torby transportowej',
			'>Mierniki VIAVI MPOLx</h2>' => '>VIAVI SmartClass Fiber MPOLx</h2>',
			'>Dwoma analizatorami Fluke Networks certyfikujemy okablowanie miedziane oraz tory optyczne w zakresie Tier 1. Mierzymy długość i tłumienie, sprawdzamy stan złączy i przygotowujemy raporty odbiorowe.</p>' => '>Dwoma analizatorami Fluke Networks certyfikujemy okablowanie miedziane oraz tory optyczne w zakresie Tier 1. Mierzymy długość i tłumienie, sprawdzamy stan złączy i przygotowujemy raporty odbiorowe. DSX-5000 służy do certyfikacji okablowania miedzianego, a zdjęcie przedstawia platformę Versiv 2 z ekranem FiberInspector Pro.</p>',
		),
		'en' => array(
			'Fibre optic fusion splicer with its cover open and display lit.' => 'Fujikura FSM-62S fusion splicer with its cover open and display lit.',
			'Fusion splicer with a fibre cleaver and accessories' => 'Fujikura FSM-62S fusion splicer with a fibre cleaver and accessories',
			'Two VIAVI testers and their carrying case.' => 'Two VIAVI SmartClass Fiber MPOLx testers and their carrying case.',
			'Two VIAVI MPOLx instruments beside their carrying case' => 'Two VIAVI SmartClass Fiber MPOLx instruments beside their carrying case',
			'>VIAVI MPOLx test sets</h2>' => '>VIAVI SmartClass Fiber MPOLx</h2>',
			'>Our two Fluke Networks analysers certify copper cabling and perform Tier 1 optical testing. We measure link length and loss, inspect connectors and prepare acceptance reports.</p>' => '>Our two Fluke Networks analysers certify copper cabling and perform Tier 1 optical testing. We measure link length and loss, inspect connectors and prepare acceptance reports. The DSX-5000 certifies copper cabling. The photo shows the Versiv 2 platform displaying FiberInspector Pro.</p>',
		),
	);
	$updates = array();
	foreach ( $replacements as $lang => $copy ) {
		foreach ( array( 'home', 'services', 'equipment' ) as $page ) {
			$id = fl_page_id( $page, $lang );
			if ( $id ) {
				$updates[ $id ] = $copy;
			}
		}
		$equipment = fl_page_id( 'equipment', $lang );
		if ( $equipment ) {
			$updates[ $equipment ]['>Fluke Networks Versiv 2</h2>'] = '>Fluke Networks DSX-5000</h2>';
		}
	}
	$contact = absint( get_option( 'fiberlan_contact_block_id' ) );
	if ( $contact && 'wp_block' === get_post_type( $contact ) ) {
		$updates[ $contact ] = array(
			'href="tel:+48662211200">+48 662 211 200</a>' => 'href="tel:+48662211200">+48 66 22 11 200</a>',
		);
	}
	$complete = true;
	foreach ( $updates as $id => $copy ) {
		$original = get_post_field( 'post_content', $id );
		$content = strtr( $original, $copy );
		if ( $content === $original ) {
			continue;
		}
		if ( wp_check_post_lock( $id ) || get_post_field( 'post_content', $id ) !== $original
			|| is_wp_error( wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true ) ) ) {
			$complete = false;
		}
	}
	if ( $complete ) {
		update_option( 'fl_equipment_details_updated', true, false );
	}
}
add_action( 'admin_init', 'fl_update_equipment_details' );
