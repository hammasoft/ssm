package com.revivo.marketplace.order;

public enum ShippingOption {
    RELAY_POINT("Point relais"),
    HOME_DELIVERY("Livraison à domicile"),
    HAND_DELIVERY("Remise en main propre");

    private final String label;

    ShippingOption(String label) {
        this.label = label;
    }

    public String label() {
        return label;
    }

    public boolean requiresAddress() {
        return this != HAND_DELIVERY;
    }
}
