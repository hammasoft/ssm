package com.revivo.events;

/**
 * Noms des topics Kafka. Un topic par bounded context : l'ordre des événements
 * est garanti par agrégat grâce à la clé de partition {@link DomainEvent#key()}.
 */
public final class Topics {

    public static final String USERS = "revivo.users.v1";
    public static final String CATALOG = "revivo.catalog.v1";
    public static final String OFFERS = "revivo.offers.v1";
    public static final String ORDERS = "revivo.orders.v1";
    public static final String MESSAGING = "revivo.messaging.v1";
    public static final String REVIEWS = "revivo.reviews.v1";

    /** Suffixe utilisé par le DeadLetterPublishingRecoverer de Spring Kafka. */
    public static final String DLT_SUFFIX = "-dlt";

    public static final String[] ALL = {USERS, CATALOG, OFFERS, ORDERS, MESSAGING, REVIEWS};

    private Topics() {
    }
}
