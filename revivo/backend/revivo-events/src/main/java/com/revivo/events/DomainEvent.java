package com.revivo.events;

import com.fasterxml.jackson.annotation.JsonIgnore;
import com.fasterxml.jackson.annotation.JsonSubTypes;
import com.fasterxml.jackson.annotation.JsonTypeInfo;

import java.time.Instant;
import java.util.UUID;

/**
 * Contrat commun de tous les événements métier publiés sur Kafka.
 * <p>
 * La sérialisation est polymorphe : le champ JSON {@code type} porte le nom logique
 * de l'événement, ce qui découple le contrat des noms de classes Java.
 * Toute évolution doit rester rétro-compatible (ajout de champs uniquement),
 * sinon on crée un nouveau type / topic versionné (cf. docs/kafka-events.md).
 */
@JsonTypeInfo(use = JsonTypeInfo.Id.NAME, property = "type")
@JsonSubTypes({
        @JsonSubTypes.Type(value = UserRegistered.class, name = "UserRegistered"),
        @JsonSubTypes.Type(value = UserFollowed.class, name = "UserFollowed"),
        @JsonSubTypes.Type(value = ItemPublished.class, name = "ItemPublished"),
        @JsonSubTypes.Type(value = ItemFavorited.class, name = "ItemFavorited"),
        @JsonSubTypes.Type(value = ItemPriceDropped.class, name = "ItemPriceDropped"),
        @JsonSubTypes.Type(value = OfferMade.class, name = "OfferMade"),
        @JsonSubTypes.Type(value = OfferAnswered.class, name = "OfferAnswered"),
        @JsonSubTypes.Type(value = OrderPlaced.class, name = "OrderPlaced"),
        @JsonSubTypes.Type(value = OrderStatusChanged.class, name = "OrderStatusChanged"),
        @JsonSubTypes.Type(value = ReviewPosted.class, name = "ReviewPosted"),
        @JsonSubTypes.Type(value = MessageSent.class, name = "MessageSent")
})
public interface DomainEvent {

    /** Identifiant unique : sert à l'idempotence côté consommateurs. */
    UUID eventId();

    Instant occurredAt();

    /** Clé de partition Kafka (identifiant de l'agrégat). */
    @JsonIgnore
    String key();

    /** Topic de destination. */
    @JsonIgnore
    String topic();

    /** Nom logique de l'événement (identique au discriminant JSON). */
    @JsonIgnore
    default String eventType() {
        return getClass().getSimpleName();
    }

    static UUID newId() {
        return UUID.randomUUID();
    }
}
