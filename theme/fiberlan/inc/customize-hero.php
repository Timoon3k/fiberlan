<?php
/**
 * Edit the homepage introduction in the native WordPress Customizer.
 *
 * @package Fiberlan
 */

defined( 'ABSPATH' ) || exit;

function fiberlan_hero_controls() {
	return array(
		'eyebrow'   => array( 'Nadtytuł', 'text' ),
		'heading'   => array( 'Nagłówek główny', 'text' ),
		'lead'      => array( 'Opis', 'textarea' ),
		'image'     => array( 'Zdjęcie główne', 'image' ),
		'alt'       => array( 'Tekst alternatywny zdjęcia', 'text' ),
		'primary'   => array( 'Pierwszy przycisk · tekst', 'text' ),
		'primary_url' => array( 'Pierwszy przycisk · adres', 'text' ),
		'secondary' => array( 'Drugi przycisk · tekst', 'text' ),
		'secondary_url' => array( 'Drugi przycisk · adres', 'text' ),
	);
}

/** Get references to the editable blocks inside the homepage introduction. */
function fiberlan_hero_targets( &$blocks, $inside_hero = false ) {
	$targets = array();
	foreach ( $blocks as &$block ) {
		$classes = explode( ' ', $block['attrs']['className'] ?? '' );
		$name = $block['blockName'];
		$is_hero = 'core/group' === $name && in_array( 'fl-hero', $classes, true );
		if ( $inside_hero ) {
			if ( 'core/heading' === $name && 1 === ( $block['attrs']['level'] ?? 2 ) ) {
				$targets['heading'] = &$block;
			} elseif ( 'core/paragraph' === $name && in_array( 'fl-eyebrow', $classes, true ) ) {
				$targets['eyebrow'] = &$block;
			} elseif ( 'core/paragraph' === $name && in_array( 'fl-lead', $classes, true ) ) {
				$targets['lead'] = &$block;
			} elseif ( 'core/image' === $name && in_array( 'fl-hero-figure', $classes, true ) ) {
				$targets['image'] = &$block;
			} elseif ( 'core/buttons' === $name && in_array( 'fl-actions', $classes, true ) ) {
				foreach ( array( 'primary', 'secondary' ) as $index => $key ) {
					if ( 'core/button' === ( $block['innerBlocks'][ $index ]['blockName'] ?? '' ) ) {
						$targets[ $key ] = &$block['innerBlocks'][ $index ];
					}
				}
			}
		}
		if ( 'core/group' === $name && $block['innerBlocks'] ) {
			$targets += fiberlan_hero_targets( $block['innerBlocks'], $inside_hero || $is_hero );
			if ( $is_hero ) {
				break;
			}
		}
	}
	return $targets;
}

function fiberlan_hero_values( $content ) {
	$blocks = parse_blocks( $content );
	$values = array();
	foreach ( fiberlan_hero_targets( $blocks ) as $key => $block ) {
		$html = $block['innerHTML'];
		$tag = array( 'heading' => 'h1', 'primary' => 'a', 'secondary' => 'a' )[ $key ] ?? 'p';
		if ( 'image' === $key ) {
			$image = new WP_HTML_Tag_Processor( $html );
			if ( $image->next_tag( 'img' ) ) {
				$values['image'] = absint( $block['attrs']['id'] ?? 0 );
				$values['alt'] = (string) $image->get_attribute( 'alt' );
			}
		} elseif ( preg_match( '~<' . $tag . '\b[^>]*>(.*?)</' . $tag . '>~s', $html, $match ) ) {
			$values[ $key ] = html_entity_decode( wp_strip_all_tags( $match[1] ), ENT_QUOTES, 'UTF-8' );
			if ( 'a' === $tag ) {
				$link = new WP_HTML_Tag_Processor( $html );
				$link->next_tag( 'a' );
				$values[ $key . '_url' ] = (string) $link->get_attribute( 'href' );
			}
		}
	}
	return $values;
}

function fiberlan_hero_apply( $content, $changes ) {
	if ( ! $changes ) {
		return $content;
	}
	$blocks = parse_blocks( $content );
	$dirty = false;
	foreach ( fiberlan_hero_targets( $blocks ) as $key => &$block ) {
		$html = $block['innerHTML'];
		if ( 'image' === $key ) {
			$image = new WP_HTML_Tag_Processor( $html );
			if ( ! $image->next_tag( 'img' ) ) {
				continue;
			}
			$id = absint( $changes['image'] ?? 0 );
			if ( $id && $id !== absint( $block['attrs']['id'] ?? 0 ) && wp_attachment_is_image( $id ) ) {
				$block['attrs']['id'] = $id;
				$size = $block['attrs']['sizeSlug'] ?? 'full';
				$image->set_attribute( 'src', wp_get_attachment_image_url( $id, $size ) );
				$image->remove_attribute( 'srcset' );
				$image->remove_attribute( 'sizes' );
				foreach ( preg_split( '/\s+/', (string) $image->get_attribute( 'class' ), -1, PREG_SPLIT_NO_EMPTY ) as $class ) {
					if ( preg_match( '/^wp-image-\d+$/D', $class ) ) {
						$image->remove_class( $class );
					}
				}
				$image->add_class( 'wp-image-' . $id );
				if ( ! array_key_exists( 'alt', $changes ) ) {
					$image->set_attribute( 'alt', (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
				}
			}
			if ( array_key_exists( 'alt', $changes ) ) {
				$image->set_attribute( 'alt', $changes['alt'] );
			}
			$html = $image->get_updated_html();
			if ( $id && 'media' === ( $block['attrs']['linkDestination'] ?? '' ) ) {
				$link = new WP_HTML_Tag_Processor( $html );
				if ( $link->next_tag( 'a' ) ) {
					$link->set_attribute( 'href', wp_get_attachment_url( $id ) );
					$html = $link->get_updated_html();
				}
			}
		} else {
			$tag = array( 'heading' => 'h1', 'primary' => 'a', 'secondary' => 'a' )[ $key ] ?? 'p';
			if ( array_key_exists( $key, $changes ) ) {
				$html = preg_replace_callback(
					'~(<' . $tag . '\b[^>]*>).*?(</' . $tag . '>)~s',
					static function ( $match ) use ( $changes, $key ) {
						return $match[1] . esc_html( $changes[ $key ] ) . $match[2];
					},
					$html,
					1
				);
			}
			if ( 'a' === $tag && isset( $changes[ $key . '_url' ] ) ) {
				$link = new WP_HTML_Tag_Processor( $html );
				if ( $link->next_tag( 'a' ) ) {
					$link->set_attribute( 'href', $changes[ $key . '_url' ] );
					$html = $link->get_updated_html();
				}
			}
		}
		// These targets are leaf blocks. Preserve the native block and its settings.
		if ( $html !== $block['innerHTML'] ) {
			$dirty = true;
			$block['innerHTML'] = $html;
			$block['innerContent'] = array( $html );
		}
	}
	unset( $block );
	return $dirty ? serialize_blocks( $blocks ) : $content;
}

add_action( 'customize_register', static function ( $manager ) {
	$pages = array( 'pl' => (int) get_option( 'page_on_front' ) );
	if ( function_exists( 'fl_page_id' ) ) {
		$pages['en'] = fl_page_id( 'home', 'en' );
	}

	$registered = array();
	$urls = array();
	foreach ( array_filter( $pages ) as $lang => $post_id ) {
		$values = fiberlan_hero_values( get_post_field( 'post_content', $post_id ) );
		if ( ! $values ) {
			continue;
		}
		$section = 'fiberlan_home_' . $lang;
		$urls[ $section ] = get_permalink( $post_id );
		$manager->add_section( $section, array(
			'title' => 'Strona główna · ' . strtoupper( $lang ),
			'priority' => 15,
			'description' => 'Edytuj pierwszą sekcję strony. Podgląd zmieni się obok. Użyj przycisku u góry, aby opublikować zmiany.',
		) );
		foreach ( fiberlan_hero_controls() as $field => list( $label, $type ) ) {
			if ( ! array_key_exists( $field, $values ) ) {
				continue;
			}
			$id = $section . '_' . $field;
			$sanitize = array( 'image' => 'absint', 'textarea' => 'sanitize_textarea_field' )[ $type ] ?? 'sanitize_text_field';
			if ( in_array( $field, array( 'primary_url', 'secondary_url' ), true ) ) {
				$sanitize = static function ( $value ) {
					return esc_url_raw( $value );
				};
			}
			$manager->add_setting( $id, array(
				'type' => 'fiberlan_hero',
				'default' => 'image' === $type ? 0 : $values[ $field ],
				'sanitize_callback' => $sanitize,
				'validate_callback' => static function ( $validity, $value ) use ( $post_id, $field ) {
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						$validity->add( 'cannot_edit_home', 'Brak uprawnień do edycji tej strony.' );
					}
					if ( 'image' === $field && ! wp_attachment_is_image( absint( $value ) ) ) {
						$validity->add( 'invalid_photo', 'Wybierz zdjęcie z biblioteki mediów.' );
					}
					return $validity;
				},
			) );
			$control = array( 'section' => $section, 'label' => $label, 'type' => $type );
			if ( 'image' === $type ) {
				// Media controls interpret their default as a URL. Supply the stored attachment ID as the actual value.
				$attachment_id = $values[ $field ];
				add_filter( "customize_value_{$id}", static function () use ( $attachment_id ) {
					return $attachment_id;
				} );
				unset( $control['type'] );
				$control['mime_type'] = 'image';
				$manager->add_control( new WP_Customize_Media_Control( $manager, $id, $control ) );
			} else {
				$manager->add_control( $id, $control );
			}
			$registered[ $id ] = array( $post_id, $field );
		}
	}

	add_action( 'customize_controls_enqueue_scripts', static function () use ( $urls ) {
		wp_add_inline_style( 'customize-controls', '[id^="customize-control-fiberlan_home_"][id$="_image"] .remove-button { display: none; }' );
		wp_add_inline_script( 'customize-controls', 'wp.customize.bind("ready", function () {
			const api = wp.customize;
			const sections = Object.entries(' . wp_json_encode( $urls, JSON_HEX_TAG ) . ');
			const noticeId = "site_editor_block_theme_notice";
			const notice = api.notifications(noticeId);
			sections.forEach(([id, url]) => {
				api.section(id, section => section.expanded.bind(open => {
					if (open && url) api.previewer.previewUrl.set(url);
					if (!notice) return;
					if (sections.some(([name]) => api.section(name)?.expanded())) api.notifications.remove(noticeId);
					else if (!api.notifications.has(noticeId)) api.notifications.add(notice);
				}));
			});
		});' );
	} );

	add_filter( 'the_content', static function ( $content ) use ( $manager, $registered ) {
		if ( ! is_customize_preview() || $manager->doing_ajax() ) {
			return $content;
		}
		$changes = array();
		foreach ( $registered as $id => list( $post_id, $field ) ) {
			if ( get_the_ID() === $post_id ) {
				$value = $manager->get_setting( $id )->post_value( null );
				if ( null !== $value ) {
					$changes[ $field ] = $value;
				}
			}
		}
		return fiberlan_hero_apply( $content, $changes );
	}, 8 );

	$pending = array();
	add_action( 'customize_update_fiberlan_hero', static function ( $value, $setting ) use ( $registered, &$pending ) {
		list( $post_id, $field ) = $registered[ $setting->id ];
		if ( current_user_can( 'edit_post', $post_id ) ) {
			$pending[ $post_id ][ $field ] = $value;
		}
	}, 10, 2 );
	add_action( 'customize_save_after', static function () use ( &$pending ) {
		foreach ( $pending as $post_id => $changes ) {
			$original = get_post_field( 'post_content', $post_id );
			$content = fiberlan_hero_apply( $original, $changes );
			if ( $content !== $original ) {
				$result = wp_update_post( wp_slash( array( 'ID' => $post_id, 'post_content' => $content ) ), true );
				if ( is_wp_error( $result ) && wp_doing_ajax() ) {
					wp_send_json_error( $result );
				}
			}
		}
		$pending = array();
	} );
} );
