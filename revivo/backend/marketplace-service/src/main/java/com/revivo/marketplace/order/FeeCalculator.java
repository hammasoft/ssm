package com.revivo.marketplace.order;

import com.revivo.marketplace.common.Money;
import com.revivo.marketplace.config.RevivoProperties;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;

/**
 * Calcul des frais côté acheteur. Le vendeur ne paie aucune commission :
 * il reçoit exactement le prix de l'article (modèle « frais acheteur »).
 */
@Component
public class FeeCalculator {

    private final RevivoProperties.Fees fees;

    public FeeCalculator(RevivoProperties properties) {
        this.fees = properties.fees();
    }

    /** Protection acheteur : fixe + pourcentage du prix de l'article. */
    public BigDecimal protectionFee(BigDecimal itemPrice) {
        return Money.scale(fees.protectionFixed().add(itemPrice.multiply(fees.protectionRate())));
    }

    public BigDecimal shippingPrice(ShippingOption option) {
        return switch (option) {
            case RELAY_POINT -> Money.scale(fees.relayPointShipping());
            case HOME_DELIVERY -> Money.scale(fees.homeDeliveryShipping());
            case HAND_DELIVERY -> Money.scale(BigDecimal.ZERO);
        };
    }

    public BigDecimal priceWithProtection(BigDecimal itemPrice) {
        return Money.scale(itemPrice.add(protectionFee(itemPrice)));
    }
}
