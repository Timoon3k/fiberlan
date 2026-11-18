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

function fl_text( $lang, $pl, $en ) {
	return 'en' === $lang ? $en : $pl;
}

function fl_hero( $lang, $eyebrow, $title, $lead, $visual = '' ) {
	$copy = fl_paragraph( $eyebrow, 'fl-eyebrow' ) . fl_heading( $title, 1 ) . fl_paragraph( $lead, 'fl-lead' );
	$inner = $visual ? fl_group( 'fl-split-copy', $copy ) . $visual : $copy;
	return fl_group( 'fl-subhero', fl_group( 'fl-shell ' . ( $visual ? 'fl-split' : 'fl-reading' ), $inner ), 'section' );
}

function fl_section( $content, $layout = 'fl-reading', $variant = '', $anchor = '' ) {
	return fl_group( trim( 'fl-section ' . $variant ), fl_group( 'fl-shell ' . $layout, $content ), 'section', $anchor );
}

function fl_contact_cta( $lang ) {
	return fl_button( fl_text( $lang, 'Zapytaj o wycenę', 'Request a quote' ), fl_url( 'contact', $lang ) );
}

function fl_ribbon( $lang ) {
	$details = fl_text( $lang, 'Poznaj technologię Ribbon', 'Explore Ribbon splicing' );
	$body = fl_text(
		$lang,
		'W kablach wstęgowych włókna są ułożone obok siebie w jednej taśmie. Spawanie wielowłóknowe łączy kilka przygotowanych włókien w jednym cyklu. Po wykonaniu prac sprawdzamy tory optyczne zgodnie z zakresem odbioru.',
		'In ribbon cables, fibres are arranged side by side in a flat strip. Mass fusion splicing joins several prepared fibres in one cycle. After installation, we test the optical links against the agreed acceptance requirements.'
	);
	$panel = '<!-- wp:details --><details class="wp-block-details"><summary>' . esc_html( $details ) . '</summary>' . fl_paragraph( $body ) . '</details><!-- /wp:details -->';
	return fl_section(
		fl_group( 'fl-milestone', fl_paragraph( '20', 'fl-milestone-number' ) . fl_paragraph( fl_text( $lang, 'Lat doświadczenia w 2026 roku', 'Years of experience in 2026' ), 'fl-milestone-caption' ) . fl_paragraph( '2006–2026', 'fl-timeline' ) )
		. fl_group( 'fl-ribbon-copy', fl_heading( fl_text( $lang, 'Technologia Ribbon', 'Ribbon technology' ) ) . fl_paragraph( fl_text( $lang, 'Specjalistyczne spawanie światłowodów w technologii Ribbon.', 'Specialist ribbon fibre splicing for multi-fibre installations.' ) ) . $panel ),
		'fl-split',
		'fl-ribbon'
	);
}

function fl_about_content( $lang, $title ) {
	$body = fl_hero( $lang, 'FIBERLAN SC', $title, fl_text( $lang, 'Od 2006 roku budujemy i mierzymy sieci światłowodowe dla firm w Polsce i za granicą.', 'We have built and tested fibre optic networks for businesses in Poland and abroad since 2006.' ) );
	$history = fl_heading( fl_text( $lang, 'Od instalacji do odbioru sieci', 'From installation to acceptance testing' ) )
		. fl_paragraph( fl_text( $lang, 'Łączymy doświadczenie w spawaniu włókien, budowie tras i pomiarach optycznych. Zakres prac ustalamy wspólnie z inwestorem, a odbiór dokumentujemy zgodnie z wymaganiami projektu.', 'Our work covers fibre splicing, cable route installation and optical testing. We agree the scope with the client and document acceptance tests against the project requirements.' ) )
		. fl_paragraph( fl_text( $lang, 'Realizujemy prace na nowych instalacjach i w czynnych obiektach. Przy modernizacji uwzględniamy dostęp do tras i uzgodnione okna prac.', 'We work on new networks and in operational facilities. For upgrades, we plan around access to cable routes and agreed maintenance windows.' ) );
	$body .= fl_section( $history );
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

function fl_services_content( $lang, $title ) {
	$services = array(
		array( 'spawanie', 'service-splice', 'Spawanie światłowodów', 'Fibre optic splicing', 'Łączymy włókna jednomodowe i wielomodowe. Przy kablach wstęgowych stosujemy spawanie wielowłóknowe Ribbon. Miejsce połączenia zabezpieczamy i układamy w kasecie, a sposób weryfikacji uzgadniamy przed odbiorem.', 'We splice single-mode and multimode fibres, including ribbon cables using mass fusion splicing. We protect the splice, arrange fibres in the tray and agree the verification method before handover.' ),
		array( 'instalacje', 'fiber-splicer-cleaver-client-photo.jpg', 'Instalacje światłowodowe', 'Fibre optic installation', 'Budujemy i rozbudowujemy tory optyczne. Prowadzimy kable, terminujemy włókna w przełącznicach, opisujemy porty i przygotowujemy instalację do pomiarów oraz odbioru.', 'We build and extend optical links. We route cables, terminate fibres in distribution panels, label ports and prepare the installation for testing and handover.' ),
		array( 'pomiary', 'viavi-mpolx-client-photo.jpg', 'Pomiary światłowodowe', 'Fibre optic testing', 'Dobieramy pomiary do typu toru i wymagań odbioru. Pomiary tłumienia oceniają całe łącze, a reflektometria OTDR pomaga zlokalizować zdarzenia na trasie. Wyniki przekazujemy w dokumentacji pomiarowej.', 'We select tests according to the link and acceptance criteria. Insertion loss tests assess the entire link, while OTDR testing helps locate events along the route. We deliver the results in a test report.' ),
		array( 'lan', 'service-lan', 'Sieci komputerowe i okablowanie miedziane', 'Copper cabling and computer networks', 'Układamy okablowanie strukturalne, opisujemy gniazda i porty oraz wykonujemy pomiary torów miedzianych. Zakres certyfikacji wynika z kategorii instalacji i wymagań inwestora.', 'We install structured cabling, label outlets and ports, and test copper links. The installation category and the client’s specifications determine the certification scope.' ),
		array( 'data-center', 'project-datacenter', 'Centra danych i przemysł', 'Data centres and industrial facilities', 'Realizujemy instalacje i prace pomiarowe w obiektach, w których istotne są dostęp do infrastruktury, kolejność uruchomień i czytelna dokumentacja. Uzgadniamy trasowanie i plan odbioru z zespołem obiektu.', 'We install and test networks in facilities where access, commissioning sequence and documentation matter. We coordinate cable routes and acceptance plans with the site team.' ),
	);
	$body = fl_hero( $lang, 'FIBERLAN SC', $title, fl_text( $lang, 'Spawanie, instalacje i pomiary sieci światłowodowych oraz okablowania miedzianego.', 'Fibre optic splicing, installation and testing, alongside copper network cabling.' ) );
	foreach ( $services as $index => list( $anchor, $image, $pl, $en, $desc_pl, $desc_en ) ) {
		$name = fl_text( $lang, $pl, $en );
		$visual = substr( $image, -4 ) === '.jpg'
			? fiberlan_image_block( $image, fl_text( $lang, 'Sprzęt FIBERLAN do prac światłowodowych', 'FIBERLAN equipment for fibre optic work' ), 'fl-section-image' )
			: fl_diagram( $image, 'fl-section-image' );
		$copy = fl_heading( $name ) . fl_paragraph( fl_text( $lang, $desc_pl, $desc_en ) ) . fl_contact_cta( $lang );
		$body .= fl_section( fl_group( 'fl-split-copy', $copy ) . $visual, 'fl-split', $index % 2 ? 'fl-alternate' : '', $anchor );
	}
	return $body;
}

function fl_equipment_content( $lang, $title ) {
	$items = array(
		array( 'fiber-splicer-cleaver-client-photo.jpg', 'Spawarka z obcinarką', 'Fusion splicer and cleaver', 'Spawarka z obcinarką i akcesoriami do przygotowania włókien.', 'Fusion splicer, cleaver and fibre preparation accessories.', 'spawanie' ),
		array( 'fluke-networks-versiv-2-client-photo.jpg', 'Fluke Networks Versiv 2', 'Fluke Networks Versiv 2', 'Platforma do sprawdzania okablowania z wymiennymi modułami. Na zdjęciu ekran inspekcji złączy światłowodowych.', 'A modular cabling test platform. The photo shows the fibre connector inspection screen.', 'instalacje' ),
		array( 'viavi-mpolx-client-photo.jpg', 'VIAVI MPOLx', 'VIAVI MPOLx', 'Zestaw pomiarowy do wielowłóknowych torów optycznych z interfejsem MPO.', 'A test set for multi-fibre optical links with MPO interfaces.', 'pomiary' ),
		array( 'service-splice', 'Sprzęt do technologii Ribbon', 'Ribbon splicing equipment', 'Przygotowanie i łączenie włókien ułożonych w taśmie.', 'Preparation and splicing of fibres arranged in a ribbon.', 'spawanie' ),
		array( 'project-datacenter', 'Reflektometry OTDR', 'OTDR testers', 'Pomiary reflektometryczne przy odbiorze instalacji i lokalizowaniu zdarzeń na trasie.', 'Reflectometry for acceptance testing and locating events along the fibre route.', 'pomiary' ),
	);
	$body = fl_hero( $lang, 'FIBERLAN SC', $title, fl_text( $lang, 'Urządzenia do przygotowania włókien, spawania, inspekcji i pomiarów torów optycznych.', 'Equipment for fibre preparation, fusion splicing, inspection and optical link testing.' ) );
	$catalogue = '';
	foreach ( $items as list( $image, $pl, $en, $copy_pl, $copy_en, $anchor ) ) {
		$name = fl_text( $lang, $pl, $en );
		$visual = substr( $image, -4 ) === '.jpg' ? fiberlan_image_block( $image, $name, 'fl-section-image' ) : fl_diagram( $image, 'fl-section-image' );
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

function fl_projects_content( $lang, $title ) {
	$lead = fl_text( $lang, 'Wybrane projekty instalacji sieci dla centrów danych, przemysłu i infrastruktury morskiej.', 'Selected network installations for data centres, industrial facilities and marine infrastructure.' );
	return fl_hero( $lang, 'FIBERLAN SC', $title, $lead, fl_diagram( 'project-datacenter', 'fl-subhero-visual' ) )
		. fl_section( fl_heading( fl_text( $lang, 'Wybrane realizacje', 'Selected projects' ) ) . fl_custom_block( 'projects', array(
			'label' => fl_text( $lang, 'Kategorie realizacji', 'Project categories' ),
			'all'   => fl_text( $lang, 'Wszystkie', 'All projects' ),
			'empty' => fl_text( $lang, 'Brak realizacji w tej kategorii.', 'There are no projects in this category.' ),
		) ), 'fl-wide' );
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

function fl_home_content( $lang, $existing = '' ) {
	$registry = WP_Block_Patterns_Registry::get_instance();
	$patterns = array();
	foreach ( array( 'hero', 'experience', 'services', 'projects', 'contact' ) as $section ) {
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
	if ( $existing ) {
		$html = strtr( $existing, $replace );
		if ( false !== strpos( $html, 'fl-ribbon' ) ) {
			return $html;
		}
		$position = strpos( $html, '<!-- wp:group {"tagName":"section","anchor":"uslugi"' );
		$ribbon = "\n" . fl_ribbon( $lang ) . "\n";
		return false === $position ? $html . $ribbon : substr_replace( $html, $ribbon, $position, 0 );
	}

	if ( 'en' === $lang ) {
		$replace += array(
			'Profesjonalne usługi telekomunikacyjne' => 'Professional telecommunications services',
			'Łączymy, instalujemy i mierzymy sieci światłowodowe dla firm, przemysłu i centrów danych. Pracujemy w Polsce i za granicą.' => 'We splice, install and test fibre optic networks for businesses, industrial facilities and data centres in Poland and abroad.',
			'Spawarka światłowodowa z otwartą osłoną, obcinarką i akcesoriami.' => 'Fibre optic fusion splicer with its cover open, a cleaver and accessories.',
			'Spawarka światłowodowa z obcinarką' => 'Fusion splicer and cleaver',
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
			'Dwa mierniki VIAVI z torbą transportową.' => 'Two VIAVI testers and their carrying case.',
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
			'Szukasz sprawdzonego partnera do realizacji instalacji światłowodowej?' => 'Looking for a partner for your fibre optic installation?',
			'Porozmawiajmy o projekcie' => 'Discuss your project',
			'Zapytaj o wycenę' => 'Request a quote',
			'Poznaj nasze usługi' => 'Explore our services',
		);
	}
	array_splice( $patterns, 2, 0, fl_ribbon( $lang ) );
	return strtr( implode( "\n", $patterns ), $replace );
}
