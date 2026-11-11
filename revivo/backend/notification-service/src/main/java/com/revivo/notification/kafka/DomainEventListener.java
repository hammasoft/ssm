package com.revivo.notification.kafka;

import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.DeserializationFeature;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.revivo.events.DomainEvent;
import com.revivo.events.Topics;
import com.revivo.notification.application.NotificationService;
import org.apache.kafka.clients.consumer.ConsumerRecord;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.kafka.annotation.KafkaListener;
import org.springframework.stereotype.Component;

/**
 * Point d'entrée Kafka. La désérialisation polymorphe s'appuie sur le discriminant
 * {@code type} du contrat partagé ; les champs inconnus sont ignorés (compatibilité ascendante).
 */
@Component
public class DomainEventListener {

    private static final Logger log = LoggerFactory.getLogger(DomainEventListener.class);

    private final ObjectMapper mapper;
    private final NotificationService notificationService;

    public DomainEventListener(ObjectMapper objectMapper, NotificationService notificationService) {
        this.mapper = objectMapper.copy().configure(DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false);
        this.notificationService = notificationService;
    }

    @KafkaListener(id = "notification-domain-events", topics = {
            Topics.USERS, Topics.CATALOG, Topics.OFFERS, Topics.ORDERS, Topics.MESSAGING, Topics.REVIEWS})
    public void onEvent(ConsumerRecord<String, String> record) throws JsonProcessingException {
        DomainEvent event = mapper.readValue(record.value(), DomainEvent.class);
        log.debug("Reçu {} depuis {}-{}@{}", event.eventType(), record.topic(), record.partition(), record.offset());
        notificationService.handle(event);
    }
}
