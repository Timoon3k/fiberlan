<?php
defined( 'ABSPATH' ) || exit;

function fl_group( $class, $content, $tag = 'div', $anchor = '' ) {
	$attrs = array( 'className' => $class, 'layout' => array( 'type' => 'default' ) );
	if ( 'div' !== $tag ) {
		$attrs['tagName'] = $tag;
	}
	if ( $anchor ) {
		$attrs['anchor'] = $anchor;
	}
	return '<!-- wp:group ' . serialize_block_attributes( $attrs ) . " -->\n"
		. '<' . $tag . ( $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . ' class="wp-block-group ' . esc_attr( $class ) . '">' . "\n"
		. $content . "\n</" . $tag . ">\n<!-- /wp:group -->\n";
}

function fl_heading( $text, $level = 2 ) {
	return sprintf( '<!-- wp:heading%s --><h%d class="wp-block-heading">%s</h%d><!-- /wp:heading -->', 2 === $level ? '' : ' {"level":' . $level . '}', $level, esc_html( $text ), $level ) . "\n";
}

function fl_paragraph( $text, $class = '' ) {
	$attrs = $class ? ' ' . serialize_block_attributes( array( 'className' => $class ) ) : '';
	return '<!-- wp:paragraph' . $attrs . ' --><p' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . esc_html( $text ) . "</p><!-- /wp:paragraph -->\n";
}

function fl_button( $text, $url ) {
	return '<!-- wp:buttons {"className":"fl-actions"} --><div class="wp-block-buttons fl-actions"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $url ) . '">' . esc_html( $text ) . "</a></div><!-- /wp:button --></div><!-- /wp:buttons -->\n";
}

function fl_custom_block( $name, $attributes = array() ) {
	return '<!-- wp:fiberlan/' . $name . ( $attributes ? ' ' . serialize_block_attributes( $attributes ) : '' ) . " /-->\n";
}

function fl_diagram( $file, $class ) {
	return '<!-- wp:image ' . serialize_block_attributes( array( 'sizeSlug' => 'full', 'linkDestination' => 'none', 'className' => $class ) ) . ' --><figure class="wp-block-image size-full ' . esc_attr( $class ) . '"><img src="' . esc_url( get_theme_file_uri( 'assets/diagrams/' . $file . '.svg' ) ) . '" alt=""/></figure><!-- /wp:image -->';
}


/**
 * Plain, editable Gutenberg image placeholder used until the client sends a
 * real photograph. Do not fabricate photographs of the team or installations.
 * A standard core/image block lets editors replace the image in one click.
 */
function fl_photo_placeholder( $lang, $class = 'fl-section-image', $subject = '' ) {
	$class = trim( $class . ' fl-photo-placeholder' );
	$attrs = array(
		'sizeSlug' => 'full',
		'linkDestination' => 'none',
		'className' => $class,
	);
	$src = get_theme_file_uri( 'assets/diagrams/photo-placeholder.svg' );
	$alt = fl_text( $lang, 'Miejsce na fotografię: ', 'Photo placeholder: ' ) . $subject;
	return '<!-- wp:image ' . serialize_block_attributes( $attrs ) . ' -->'
		. '<figure class="wp-block-image size-full ' . esc_attr( $class ) . '">'
		. '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '"/>'
		. '</figure><!-- /wp:image -->';
}

function fl_text( $lang, $pl, $en ) {
	return 'en' === $lang ? $en : $pl;
}

function fl_hero( $lang, $eyebrow, $title, $lead, $visual = '', $extra_class = '' ) {
	$copy = fl_paragraph( $eyebrow, 'fl-eyebrow' ) . fl_heading( $title, 1 ) . fl_paragraph( $lead, 'fl-lead' );
	$inner = $visual ? fl_group( 'fl-split-copy', $copy ) . $visual : $copy;
	return fl_group( trim( 'fl-subhero ' . $extra_class ), fl_group( 'fl-shell ' . ( $visual ? 'fl-split' : 'fl-reading' ), $inner ), 'section' );
}

function fl_section( $content, $layout = 'fl-reading', $variant = '', $anchor = '' ) {
	return fl_group( trim( 'fl-section ' . $variant ), fl_group( 'fl-shell ' . $layout, $content ), 'section', $anchor );
}

function fl_contact_cta( $lang ) {
	return fl_button( fl_text( $lang, 'Zapytaj o wycenę', 'Request a quote' ), fl_url( 'contact', $lang ) );
}

function fl_ribbon( $lang ) {
	$body = fl_text(
		$lang,
		'W kablach wstęgowych włókna są ułożone obok siebie w jednej taśmie. Spawanie wielowłóknowe łączy kilka przygotowanych włókien w jednym cyklu. Po wykonaniu prac sprawdzamy tory optyczne zgodnie z zakresem odbioru.',
		'In ribbon cables, fibres are arranged side by side in a flat strip. Mass fusion splicing joins several prepared fibres in one cycle. After installation, we test the optical links against the agreed acceptance requirements.'
	);
	return fl_section(
		fl_group( 'fl-milestone', fl_paragraph( '20', 'fl-milestone-number' )
			. fl_paragraph( fl_text( $lang, 'Lat doświadczenia w 2026 roku', 'Years of experience in 2026' ), 'fl-milestone-caption' )
			. fl_paragraph( '2006–2026', 'fl-timeline' ) )
		. fl_group( 'fl-ribbon-copy', fl_heading( fl_text( $lang, 'Technologia Ribbon', 'Ribbon technology' ) )
			. fl_paragraph( fl_text( $lang,
				'Specjalistyczne spawanie światłowodów w technologii Ribbon.',
				'Specialist ribbon fibre splicing for multi-fibre installations.' ) )
			. fl_paragraph( $body ) ),
		'fl-split', 'fl-ribbon'
	);
}

function fl_scope_cards( $lang, $heading, $items ) {
	$cards = '';
	foreach ( $items as list( $name_pl, $name_en, $copy_pl, $copy_en ) ) {
		$cards .= fl_group( 'fl-scope-card', fl_heading( fl_text( $lang, $name_pl, $name_en ), 3 )
			. fl_paragraph( fl_text( $lang, $copy_pl, $copy_en ) ), 'article' );
	}
	return fl_section( fl_heading( $heading ) . fl_group( 'fl-scope-grid', $cards ), 'fl-wide' );
}

function fl_about_content( $lang, $title ) {
	$body = fl_hero( $lang, 'FIBERLAN SC', $title, fl_text( $lang,
		'Jesteśmy kilkuosobowym zespołem inżynierów i techników. Od 2006 roku budujemy, mierzymy i utrzymujemy sieci światłowodowe i Ethernet dla firm, deweloperów, operatorów oraz centrów danych.',
		'We are a small team of engineers and technicians. Since 2006 we have built, tested and maintained fibre and Ethernet networks for businesses, property developers, telecoms operators and data centres.' ) );
	$body .= fl_section(
		fl_photo_placeholder( $lang, 'fl-team-photo', fl_text( $lang, 'zespół FIBERLAN', 'FIBERLAN team' ) )
		. fl_group( 'fl-about-story-copy',
			fl_heading( fl_text( $lang, 'FIBERLAN od 2006 roku', 'FIBERLAN since 2006' ) )
			. fl_paragraph( fl_text( $lang,
				'Od 2006 roku projektujemy, instalujemy i mierzymy infrastrukturę światłowodową w Polsce i za granicą. Nasz zespół pracuje przy wdrożeniach operatorskich, przemysłowych i centrach danych.',
				'Since 2006 our team has installed and tested fibre optic infrastructure in Poland and abroad, supporting telecom operators, industrial facilities and data centres.' ) )
			. fl_paragraph( fl_text( $lang,
				'Realizujemy również modernizacje działających sieci, planując prace tak, aby ograniczyć przestoje.',
				'We also upgrade live networks, planning our work to minimise downtime.' ) ) ),
		'fl-about-story'
	);
	$body .= fl_scope_cards( $lang, fl_text( $lang, 'Jak pracujemy', 'How we work' ), array(
		array( 'Dwie dekady w terenie', 'Two decades in the field',
			'Zrealizowaliśmy setki projektów, od sieci biurowych po magistrale światłowodowe, serwerownie i centra danych klientów hiperskalowych.',
			'Our hundreds of projects range from office networks and fibre backbones to server rooms and data centres for hyperscale clients.' ),
		array( 'Bezpośredni kontakt z założycielami', 'Direct access to the founders',
			'Za każdy projekt odpowiadają Andrzej i Norbert, założyciele FIBERLAN. Od pierwszych ustaleń po odbiór masz kontakt z osobami, które sprawują nadzór techniczny nad pracami.',
			'Andrzej and Norbert, the founders of FIBERLAN, take responsibility for every project. From the first discussion to handover, you speak directly to the people supervising the work.' ),
		array( 'Gotowość serwisowa przez całą dobę', 'Round-the-clock support',
			'W Polsce zapewniamy serwis 24/7. Pracujemy również przy kontraktach w Europie, gdzie wspieramy zespoły na miejscu podczas prac instalacyjnych i awarii.',
			'We provide a 24/7 call-out service across Poland. We also work on contracts across Europe, supporting local teams during installations and outages.' ),
		array( 'Pomiary i dokumentacja odbiorowa', 'Acceptance testing and records',
			'Sprawdzamy każdy odcinek sieci. Przekazujemy raporty pomiarowe i dokumentację powykonawczą akceptowaną przez większość dostawców komponentów okablowania.',
			'We test each network section and provide measurement reports and as-built records accepted by most cabling component suppliers.' ),
	) );
	$body .= fl_ribbon( $lang );
	$sectors = 'en' === $lang
		? array( 'Telecommunications operators', 'Data centres', 'Manufacturing and industry', 'Public administration', 'Commercial facilities', 'Marine infrastructure' )
		: array( 'Operatorzy telekomunikacyjni', 'Centra danych', 'Przemysł i produkcja', 'Administracja publiczna', 'Obiekty komercyjne', 'Infrastruktura morska' );
	$cards = '';
	foreach ( $sectors as $sector ) {
		$cards .= fl_group( 'fl-sector', fl_heading( $sector, 3 ) );
	}
	return $body . fl_section( fl_heading( fl_text( $lang, 'Dla kogo pracujemy', 'Who we work with' ) ) . fl_group( 'fl-sector-grid', $cards ) . fl_contact_cta( $lang ), 'fl-wide' );
}

function fl_equipment_visual( $file, $lang ) {
	$photos = array(
		'fiber-splicer-cleaver-client-photo.jpg'   => array( 'Spawarka światłowodowa Fujikura FSM-62S z obcinarką i akcesoriami', 'Fujikura FSM-62S fusion splicer with a fibre cleaver and accessories' ),
		'fluke-networks-versiv-2-client-photo.jpg' => array( 'Dwa moduły Fluke Networks Versiv 2 z ekranem inspekcji złączy', 'Fluke Networks Versiv 2 main and remote units with the connector inspection screen' ),
		'viavi-mpolx-client-photo.jpg'             => array( 'Dwa mierniki VIAVI SmartClass Fiber MPOLx obok torby transportowej', 'Two VIAVI SmartClass Fiber MPOLx instruments beside their carrying case' ),
	);
	if ( isset( $photos[ $file ] ) ) {
		return fiberlan_image_block( $file, fl_text( $lang, ...$photos[ $file ] ), 'fl-section-image' );
	}
	return fl_diagram( $file, 'fl-section-image' );
}

function fl_services_content( $lang, $title ) {
	$services = array(
		array( 'spawanie', 'fiber-splicer-cleaver-client-photo.jpg', 'Spawanie światłowodów', 'Fibre optic splicing',
			'Spawamy włókna jednomodowe i wielomodowe w przyłączach FTTx, magistralach operatorskich, serwerowniach i centrach danych. Układamy włókna w przełącznicach i mufach, zabezpieczamy spawy i sprawdzamy tłumienie połączeń zgodnie z wymaganiami operatora.',
			'We splice single-mode and multimode fibres for FTTx connections, carrier backbones, server rooms and data centres. We route fibres in panels and closures, protect each splice and check losses against the operator’s requirements.' ),
		array( 'instalacje', 'service-lan', 'Instalacje światłowodowe i szafy RACK', 'Fibre installations and equipment racks',
			'W biurach, halach i budynkach deweloperskich budujemy trasy kablowe i uruchamiamy punkty dystrybucyjne. Montujemy i krosujemy szafy RACK oraz porządkujemy okablowanie optyczne i miedziane.',
			'We build cable routes and commission distribution points in offices, industrial halls and new developments. We install and patch racks and organise both fibre and copper cabling.' ),
		array( 'pomiary', 'service-splice', 'Badania OTDR i pomiary transmisyjne', 'OTDR diagnostics and transmission testing',
			'Reflektometrami EXFO lokalizujemy mikrouszkodzenia, oceniamy spawy i złącza oraz mierzymy tłumienie traktu. Raporty z pomiarów przekazujemy z dokumentacją powykonawczą do odbioru technicznego.',
			'We use EXFO optical time-domain reflectometers to locate small faults, assess splices and connectors, and measure link loss. We supply the test reports with the as-built records for technical acceptance.' ),
		array( 'lan', 'fluke-networks-versiv-2-client-photo.jpg', 'Sieci komputerowe i okablowanie miedziane', 'Copper cabling and computer networks',
			'Układamy okablowanie strukturalne, opisujemy gniazda i porty oraz badamy tory miedziane analizatorami Fluke Networks. Zakres certyfikacji dobieramy do kategorii instalacji i wymagań odbioru.',
			'We install structured cabling, label outlets and ports, and test copper links with Fluke Networks analysers. We select certification tests to match the cabling category and acceptance requirements.' ),
		array( 'data-center', 'viavi-mpolx-client-photo.jpg', 'MPO/MTP i spawy taśmowe Ribbon', 'MPO/MTP systems and ribbon splicing',
			'W centrach danych z dużą liczbą włókien wykonujemy spawy taśmowe Ribbon oraz połączenia MPO/MTP. Taki układ ułatwia rozbudowę i utrzymanie porządku w okablowaniu. Łącza wielowłóknowe sprawdzamy miernikami VIAVI.',
			'In high-density data centres we use ribbon splicing and MPO/MTP connections to keep cabling organised and leave room for expansion. We test multi-fibre links with VIAVI instruments.' ),
		array( 'serwis', '', 'Serwis światłowodowy 24/7', '24/7 fibre fault repair',
			'Pełnimy całodobowe dyżury serwisowe w całej Polsce. Po zgłoszeniu awarii lokalizujemy przerwę w kablu za pomocą OTDR i przywracamy ciągłość transmisji.',
			'Our fibre repair team is on call across Poland around the clock. When a fault is reported, we locate the cable break with an OTDR and restore transmission.' ),
	);
	$body = fl_hero( $lang, 'FIBERLAN SC', $title, fl_text( $lang,
		'Projektujemy, budujemy i diagnozujemy sieci optyczne. Łączymy spawanie światłowodów z pomiarami transmisyjnymi, od przyłącza abonenckiego po połączenia wielowłóknowe w centrum danych.',
		'We design, build and diagnose optical networks. Our work combines fibre splicing with transmission testing, from a subscriber connection to multi-fibre links in a data centre.' ) );
	foreach ( $services as $index => list( $anchor, $image, $pl, $en, $desc_pl, $desc_en ) ) {
		$name = fl_text( $lang, $pl, $en );
		if ( '' === $image ) {
			$body .= fl_section( fl_group( 'fl-split-copy', fl_heading( $name ) . fl_paragraph( fl_text( $lang, $desc_pl, $desc_en ) ) )
				. fl_button( fl_text( $lang, 'Kontakt z serwisem', 'Contact the repair team' ), fl_url( 'contact', $lang ) ),
				'fl-repair-row', 'fl-repair', $anchor );
			continue;
		}
		$visual = fl_photo_placeholder( $lang, 'fl-section-image', $name );
		$copy = fl_heading( $name ) . fl_paragraph( fl_text( $lang, $desc_pl, $desc_en ) ) . fl_contact_cta( $lang );
		$body .= fl_section( fl_group( 'fl-split-copy', $copy ) . $visual, 'fl-split', $index % 2 ? 'fl-alternate' : '', $anchor );
	}
	return $body;
}

function fl_equipment_content( $lang, $title ) {
	$items = array(
		array( 'fiber-splicer-cleaver-client-photo.jpg', 'Fujikura FSM-62S', 'Fujikura FSM-62S',
			'Mamy cztery spawarki jednowłóknowe Fujikura z modeli FSM-62S i FSM-70S. Serię S wykorzystujemy w klasycznych instalacjach i magistralach. Do jednoczesnego łączenia wielu włókien w centrach danych wykorzystujemy serię R do spawów taśmowych Ribbon.',
			'We have four single-fibre Fujikura splicers across the FSM-62S and FSM-70S models. We use the S series on conventional installations and backbones, and R-series ribbon splicers for joining multiple fibres in a single operation in data centres.', 'spawanie' ),
		array( 'fluke-networks-versiv-2-client-photo.jpg', 'Fluke Networks DSX-5000', 'Fluke Networks DSX-5000',
			'Dwoma analizatorami Fluke Networks certyfikujemy okablowanie miedziane oraz tory optyczne w zakresie Tier 1. Mierzymy długość i tłumienie, sprawdzamy stan złączy i przygotowujemy raporty odbiorowe. DSX-5000 służy do certyfikacji okablowania miedzianego, a zdjęcie przedstawia platformę Versiv 2 z ekranem FiberInspector Pro.',
			'Our two Fluke Networks analysers certify copper cabling and perform Tier 1 optical testing. We measure link length and loss, inspect connectors and prepare acceptance reports. The DSX-5000 certifies copper cabling. The photo shows the Versiv 2 platform displaying FiberInspector Pro.', 'lan' ),
		array( 'viavi-mpolx-client-photo.jpg', 'VIAVI SmartClass Fiber MPOLx', 'VIAVI SmartClass Fiber MPOLx',
			'Dwa przyrządy VIAVI pozwalają nam testować jednomodowe i wielomodowe tory MPO/MTP. W instalacjach o dużej gęstości sprawdzamy polaryzację, czystość złączy i parametry transmisyjne poszczególnych włókien.',
			'Our two VIAVI instruments test single-mode and multimode MPO/MTP links. In high-density installations we check polarity, connector cleanliness and transmission performance for individual fibres.', 'data-center' ),
		array( 'service-splice', 'Reflektometry EXFO', 'EXFO optical time-domain reflectometers',
			'Mamy trzy reflektometry EXFO do pomiarów na długościach fal 1310, 1550 i 1625 nm. W badaniach Tier 2 oceniamy poszczególne spawy i złącza oraz lokalizujemy uszkodzenia na długich trasach operatorskich.',
			'We have three EXFO OTDRs for measurements at 1310, 1550 and 1625 nm. Tier 2 testing lets us assess individual splices and connectors and pinpoint faults on long carrier routes.', 'pomiary' ),
	);
	$body = fl_hero( $lang, 'FIBERLAN SC', $title, fl_text( $lang,
		'Korzystamy z precyzyjnych spawarek i mierników Fujikura, Fluke Networks, EXFO oraz VIAVI. Sprzęt dobieramy do wymagań instalacji, a pomiary dokumentujemy zgodnie z zakresem odbioru.',
		'We use precision splicers and test instruments from Fujikura, Fluke Networks, EXFO and VIAVI. We choose equipment for each installation and document testing against the handover requirements.'
	), fl_photo_placeholder( $lang, 'fl-subhero-visual', fl_text( $lang, 'fotografia zaplecza technicznego', 'technical equipment' ) ), 'fl-equipment-hero' );

	$catalogue = '';
	foreach ( $items as list( $image, $pl, $en, $copy_pl, $copy_en, $anchor ) ) {
		$name = fl_text( $lang, $pl, $en );
		$visual = 'service-splice' === $image
			? fl_photo_placeholder( $lang, 'fl-section-image', $name )
			: fl_equipment_visual( $image, $lang );
		$copy = fl_heading( $name ) . fl_paragraph( fl_text( $lang, $copy_pl, $copy_en ) )
			. fl_button( fl_text( $lang, 'Zobacz powiązaną usługę', 'View related service' ), fl_url( 'services', $lang ) . '#' . $anchor );
		$catalogue .= fl_group( 'fl-equipment-item', $visual . fl_group( 'fl-equipment-copy', $copy ), 'article' );
	}
	return $body . fl_section( $catalogue, 'fl-equipment' );
}

function fl_project_examples() {
	return array(
		'frankfurt' => array( 'project-datacenter', array( 'data-center', 'telecom', 'international' ), 'Centrum danych we Frankfurcie nad Menem', 'Data centre in Frankfurt am Main', 'Frankfurt nad Menem, Niemcy', 'Frankfurt am Main, Germany', 'Prace przy infrastrukturze sieciowej w centrum danych.', 'Network infrastructure work at a data centre.' ),
		'bordeaux'  => array( 'project-factory', array( 'industry', 'international' ), 'Fabryka szkła w okolicach Bordeaux', 'Glass factory near Bordeaux', 'Okolice Bordeaux, Francja', 'Near Bordeaux, France', 'Instalacje sieci komputerowych w obiekcie przemysłowym.', 'Computer network installation at an industrial facility.' ),
		'marine'    => array( 'project-ship', array( 'marine', 'international' ), 'Statki wycieczkowe i stocznie', 'Cruise ships and shipyards', 'Stocznie międzynarodowe', 'International shipyards', 'Prace instalacyjne na jednostkach zadokowanych w stoczniach m.in. w Niemczech, Hiszpanii, Anglii, Irlandii, Japonii, na Bahamach, Karaibach, we Włoszech i na Malcie.', 'Installation work on vessels docked in shipyards in Germany, Spain, England, Ireland, Japan, the Bahamas, the Caribbean, Italy and Malta.' ),
	);
}

/**
 * Bilingual project story, 4 service-sector case descriptions and an
 * editable native image gallery. The editorial implementation note supplied
 * by the client is deliberately not displayed on the public website.
 */
function fl_projects_content( $lang, $title ) {
	$lead = fl_text( $lang,
		'Od 2006 roku zrealizowaliśmy setki projektów na terenie Polski i całej Europy. Jako podwykonawcy wspieramy zarówno generalnych wykonawców, dużych operatorów telekomunikacyjnych takich jak T-Mobile, Orange czy PLUS, jak i sektor MŚP oraz deweloperów. Poniżej prezentujemy wybrane obszary naszych wdrożeń.',
		'Since 2006, we have delivered hundreds of projects across Poland and Europe. As a subcontractor, we support general contractors, major telecom operators such as T-Mobile, Orange and PLUS, as well as SMEs and property developers. Explore selected areas of our work below.'
	);
	$cases = array(
		array(
			'Centra Danych (Data Center) – wdrożenia MPO/MTP i Ribbon',
			'Data centres — MPO/MTP and Ribbon installations',
			'Wykonanie wysoko wydajnych połączeń wielowłóknowych w technologii taśmowej (Ribbon) oraz systemach MPO/MTP. Certyfikacja torów optycznych miernikami Viavi pod kątem transmisji 100G/400G.',
			'High-performance multi-fibre connections using ribbon fusion splicing and MPO/MTP systems. Certification of optical links with VIAVI instruments for 100G/400G transmission.'
		),
		array(
			'Infrastruktura magazynowa i przemysłowa – sieci szkieletowe',
			'Warehousing and industrial infrastructure — backbone networks',
			'Budowa światłowodowych sieci szkieletowych łączących hale produkcyjne i biurowce. Spawanie przełącznic w szafach RACK oraz kompletne pomiary reflektometryczne OTDR.',
			'Fibre optic backbone networks connecting production halls and offices. Splicing at rack-mounted distribution panels and complete OTDR reflectometry testing.'
		),
		array(
			'Inwestycje deweloperskie i biurowce – okablowanie strukturalne',
			'Property developments and office buildings — structured cabling',
			'Kompleksowe wykonanie pionów światłowodowych oraz wyposażenie pomieszczeń teletechnicznych. Przygotowanie dokumentacji powykonawczej do odbiorów inwestorskich.',
			'Complete fibre riser installations and telecommunications room fit-outs, including as-built documentation for investor handover.'
		),
		array(
			'Magistrale operatorskie i serwis awaryjny',
			'Carrier backbone networks and emergency maintenance',
			'Spawanie muf liniowych w studniach kablowych, precyzyjna lokalizacja uszkodzeń na rozległych odcinkach traktów oraz szybkie przywracanie łączności w trybie 24/7.',
			'Splicing of inline closures in cable chambers, precise fault location across extensive routes and rapid restoration of connectivity with 24/7 response.'
		),
	);
	$cards = '';
	$gallery = '';
	foreach ( $cases as $case ) {
		$cards .= fl_group( 'fl-project-feature',
			fl_heading( fl_text( $lang, $case[0], $case[1] ), 3 )
			. fl_paragraph( fl_text( $lang, 'Zakres prac', 'Scope of work' ), 'fl-project-feature-label' )
			. fl_paragraph( fl_text( $lang, $case[2], $case[3] ) ), 'article' );
		$gallery .= fl_photo_placeholder( $lang, 'fl-gallery-placeholder',
			fl_text( $lang, $case[0], $case[1] ) );
	}
	$catalogue = fl_section(
		fl_heading( fl_text( $lang, 'Wybrane realizacje', 'Selected projects' ) )
		. fl_custom_block( 'projects', array(
			'label' => fl_text( $lang, 'Kategorie realizacji', 'Project categories' ),
			'all' => fl_text( $lang, 'Wszystkie', 'All projects' ),
			'empty' => fl_text( $lang, 'Brak realizacji w tej kategorii.', 'No projects in this category.' ),
		) ), 'fl-wide', 'fl-project-catalogue' );
	$showcase = fl_section(
		fl_heading( fl_text( $lang, 'Obszary naszych wdrożeń', 'Areas of expertise' ) )
		. fl_group( 'fl-project-feature-grid', $cards ), 'fl-wide', 'fl-project-showcase' );
	$photos = fl_section(
		fl_heading( fl_text( $lang, 'Galeria realizacji', 'Project gallery' ) )
		. fl_paragraph( fl_text( $lang,
			'Miejsce na fotografie z kolejnych projektów. Zdjęcia można zastąpić w edytorze Gutenberg.',
			'Space for photographs from upcoming projects. Replace these images in the Gutenberg editor.' ) )
		. fl_group( 'fl-project-gallery', $gallery ), 'fl-wide', 'fl-project-photos' );
	return fl_hero( $lang, 'FIBERLAN SC', $title, $lead ) . $catalogue . $showcase . $photos;
}


function fl_contact_content( $lang, $title ) {
	$lead = fl_text( $lang, 'Opisz zakres instalacji, lokalizację i przewidywany termin. Wrócimy z pytaniami lub propozycją kolejnych kroków.', 'Describe the installation scope, location and expected schedule. We will follow up with any questions or proposed next steps.' );
	$sidebar = fl_heading( fl_text( $lang, 'Dane kontaktowe', 'Contact details' ) ) . fl_paragraph( 'FIBERLAN SC' ) . fiberlan_contact_reference();
	return fl_hero( $lang, 'FIBERLAN SC', $title, $lead )
		. fl_section( fl_group( 'fl-contact-sidebar', $sidebar ) . fl_group( 'fl-form-panel', fl_heading( fl_text( $lang, 'Zapytanie ofertowe', 'Project enquiry' ) ) . fl_custom_block( 'contact', fl_contact_labels( $lang ) ) ), 'fl-contact-layout' );
}

function fl_legal_content( $lang, $title, $key ) {
	$draft = fl_text( $lang, 'Projekt dokumentu do zatwierdzenia przez administratora danych przed publikacją.', 'Draft for approval by the data controller before publication.' );
	$body = fl_hero( $lang, 'FIBERLAN SC', $title, $draft );
	if ( 'privacy' === $key ) {
		$copy = array(
			array( 'Administrator i kontakt', 'Data controller and contact', 'Administratorem danych jest FIBERLAN SC, ul. Wojskowa 8A lok. 1, 03-599 Warszawa. W sprawach dotyczących danych skontaktuj się z nami pod adresem info@fiberlan.pl.', 'FIBERLAN SC, ul. Wojskowa 8A lok. 1, 03-599 Warsaw, Poland, is the data controller. For questions about your data, email info@fiberlan.pl.' ),
			array( 'Zapytania i podstawy przetwarzania', 'Enquiries and legal grounds', 'Dane z formularza wykorzystujemy do obsługi zapytania i przygotowania oferty. Podstawą mogą być działania przed zawarciem umowy lub uzasadniony interes polegający na prowadzeniu korespondencji. Dane kontaktowe i opis zapytania otrzymuje zespół obsługujący pocztę firmy.', 'We use form submissions to respond to enquiries and prepare quotes. Processing may be necessary to take steps before a contract or to pursue our legitimate interest in business correspondence. The team handling company email can access contact details and enquiry content.' ),
			array( 'Dostawcy i retencja', 'Providers and retention', 'Informacje mogą być przetwarzane przez dostawców hostingu i poczty. Przed publikacją uzupełnimy listę usług, okresy przechowywania korespondencji, dane dotyczące transferów i zasady retencji logów technicznych.', 'Hosting and email providers may process information. Before publication, this policy will list the services used, correspondence retention periods, transfer details and the retention policy for technical logs.' ),
			array( 'Twoje prawa', 'Your rights', 'W zależności od podstawy przetwarzania możesz żądać dostępu, sprostowania, usunięcia, ograniczenia lub przeniesienia danych oraz wnieść sprzeciw. Przysługuje Ci prawo wniesienia skargi do Prezesa Urzędu Ochrony Danych Osobowych.', 'Depending on the legal basis, you may request access, rectification, erasure, restriction or portability, or object to processing. You can lodge a complaint with the Polish data protection authority.' ),
		);
	} else {
		$copy = array(
			array( 'Niezbędne dane techniczne', 'Essential technical data', 'WordPress używa danych sesji m.in. do logowania administratorów i obsługi ustawień witryny. Wybór języka jest zapamiętywany w pamięci przeglądarki jako fiberlan-language.', 'WordPress uses session data for functions such as administrator login and site settings. The browser stores your language preference in local storage under fiberlan-language.' ),
			array( 'Zgody i zmiana ustawień', 'Consent and changes to your settings', 'Po skonfigurowaniu CookieYes z panelu zgód będzie można włączyć albo odrzucić poszczególne kategorie i później zmienić wybór. Ostateczne kategorie, dostawcy i okresy przechowywania zostaną podane po skanowaniu konfiguracji produkcyjnej.', 'Once CookieYes has been configured, the consent panel will let you accept or reject categories and change your choice later. The final categories, providers and retention periods will be listed after scanning the production configuration.' ),
		);
	}
	$paragraphs = '';
	foreach ( $copy as list( $pl, $en, $text_pl, $text_en ) ) {
		$paragraphs .= fl_heading( fl_text( $lang, $pl, $en ) ) . fl_paragraph( fl_text( $lang, $text_pl, $text_en ) );
	}
	return $body . fl_section( $paragraphs );
}

function fl_home_reviews_content( $lang ) {
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'fiberlan/home-reviews' );
	$content = $pattern['content'] ?? '';
	return 'en' === $lang ? strtr( $content, array(
		'Opinie z Google' => 'Google reviews',
		'Sprawdź opinie o FIBERLAN SC w Mapach Google.' => 'Read reviews of FIBERLAN SC on Google Maps.',
		'Znajdź FIBERLAN w Mapach Google' => 'Find FIBERLAN on Google Maps',
		'Widżet opinii z Google' => 'Google reviews widget',
	) ) : $content;
}

function fl_home_content( $lang, $existing = '' ) {
	$registry = WP_Block_Patterns_Registry::get_instance();
	$patterns = array();
	foreach ( array( 'hero', 'experience', 'services', 'projects' ) as $section ) {
		$patterns[] = $registry->get_registered( 'fiberlan/home-' . $section )['content'];
	}
	$links = array(
		'Wycena instalacji światłowodowej' => fl_url( 'contact', $lang ),
		'Projekt instalacji światłowodowej' => fl_url( 'contact', $lang ),
		'Pytanie dotyczące usług FIBERLAN' => fl_url( 'services', $lang ),
		'Zapytanie o realizacje FIBERLAN' => fl_url( 'projects', $lang ),
		'Zapytanie o usługę – Spawanie światłowodów' => fl_url( 'services', $lang ) . '#spawanie',
		'Zapytanie o usługę – Pomiary i certyfikacja' => fl_url( 'services', $lang ) . '#pomiary',
		'Zapytanie o usługę – Instalacje światłowodowe' => fl_url( 'services', $lang ) . '#instalacje',
		'Zapytanie o usługę – Sieci komputerowe i miedziane' => fl_url( 'services', $lang ) . '#lan',
	);
	$replace = array( 'href="#uslugi"' => 'href="' . esc_url( fl_url( 'services', $lang ) ) . '"' );
	foreach ( $links as $subject => $url ) {
		$replace[ 'href="mailto:info@fiberlan.pl?subject=' . rawurlencode( $subject ) . '"' ] = 'href="' . esc_url( $url ) . '"';
	}
	foreach ( array(
		'frankfurt' => array( 'Centrum danych', 'Data centre' ),
		'bordeaux'  => array( 'Fabryka szkła', 'Glass factory' ),
		'marine'    => array( 'Statki wycieczkowe', 'Cruise ships' ),
	) as $key => list( $pl, $en ) ) {
		$url = fl_url( $key, $lang );
		if ( $url ) {
			$replace[ '>' . $pl . '</h3>' ] = '><a href="' . esc_url( $url ) . '">' . esc_html( fl_text( $lang, $pl, $en ) ) . '</a></h3>';
		}
	}
	$replace['>Zapytaj o usługę</a>'] = '>' . fl_text( $lang, 'Wszystkie usługi', 'All services' ) . '</a>';
	$replace['>Zapytaj o realizacje</a>'] = '>' . fl_text( $lang, 'Zobacz realizacje', 'View our projects' ) . '</a>';
	if ( $existing ) {
		$html = strtr( $existing, $replace );
		if ( false !== strpos( $html, 'fl-ribbon' ) ) {
			return $html;
		}
		$position = strpos( $html, '<!-- wp:group {"tagName":"section","className":"fl-reviews"' );
		if ( false === $position ) {
			$position = strpos( $html, '<!-- wp:group {"tagName":"section","className":"fl-contact"' );
		}
		$ribbon = "\n" . fl_ribbon( $lang ) . "\n";
		return false === $position ? $html . $ribbon : substr_replace( $html, $ribbon, $position, 0 );
	}

	if ( 'en' === $lang ) {
		$replace += array(
			'Profesjonalne usługi telekomunikacyjne' => 'Professional telecommunications services',
			'Łączymy, instalujemy i mierzymy sieci światłowodowe dla firm, przemysłu i centrów danych. Pracujemy w Polsce i za granicą.' => 'We splice, install and test fibre optic networks for businesses, industrial facilities and data centres in Poland and abroad.',
			'Spawarka światłowodowa Fujikura FSM-62S z otwartą osłoną i włączonym ekranem.' => 'Fujikura FSM-62S fusion splicer with its cover open and display lit.',
			'20 lat doświadczenia' => '20 years of experience',
			'Działamy od 2006 roku' => 'Operating since 2006',
			'Polska i świat' => 'Poland and abroad',
			'Realizacje międzynarodowe' => 'International projects',
			'Technologia Ribbon' => 'Ribbon technology',
			'Spawanie wielowłóknowe' => 'Mass fusion splicing',
			'Infrastruktura B2B' => 'Business infrastructure',
			'Dla firm i przemysłu' => 'For business and industry',
			'Nasze usługi' => 'Our services',
			'Zapytaj o usługę' => 'Explore our services',
			'Spawanie światłowodów' => 'Fibre optic splicing',
			'Łączenie włókien jedno- i wielomodowych oraz spawanie w technologii Ribbon.' => 'Single-mode, multimode and ribbon fibre splicing.',
			'Dwa mierniki VIAVI SmartClass Fiber MPOLx z torbą transportową.' => 'Two VIAVI SmartClass Fiber MPOLx testers and their carrying case.',
			'Pomiary i certyfikacja' => 'Testing and certification',
			'Pomiary OTDR, testy tłumienia oraz dokumentacja odbiorowa sieci.' => 'OTDR and insertion loss testing with acceptance reports.',
			'Zestaw Fluke Networks Versiv 2 do sprawdzania instalacji.' => 'Fluke Networks Versiv 2 cabling tester.',
			'Instalacje światłowodowe' => 'Fibre optic installation',
			'Budowa, rozbudowa i modernizacja sieci optycznych w obiektach firmowych.' => 'Installation, extensions and upgrades for business fibre optic networks.',
			'Sieci komputerowe i miedziane' => 'Copper cabling and computer networks',
			'Okablowanie strukturalne, infrastruktura LAN i testy połączeń.' => 'Structured cabling, LAN infrastructure and link testing.',
			'Doświadczenie na globalną skalę' => 'International experience',
			'Od sieci w centrach danych po instalacje na statkach wycieczkowych.' => 'From data centre networks to installations on cruise ships.',
			'Zapytaj o realizacje' => 'View our projects',
			'Centrum danych' => 'Data centre',
			'Frankfurt nad Menem, Niemcy' => 'Frankfurt am Main, Germany',
			'Fabryka szkła' => 'Glass factory',
			'Okolice Bordeaux, Francja' => 'Near Bordeaux, France',
			'Statki wycieczkowe' => 'Cruise ships',
			'Stocznie międzynarodowe' => 'International shipyards',
			'Zapytaj o wycenę' => 'Request a quote',
			'Poznaj nasze usługi' => 'Explore our services',
		);
	}
	$patterns[] = fl_ribbon( $lang );
	$patterns[] = fl_home_reviews_content( $lang );
	return strtr( implode( "\n", $patterns ), $replace );
}
