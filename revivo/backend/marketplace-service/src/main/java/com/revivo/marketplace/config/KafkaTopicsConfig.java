package com.revivo.marketplace.config;

import com.revivo.events.Topics;
import org.apache.kafka.clients.admin.NewTopic;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.kafka.config.TopicBuilder;
import org.springframework.kafka.core.KafkaAdmin;

import java.util.Arrays;

/** Déclaration des topics : créés au démarrage s'ils n'existent pas (3 partitions). */
@Configuration
public class KafkaTopicsConfig {

    @Bean
    KafkaAdmin.NewTopics revivoTopics() {
        return new KafkaAdmin.NewTopics(Arrays.stream(Topics.ALL)
                .map(name -> TopicBuilder.name(name).partitions(3).replicas(1).build())
                .toArray(NewTopic[]::new));
    }
}
