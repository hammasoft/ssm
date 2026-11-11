package com.revivo.notification.config;

import com.fasterxml.jackson.core.JsonProcessingException;
import com.revivo.events.Topics;
import org.apache.kafka.common.TopicPartition;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.kafka.core.KafkaTemplate;
import org.springframework.kafka.listener.DeadLetterPublishingRecoverer;
import org.springframework.kafka.listener.DefaultErrorHandler;
import org.springframework.util.backoff.ExponentialBackOff;

/**
 * Gestion des erreurs de consommation :
 * <ul>
 *   <li>erreurs transitoires (base indisponible…) : 4 tentatives avec back-off exponentiel ;</li>
 *   <li>messages illisibles (« poison pills ») : pas de retry ;</li>
 *   <li>dans tous les cas, échec définitif → publication dans {@code <topic>-dlt} pour analyse / rejeu.</li>
 * </ul>
 */
@Configuration
public class KafkaConsumerConfig {

    @Bean
    DefaultErrorHandler kafkaErrorHandler(KafkaTemplate<String, String> template) {
        DeadLetterPublishingRecoverer recoverer = new DeadLetterPublishingRecoverer(template,
                (record, ex) -> new TopicPartition(record.topic() + Topics.DLT_SUFFIX, -1));
        ExponentialBackOff backOff = new ExponentialBackOff(500L, 2.0);
        backOff.setMaxAttempts(4);
        DefaultErrorHandler handler = new DefaultErrorHandler(recoverer, backOff);
        handler.addNotRetryableExceptions(JsonProcessingException.class, IllegalArgumentException.class);
        return handler;
    }
}
