package com.revivo.marketplace.order;

import com.revivo.marketplace.config.RevivoProperties;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.time.Clock;

@Component
class OrderScheduler {

    private static final Logger log = LoggerFactory.getLogger(OrderScheduler.class);

    private final OrderService orderService;
    private final RevivoProperties properties;
    private final Clock clock;

    OrderScheduler(OrderService orderService, RevivoProperties properties, Clock clock) {
        this.orderService = orderService;
        this.properties = properties;
        this.clock = clock;
    }

    @Scheduled(fixedDelayString = "PT10M", initialDelayString = "PT1M")
    void autoComplete() {
        int done = orderService.autoCompleteDelivered(clock.instant().minus(properties.orders().autoCompleteAfter()));
        if (done > 0) {
            log.info("{} commandes validées automatiquement", done);
        }
    }
}
