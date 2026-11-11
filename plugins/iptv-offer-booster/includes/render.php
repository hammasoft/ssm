<?php
/**
 * Génération du HTML de l'offre (commun à tous les modèles).
 *
 * @package IPTV_Offer_Booster
 */

defined( 'ABSPATH' ) || exit;

/**
 * HTML de l'offre.
 *
 * @param array  $s        Réglages.
 * @param string $template Modèle (topbar|popup|exit|slidein|banner|inline).
 */
function iob_render( array $s, $template = '' ) {
	$template = $template ?: $s['template'];
	$styles   = iob_styles();
	list( , $c1, $c2, $fg ) = $styles[ $s['style'] ] ?? $styles['violet'];
	$end      = iob_end_timestamp( $s );
	$is_modal = in_array( $template, array( 'popup', 'exit' ), true );
	$title_id = 'iob-title-' . wp_rand( 100, 999 );
	$target   = $s['new_tab'] ? ' target="_blank" rel="noopener"' : '';

	$countdown = '';
	if ( $s['countdown'] && $end ) {
		$countdown = sprintf(
			'<div class="iob-countdown" data-end="%1$d" aria-live="off"><span class="iob-countdown__label">%2$s</span><span class="iob-countdown__units"><span><b data-u="d">00</b><small>%3$s</small></span><span><b data-u="h">00</b><small>%4$s</small></span><span><b data-u="m">00</b><small>%5$s</small></span><span><b data-u="s">00</b><small>%6$s</small></span></span></div>',
			(int) $end,
			esc_html__( 'Fin de l’offre dans', 'iptv-offer-booster' ),
			esc_html__( 'j', 'iptv-offer-booster' ),
			esc_html__( 'h', 'iptv-offer-booster' ),
			esc_html__( 'min', 'iptv-offer-booster' ),
			esc_html__( 's', 'iptv-offer-booster' )
		);
	} elseif ( $end && 'topbar' !== $template ) {
		/* translators: %s: date */
		$countdown = '<p class="iob-until">' . esc_html( sprintf( __( 'Offre valable jusqu’au %s', 'iptv-offer-booster' ), wp_date( get_option( 'date_format' ) . ' H:i', $end ) ) ) . '</p>';
	}

	$prices = '';
	if ( $s['new_price'] ) {
		$prices = '<p class="iob-prices">'
			. ( $s['old_price'] ? '<del aria-label="' . esc_attr__( 'Prix habituel', 'iptv-offer-booster' ) . '">' . esc_html( $s['old_price'] ) . '</del> ' : '' )
			. '<strong>' . esc_html( $s['new_price'] ) . '</strong>'
			. ( $s['price_note'] ? ' <small>' . esc_html( $s['price_note'] ) . '</small>' : '' )
			. '</p>';
	}

	$coupon = '';
	if ( $s['coupon'] ) {
		$coupon = sprintf(
			'<div class="iob-coupon"><span>%1$s</span><code>%2$s</code><button type="button" class="iob-copy" data-code="%2$s" data-done="%4$s">%3$s</button></div>',
			esc_html__( 'Code :', 'iptv-offer-booster' ),
			esc_attr( $s['coupon'] ),
			esc_html__( 'Copier', 'iptv-offer-booster' ),
			esc_attr__( 'Copié ✓', 'iptv-offer-booster' )
		);
	}

	$cta = sprintf( '<a class="iob-cta" href="%s"%s data-iob-click>%s</a>', esc_url( $s['url'] ), $target, esc_html( $s['button'] ) );
	$img = ( $s['image'] && in_array( $template, array( 'popup', 'exit', 'banner', 'inline' ), true ) )
		? '<img class="iob-img" src="' . esc_url( $s['image'] ) . '" alt="" loading="lazy" decoding="async">'
		: '';
	$close = 'inline' === $template ? '' : '<button type="button" class="iob-close" aria-label="' . esc_attr__( 'Fermer l’offre', 'iptv-offer-booster' ) . '">&times;</button>';
	$legal = $s['legal'] ? '<p class="iob-legal">' . esc_html( $s['legal'] ) . '</p>' : '';

	$style_attr = sprintf( '--iob-c1:%s;--iob-c2:%s;--iob-fg:%s;', esc_attr( $c1 ), esc_attr( $c2 ), esc_attr( $fg ) );

	if ( 'topbar' === $template ) {
		$inner = sprintf(
			'<div class="iob-topbar__inner">%1$s<p class="iob-topbar__text">%2$s <strong>%3$s</strong></p>%4$s%5$s%6$s</div>%7$s',
			$s['badge'] ? '<span class="iob-badge">' . esc_html( $s['badge'] ) . '</span>' : '',
			'',
			esc_html( $s['title'] ),
			$s['countdown'] && $end ? $countdown : '',
			$coupon,
			$cta,
			$close
		);
	} else {
		$inner = sprintf(
			'%1$s%2$s<div class="iob-body">%3$s<p class="iob-title" id="%4$s">%5$s</p>%6$s%7$s%8$s%9$s%10$s%11$s</div>',
			$close,
			$img,
			$s['badge'] ? '<span class="iob-badge">' . esc_html( $s['badge'] ) . '</span>' : '',
			esc_attr( $title_id ),
			esc_html( $s['title'] ),
			$s['text'] ? '<p class="iob-text">' . esc_html( $s['text'] ) . '</p>' : '',
			$prices,
			$countdown,
			$coupon,
			$cta,
			$legal
		);
	}

	$html = sprintf(
		'<div class="iob iob--%1$s iob--style-%2$s" style="%3$s" data-template="%1$s"%4$s hidden>%5$s</div>',
		esc_attr( $template ),
		esc_attr( $s['style'] ),
		$style_attr,
		$is_modal ? ' role="dialog" aria-modal="true" aria-labelledby="' . esc_attr( $title_id ) . '"' : ( 'topbar' === $template ? ' role="region" aria-label="' . esc_attr__( 'Offre promotionnelle', 'iptv-offer-booster' ) . '"' : '' ),
		$is_modal ? '<div class="iob-overlay" data-iob-close></div><div class="iob-dialog" tabindex="-1">' . $inner . '</div>' : '<div class="iob-card">' . $inner . '</div>'
	);

	// Le shortcode est affiché immédiatement, sans JS.
	if ( 'inline' === $template ) {
		$html = str_replace( ' hidden>', '>', $html );
	}
	return $html;
}
