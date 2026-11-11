package com.revivo.marketplace.outbox;

import com.revivo.marketplace.config.RevivoProperties;
import org.apache.kafka.clients.producer.ProducerRecord;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.kafka.core.KafkaTemplate;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

import java.nio.charset.StandardCharsets;
import java.time.Clock;
import java.time.Duration;
import java.util.List;
import java.util.concurrent.TimeUnit;

/**
 * Relaie les événements de l'outbox vers Kafka (polling publisher).
 * Les échecs sont comptabilisés et retentés au cycle suivant ; l'ordre par agrégat est
 * préservé car on s'arrête au premier échec du lot.
 */
@Component
public class OutboxRelay {

    private static final Logger log = LoggerFactory.getLogger(OutboxRelay.class);

    private final OutboxEventRepository repository;
    private final KafkaTemplate<String, String> kafkaTemplate;
    private final RevivoProperties properties;
    private final Clock clock;

    public OutboxRelay(OutboxEventRepository repository, KafkaTemplate<String, String> kafkaTemplate,
                       RevivoProperties properties, Clock clock) {
        this.repository = repository;
        this.kafkaTemplate = kafkaTemplate;
        this.properties = properties;
        this.clock = clock;
    }

    @Scheduled(fixedDelayString = "${revivo.outbox.poll-interval-ms:1000}")
    @Transactional
    public void relay() {
        List<OutboxEvent> batch = repository.lockNextBatch(properties.outbox().batchSize());
        for (OutboxEvent event : batch) {
            try {
                ProducerRecord<String, String> record =
                        new ProducerRecord<>(event.getTopic(), event.getEventKey(), event.getPayload());
                record.headers().add("eventType", event.getEventType().getBytes(StandardCharsets.UTF_8));
                record.headers().add("eventId", event.getId().toString().getBytes(StandardCharsets.UTF_8));
                kafkaTemplate.send(record).get(10, TimeUnit.SECONDS);
                event.markPublished(clock.instant());
            } catch (Exception e) {
                if (e instanceof InterruptedException) {
                    Thread.currentThread().interrupt();
                }
                event.markFailed(e.getMessage());
                log.warn("Publication Kafka échouée pour {} ({}), tentative {} : {}",
                        event.getEventType(), event.getId(), event.getAttempts(), e.getMessage());
                break;
            }
        }
    }

    /** Purge quotidienne des événements publiés depuis plus de 7 jours. */
    @Scheduled(cron = "0 30 3 * * *")
    @Transactional
    public void purge() {
        int deleted = repository.deletePublishedBefore(clock.instant().minus(Duration.ofDays(7)));
        log.info("Outbox : {} événements publiés purgés", deleted);
    }
}
