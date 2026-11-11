package com.revivo.notification.application;

import com.revivo.events.DomainEvent;
import com.revivo.events.ItemFavorited;
import com.revivo.events.ItemPriceDropped;
import com.revivo.events.ItemPublished;
import com.revivo.events.MessageSent;
import com.revivo.events.OfferAnswered;
import com.revivo.events.OfferMade;
import com.revivo.events.OrderPlaced;
import com.revivo.events.OrderStatusChanged;
import com.revivo.events.ReviewPosted;
import com.revivo.events.UserFollowed;
import com.revivo.events.UserRegistered;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.text.NumberFormat;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import static com.revivo.notification.application.NotificationDraft.inApp;
import static com.revivo.notification.application.NotificationDraft.withEmail;

/**
 * Traduit chaque événement métier en notifications destinées aux membres concernés.
 * Fonction pure (sans effet de bord) : facile à tester unitairement.
 */
@Component
public class NotificationFactory {

    public List<NotificationDraft> draftsFor(DomainEvent event) {
        return switch (event) {
            case UserRegistered e -> List.of(withEmail(e.userId(), "WELCOME", "Bienvenue sur Revivo, " + e.username() + " 👋",
                    "Videz votre dressing, trouvez des pépites : publiez votre première annonce en 2 minutes.", "/sell"));
            case UserFollowed e -> List.of(inApp(e.followedId(), "NEW_FOLLOWER", e.followerUsername() + " vous suit",
                    "Ses prochaines visites dans votre dressing seront peut-être des achats !", "/members/" + e.followerId()));
            case ItemPublished e -> e.followerIds().stream()
                    .map(id -> inApp(id, "FOLLOWED_NEW_ITEM", e.sellerUsername() + " a publié un nouvel article",
                            "« " + e.title() + " » à " + eur(e.price()), "/items/" + e.itemId()))
                    .toList();
            case ItemFavorited e -> List.of(inApp(e.sellerId(), "ITEM_FAVORITED",
                    e.username() + " a ajouté votre article à ses favoris",
                    "« " + e.title() + " » — pensez à lui faire une offre !", "/items/" + e.itemId()));
            case ItemPriceDropped e -> e.interestedUserIds().stream()
                    .map(id -> inApp(id, "PRICE_DROP", "Baisse de prix sur un de vos favoris",
                            "« " + e.title() + " » passe de " + eur(e.oldPrice()) + " à " + eur(e.newPrice()),
                            "/items/" + e.itemId()))
                    .toList();
            case OfferMade e -> List.of(withEmail(e.sellerId(), "OFFER_RECEIVED",
                    "Nouvelle offre de " + eur(e.amount()),
                    e.buyerUsername() + " propose " + eur(e.amount()) + " pour « " + e.itemTitle() + " ».", "/offers"));
            case OfferAnswered e -> List.of(e.accepted()
                    ? withEmail(e.buyerId(), "OFFER_ACCEPTED", "Offre acceptée 🎉",
                    "Vous pouvez acheter « " + e.itemTitle() + " » à " + eur(e.amount()) + " pendant 24 h.",
                    "/items/" + e.itemId())
                    : inApp(e.buyerId(), "OFFER_DECLINED", "Offre refusée",
                    "Le vendeur a décliné votre offre de " + eur(e.amount()) + " pour « " + e.itemTitle() + " ».",
                    "/items/" + e.itemId()));
            case OrderPlaced e -> List.of(
                    withEmail(e.sellerId(), "ITEM_SOLD", "Vendu ! 🎉",
                            "« " + e.itemTitle() + " » a été acheté. Expédiez-le sous 5 jours : "
                                    + eur(e.itemPrice()) + " vous attendent.", "/orders/" + e.orderId()),
                    withEmail(e.buyerId(), "ORDER_CONFIRMED", "Paiement confirmé",
                            "Votre achat « " + e.itemTitle() + " » (" + eur(e.total()) + ") est protégé jusqu'à la réception.",
                            "/orders/" + e.orderId()));
            case OrderStatusChanged e -> orderStatus(e);
            case ReviewPosted e -> List.of(inApp(e.targetUserId(), "REVIEW_RECEIVED",
                    "Nouvel avis " + stars(e.rating()), e.authorUsername() + " a évalué votre transaction.",
                    "/members/" + e.targetUserId()));
            case MessageSent e -> List.of(inApp(e.recipientId(), "NEW_MESSAGE",
                    "Nouveau message de " + e.senderUsername(), e.preview(), "/inbox/" + e.conversationId()));
            default -> List.of();
        };
    }

    private List<NotificationDraft> orderStatus(OrderStatusChanged e) {
        String link = "/orders/" + e.orderId();
        String item = "« " + e.itemTitle() + " »";
        List<NotificationDraft> drafts = new ArrayList<>();
        switch (e.newStatus()) {
            case "SHIPPED" -> drafts.add(withEmail(e.buyerId(), "ORDER_SHIPPED", "Votre commande est en route 🚚",
                    item + " a été expédié." + (e.detail() == null ? "" : " " + e.detail()), link));
            case "DELIVERED" -> drafts.add(withEmail(e.buyerId(), "ORDER_DELIVERED", "Colis livré",
                    "Vérifiez " + item + " et confirmez que tout est OK pour finaliser la commande.", link));
            case "COMPLETED" -> {
                drafts.add(inApp(e.sellerId(), "FUNDS_RELEASED", "Fonds disponibles 💶",
                        "La vente de " + item + " est finalisée : le montant est dans votre porte-monnaie.", link));
                drafts.add(inApp(e.buyerId(), "REVIEW_REQUEST", "Commande finalisée",
                        "Laissez un avis au vendeur de " + item + ".", link));
            }
            case "CANCELLED" -> {
                drafts.add(withEmail(e.buyerId(), "ORDER_CANCELLED", "Commande annulée",
                        item + " — vous allez être intégralement remboursé.", link));
                drafts.add(inApp(e.sellerId(), "ORDER_CANCELLED", "Commande annulée",
                        item + " est de nouveau en vente.", link));
            }
            case "DISPUTED" -> drafts.add(withEmail(e.sellerId(), "ORDER_DISPUTED", "Litige ouvert",
                    "L'acheteur de " + item + " a signalé un problème : " + e.detail(), link));
            case "REFUNDED" -> {
                drafts.add(withEmail(e.buyerId(), "ORDER_REFUNDED", "Remboursement effectué",
                        "Le litige sur " + item + " a été résolu en votre faveur.", link));
                drafts.add(inApp(e.sellerId(), "ORDER_REFUNDED", "Litige clôturé",
                        "L'acheteur de " + item + " a été remboursé.", link));
            }
            default -> {
            }
        }
        return drafts;
    }

    static String eur(BigDecimal amount) {
        return NumberFormat.getCurrencyInstance(Locale.FRANCE).format(amount).replace(' ', ' ').replace(' ', ' ');
    }

    private static String stars(int rating) {
        return "★".repeat(rating) + "☆".repeat(5 - rating);
    }
}
