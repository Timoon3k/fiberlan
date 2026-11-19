<?php
defined( 'ABSPATH' ) || exit;

function fl_schema_organization() {
	$home = home_url( '/' );
	$name = get_bloginfo( 'name' );
	$org = array( '@type' => 'Organization', '@id' => $home . '#organization', 'name' => $name, 'url' => $home );
	$logo = wp_get_attachment_image_url( (int) get_option( 'site_logo' ), 'full' );
	if ( $logo ) {
		$org['logo'] = $logo;
	}
	$id = (int) get_option( 'fiberlan_contact_block_id' );
	if ( ! $id ) {
		return $org;
	}
	$raw = get_post_field( 'post_content', $id );
	$html = new WP_HTML_Tag_Processor( $raw );
	$phones = array();
	while ( $html->next_tag( 'a' ) ) {
		$href = (string) $html->get_attribute( 'href' );
		if ( preg_match( '/^tel:(\+?[\d\s-]+)$/D', $href, $m ) ) {
			$phones[] = $m[1];
		} elseif ( 0 === strpos( $href, 'mailto:' ) && is_email( substr( $href, 7 ) ) ) {
			$org['email'] = substr( $href, 7 );
		}
	}
	if ( $phones ) {
		$org['telephone'] = reset( $phones );
	}
	if ( preg_match( '~<p\b[^>]*\bfl-address\b[^>]*>(.*?)</p>~is', $raw, $m ) ) {
		$address = trim( wp_strip_all_tags( $m[1] ) );
		$org['@type'] = array( 'Organization', 'LocalBusiness' );
		$org['address'] = array( '@type' => 'PostalAddress', 'streetAddress' => $address );
		if ( preg_match( '/^(.+?),\s*(\d{2}-\d{3})\s+([^,]+)$/uD', $address, $parts ) ) {
			$org['address'] = array(
				'@type' => 'PostalAddress', 'streetAddress' => $parts[1], 'postalCode' => $parts[2],
				'addressLocality' => $parts[3], 'addressCountry' => 'PL',
			);
		}
	}
	return $org;
}

function fl_managed_schema_page() {
	return is_page() && get_post_meta( get_queried_object_id(), '_fl_page_key', true );
}

// FIBERLAN owns the graph for its seeded pages so there is one graph even with Rank Math.
add_filter( 'rank_math/json_ld', static function ( $graph ) {
	return fl_managed_schema_page() ? array() : $graph;
}, 200 );

add_action( 'wp_head', static function () {
	if ( ! fl_managed_schema_page() ) {
		return;
	}
	$id = get_queried_object_id();
	$url = get_permalink( $id );
	$org = fl_schema_organization();
	$graph = array( $org );
	$locale = 'en' === fl_page_lang( $id ) ? 'en-GB' : 'pl-PL';
	$site = array( '@type' => 'WebSite', '@id' => home_url( '/#website' ), 'url' => home_url( '/' ), 'name' => get_bloginfo( 'name' ), 'publisher' => array( '@id' => $org['@id'] ) );
	$page = array(
		'@type' => 'WebPage', '@id' => $url . '#webpage', 'url' => $url,
		'name' => fl_seo_meta( $id, 'title' ), 'description' => fl_seo_meta( $id, 'description' ),
		'inLanguage' => $locale, 'isPartOf' => array( '@id' => $site['@id'] ),
	);
	$graph[] = $site;
	$items = fl_breadcrumbs( $id );
	if ( $items ) {
		$list = array();
		foreach ( $items as $index => $item ) {
			$list[] = array( '@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url'] );
		}
		$crumbs = array( '@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumbs', 'itemListElement' => $list );
		$page['breadcrumb'] = array( '@id' => $crumbs['@id'] );
		$graph[] = $crumbs;
	}
	foreach ( fl_service_sections( $id ) as $item ) {
		$graph[] = array(
			'@type' => 'Service', '@id' => $item['url'], 'url' => $item['url'], 'name' => $item['name'],
			'provider' => array( '@id' => $org['@id'] ),
		);
	}
	$graph[] = $page;
	printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
}, 30 );
