package com.revivo.marketplace.outbox;

import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.revivo.events.DomainEvent;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Propagation;
import org.springframework.transaction.annotation.Transactional;

/**
 * Point d'entrée unique pour émettre un événement métier.
 * <p>
 * <b>Transactional Outbox</b> : l'événement est écrit dans la même transaction que la
 * modification métier. Il ne peut donc ni être perdu (commit métier sans événement), ni
 * être publié pour une transaction annulée. {@link OutboxRelay} se charge ensuite de
 * l'envoi vers Kafka (livraison at-least-once, consommateurs idempotents).
 */
@Component
public class DomainEventPublisher {

    private final OutboxEventRepository repository;
    private final ObjectMapper objectMapper;

    public DomainEventPublisher(OutboxEventRepository repository, ObjectMapper objectMapper) {
        this.repository = repository;
        this.objectMapper = objectMapper;
    }

    @Transactional(propagation = Propagation.MANDATORY)
    public void publish(DomainEvent event) {
        try {
            String payload = objectMapper.writerFor(DomainEvent.class).writeValueAsString(event);
            repository.save(new OutboxEvent(event.eventId(), event.topic(), event.key(), event.eventType(),
                    payload, event.occurredAt()));
        } catch (JsonProcessingException e) {
            throw new IllegalStateException("Sérialisation impossible de " + event.eventType(), e);
        }
    }
}
