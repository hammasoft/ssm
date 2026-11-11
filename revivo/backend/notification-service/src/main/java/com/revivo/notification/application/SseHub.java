package com.revivo.notification.application;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;
import org.springframework.transaction.event.TransactionPhase;
import org.springframework.transaction.event.TransactionalEventListener;
import org.springframework.web.servlet.mvc.method.annotation.SseEmitter;

import java.io.IOException;
import java.time.Duration;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.CopyOnWriteArrayList;

/**
 * Registre des connexions Server-Sent Events par utilisateur (plusieurs onglets possibles).
 * <p>
 * Limite connue : en multi-instances, un utilisateur n'est connecté qu'à une instance.
 * Il suffit alors que chaque instance consomme les événements avec un group-id propre
 * (fan-out) ou d'ajouter un Redis Pub/Sub — cf. ADR-004.
 */
@Component
public class SseHub {

    private static final Logger log = LoggerFactory.getLogger(SseHub.class);
    private static final long TIMEOUT_MS = Duration.ofMinutes(30).toMillis();

    private final Map<Long, List<SseEmitter>> emitters = new ConcurrentHashMap<>();

    public SseEmitter subscribe(Long userId) {
        SseEmitter emitter = new SseEmitter(TIMEOUT_MS);
        emitters.computeIfAbsent(userId, k -> new CopyOnWriteArrayList<>()).add(emitter);
        Runnable remove = () -> remove(userId, emitter);
        emitter.onCompletion(remove);
        emitter.onTimeout(remove);
        emitter.onError(e -> remove.run());
        send(userId, emitter, SseEmitter.event().name("connected").data("ok"));
        return emitter;
    }

    @TransactionalEventListener(phase = TransactionPhase.AFTER_COMMIT)
    public void onNotificationsCreated(NotificationsCreated event) {
        for (int i = 0; i < event.notifications().size(); i++) {
            push(event.userIds().get(i), event.notifications().get(i));
        }
    }

    public void push(Long userId, NotificationDto notification) {
        for (SseEmitter emitter : emitters.getOrDefault(userId, List.of())) {
            send(userId, emitter, SseEmitter.event().name("notification").id(String.valueOf(notification.id()))
                    .data(notification));
        }
    }

    /** Heartbeat : évite la fermeture des connexions inactives par les proxys. */
    @Scheduled(fixedRate = 25_000)
    void heartbeat() {
        emitters.forEach((userId, list) -> list.forEach(e -> send(userId, e, SseEmitter.event().comment("ping"))));
    }

    public int connectedUsers() {
        return emitters.size();
    }

    private void send(Long userId, SseEmitter emitter, SseEmitter.SseEventBuilder event) {
        try {
            emitter.send(event);
        } catch (IOException | IllegalStateException e) {
            log.debug("Connexion SSE fermée pour l'utilisateur {}", userId);
            remove(userId, emitter);
        }
    }

    private void remove(Long userId, SseEmitter emitter) {
        emitters.computeIfPresent(userId, (k, list) -> {
            list.remove(emitter);
            return list.isEmpty() ? null : list;
        });
    }
}
