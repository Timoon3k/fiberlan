<?php
/**
 * Title: Strona główna · Wprowadzenie
 * Slug: fiberlan/home-hero
 * Categories: fiberlan
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"fl-hero","layout":{"type":"default"}} -->
<section class="wp-block-group fl-hero">
<!-- wp:group {"className":"fl-shell fl-hero-grid","layout":{"type":"default"}} -->
<div class="wp-block-group fl-shell fl-hero-grid">
<!-- wp:group {"className":"fl-hero-copy","layout":{"type":"default"}} -->
<div class="wp-block-group fl-hero-copy">
<!-- wp:paragraph {"className":"fl-eyebrow"} -->
<p class="fl-eyebrow">Profesjonalne usługi telekomunikacyjne</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Spawanie światłowodów</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"fl-lead"} -->
<p class="fl-lead">Łączymy, instalujemy i mierzymy sieci światłowodowe dla firm, przemysłu i centrów danych. Pracujemy w Polsce i za granicą.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"className":"fl-actions"} -->
<div class="wp-block-buttons fl-actions">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="mailto:info@fiberlan.pl?subject=Wycena%20instalacji%20%C5%9Bwiat%C5%82owodowej">Zapytaj o wycenę</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#uslugi">Poznaj nasze usługi</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<?php echo fiberlan_image_block( 'fiber-splicer-cleaver-client-photo.jpg', 'Spawarka światłowodowa z otwartą osłoną, obcinarką i akcesoriami.', 'fl-hero-figure', 'sprzet', 'Spawarka światłowodowa z obcinarką' ); ?>
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
