<?php
/**
 * Page d'accueil — entonnoir « urgence → appel ».
 *
 * 1. Hero : promesse + délai + bouton d'appel (au-dessus de la ligne de flottaison mobile)
 * 2. Les 10 dépannages les plus recherchés (ordre = volume de recherche)
 * 3. Comment ça marche (3 étapes)
 * 4. Tarifs indicatifs (lever la peur de l'arnaque)
 * 5. Pourquoi nous / preuves
 * 6. Zones d'intervention (SEO local)
 * 7. Derniers conseils (maillage vers le blog)
 * 8. FAQ (balisée FAQPage)
 *
 * @package Beffroi
 */

defined( 'ABSPATH' ) || exit;

get_header();
$bd_reviews = (int) bd_business( 'review_count' );
?>

<section class="hero">
	<div class="container hero__inner">
		<div class="hero__copy">
			<p class="hero__kicker">Urgence plomberie &amp; chauffage · Nord-Pas-de-Calais</p>
			<h1 class="hero__title">Plombier chauffagiste en urgence, <span>24h/24 et 7j/7</span></h1>
			<p class="hero__lead">Fuite d'eau, WC bouché, plus d'eau chaude, chaudière en panne&nbsp;? Un artisan qualifié est chez vous en <strong><?php echo esc_html( bd_business( 'delay' ) ); ?></strong> à Lille, Arras, Lens, Douai, Valenciennes, Dunkerque, Calais et alentours.</p>
			<div class="hero__actions">
				<?php echo bd_call_button( 'hero', '', 'btn btn--call btn--xl' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<a class="btn btn--ghost" href="#depannages">Voir les dépannages</a>
			</div>
			<ul class="hero__trust">
				<li>✅ Devis gratuit avant intervention</li>
				<li>✅ Déplacement + diagnostic dès <?php echo esc_html( bd_business( 'price_from' ) ); ?>&nbsp;€</li>
				<li>✅ Artisans assurés, garantie décennale</li>
				<?php if ( $bd_reviews > 0 ) : ?>
					<li>⭐ <?php echo esc_html( bd_business( 'rating' ) ); ?>/5 sur <?php echo (int) $bd_reviews; ?> avis Google</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="hero__card" aria-label="Urgence : les bons réflexes">
			<p class="hero__card-title">🚨 En attendant notre arrivée</p>
			<ol>
				<li>Coupez l'eau au <strong>robinet d'arrêt général</strong> (près du compteur).</li>
				<li>Coupez l'électricité si l'eau approche des prises.</li>
				<li>Odeur de gaz&nbsp;? Sortez et appelez le <strong>0 800 47 33 33</strong> (GRDF).</li>
				<li>Prenez des photos pour votre assurance.</li>
			</ol>
			<?php echo bd_call_button( 'hero_card', 'Appeler maintenant', 'btn btn--call' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>

<section id="depannages" class="section">
	<div class="container">
		<header class="section__head">
			<h2>Les dépannages les plus demandés dans le Nord-Pas-de-Calais</h2>
			<p>Classés selon les recherches les plus fréquentes des habitants du 59 et du 62. Cliquez pour lire nos conseils ou appelez directement.</p>
		</header>
		<div class="services">
			<?php foreach ( bd_services() as $i => $service ) : ?>
				<article class="service<?php echo $i < 3 ? ' service--top' : ''; ?>">
					<?php if ( $i < 3 ) : ?><span class="service__badge">Top <?php echo (int) $i + 1; ?></span><?php endif; ?>
					<span class="service__icon" aria-hidden="true"><?php echo esc_html( $service['icon'] ); ?></span>
					<h3 class="service__title"><?php echo esc_html( $service['title'] ); ?></h3>
					<p class="service__text"><?php echo esc_html( $service['text'] ); ?></p>
					<p class="service__price"><?php echo esc_html( $service['price'] ); ?></p>
					<div class="service__actions">
						<?php echo bd_call_button( 'service_' . sanitize_title( $service['title'] ), 'Appeler', 'btn btn--call btn--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<a class="service__link" href="<?php echo esc_url( bd_post_url( $service['article'] ) ); ?>">Nos conseils →</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--alt">
	<div class="container">
		<header class="section__head"><h2>Comment se passe votre intervention ?</h2></header>
		<ol class="steps">
			<li><strong>1. Vous appelez</strong><span>Un conseiller-plombier qualifie la panne au téléphone et vous donne les gestes de sécurité.</span></li>
			<li><strong>2. Prix annoncé</strong><span>Le tarif du déplacement et une fourchette de prix vous sont communiqués avant le départ.</span></li>
			<li><strong>3. Artisan sur place</strong><span>Diagnostic, devis écrit signé, réparation, puis facture détaillée pour votre assurance.</span></li>
		</ol>
	</div>
</section>

<section class="section">
	<div class="container">
		<header class="section__head">
			<h2>Tarifs indicatifs, sans mauvaise surprise</h2>
			<p>Prix TTC constatés pour une intervention standard en journée. Le prix définitif figure sur le devis signé avant travaux.</p>
		</header>
		<div class="table-wrap">
			<table class="prices">
				<thead><tr><th scope="col">Intervention</th><th scope="col">Fourchette</th></tr></thead>
				<tbody>
					<tr><td>Déplacement + diagnostic</td><td>dès <?php echo esc_html( bd_business( 'price_from' ) ); ?> €</td></tr>
					<tr><td>Réparation de fuite apparente</td><td>90 – 200 €</td></tr>
					<tr><td>Débouchage WC / évier (furet)</td><td>95 – 220 €</td></tr>
					<tr><td>Dépannage chauffe-eau électrique</td><td>90 – 250 €</td></tr>
					<tr><td>Dépannage chaudière gaz</td><td>110 – 280 €</td></tr>
					<tr><td>Recherche de fuite non destructive</td><td>290 – 490 €</td></tr>
					<tr><td>Majoration nuit / dimanche / férié</td><td>annoncée avant départ</td></tr>
				</tbody>
			</table>
		</div>
		<p class="center"><a class="btn btn--ghost" href="<?php echo esc_url( bd_post_url( 'tarifs', 'page' ) ); ?>">Voir tous nos tarifs</a></p>
	</div>
</section>

<section class="section section--alt">
	<div class="container">
		<header class="section__head"><h2>Pourquoi appeler <?php echo esc_html( bd_business( 'name' ) ); ?> ?</h2></header>
		<div class="features">
			<div><strong>⏱ Rapides</strong><p>Artisans répartis dans la métropole lilloise, le Douaisis, le Valenciennois, le bassin minier, l'Arrageois et le littoral.</p></div>
			<div><strong>💶 Transparents</strong><p>Prix annoncé au téléphone, devis écrit signé avant travaux. Aucune facture surprise.</p></div>
			<div><strong>🛠 Qualifiés</strong><p>Plombiers et chauffagistes formés, habilités gaz, équipés caméra, furet électrique et détection de fuite.</p></div>
			<div><strong>🛡 Assurés</strong><p>Responsabilité civile et garantie décennale. Facture et rapport conformes pour votre assureur.</p></div>
		</div>
	</div>
</section>

<section class="section">
	<div class="container">
		<header class="section__head">
			<h2>Zones d'intervention dans le Nord et le Pas-de-Calais</h2>
			<p>Plombier et chauffagiste d'urgence dans plus de 150 communes.</p>
		</header>
		<div class="zones">
			<?php foreach ( bd_zones() as $dept => $cities ) : ?>
				<div class="zones__col">
					<h3><?php echo esc_html( $dept ); ?></h3>
					<ul>
						<?php foreach ( $cities as $city ) : ?>
							<li>Plombier <?php echo esc_html( $city ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
		<p class="center"><a class="btn btn--ghost" href="<?php echo esc_url( bd_post_url( 'zones-intervention', 'page' ) ); ?>">Toutes nos zones</a></p>
	</div>
</section>

<?php
$bd_latest = new WP_Query(
	array(
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( $bd_latest->have_posts() ) :
	?>
	<section id="conseils" class="section section--alt">
		<div class="container">
			<header class="section__head"><h2>Conseils de plombier pour réagir vite</h2></header>
			<div class="cards">
				<?php
				while ( $bd_latest->have_posts() ) :
					$bd_latest->the_post();
					get_template_part( 'template-parts/card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php if ( get_option( 'page_for_posts' ) ) : ?>
				<p class="center"><a class="btn btn--ghost" href="<?php echo esc_url( (string) get_permalink( (int) get_option( 'page_for_posts' ) ) ); ?>">Tous les conseils</a></p>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<section class="section">
	<div class="container container--narrow">
		<header class="section__head"><h2>Questions fréquentes</h2></header>
		<div class="faq">
			<?php foreach ( bd_faq() as $item ) : ?>
				<details>
					<summary><?php echo esc_html( $item['q'] ); ?></summary>
					<p><?php echo esc_html( $item['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_footer();
