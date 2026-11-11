package com.revivo.notification.application;

import com.revivo.events.DomainEvent;
import com.revivo.events.UserRegistered;
import com.revivo.notification.domain.Notification;
import com.revivo.notification.domain.NotificationRepository;
import com.revivo.notification.domain.ProcessedEvent;
import com.revivo.notification.domain.ProcessedEventRepository;
import com.revivo.notification.domain.UserDirectoryEntry;
import com.revivo.notification.domain.UserDirectoryRepository;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.context.ApplicationEventPublisher;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Instant;
import java.util.ArrayList;
import java.util.List;

@Service
public class NotificationService {

    private static final Logger log = LoggerFactory.getLogger(NotificationService.class);

    private final NotificationRepository notifications;
    private final ProcessedEventRepository processedEvents;
    private final UserDirectoryRepository directory;
    private final NotificationFactory factory;
    private final ApplicationEventPublisher publisher;

    public NotificationService(NotificationRepository notifications, ProcessedEventRepository processedEvents,
                               UserDirectoryRepository directory, NotificationFactory factory,
                               ApplicationEventPublisher publisher) {
        this.notifications = notifications;
        this.processedEvents = processedEvents;
        this.directory = directory;
        this.factory = factory;
        this.publisher = publisher;
    }

    /**
     * Traitement idempotent d'un événement : la trace {@code processed_events} et les
     * notifications sont écrites dans la même transaction.
     *
     * @return {@code false} si l'événement avait déjà été traité (doublon Kafka)
     */
    @Transactional
    public boolean handle(DomainEvent event) {
        if (processedEvents.existsById(event.eventId())) {
            log.debug("Événement {} déjà traité, ignoré", event.eventId());
            return false;
        }
        processedEvents.save(new ProcessedEvent(event.eventId(), event.eventType(), Instant.now()));

        if (event instanceof UserRegistered e) {
            directory.save(new UserDirectoryEntry(e.userId(), e.username(), e.email()));
        }

        List<NotificationDraft> drafts = factory.draftsFor(event);
        List<NotificationDto> created = new ArrayList<>();
        for (NotificationDraft d : drafts) {
            Notification n = notifications.save(new Notification(d.userId(), d.type(), d.title(), d.body(), d.link(),
                    event.eventId(), event.occurredAt()));
            created.add(NotificationDto.from(n));
        }
        if (!created.isEmpty()) {
            publisher.publishEvent(new NotificationsCreated(created,
                    drafts.stream().map(NotificationDraft::userId).toList(), drafts));
        }
        log.debug("{} → {} notification(s)", event.eventType(), created.size());
        return true;
    }

    @Transactional(readOnly = true)
    public Page<NotificationDto> list(Long userId, int page, int size) {
        return notifications.findByUserIdOrderByCreatedAtDesc(userId, PageRequest.of(page, Math.min(size, 50)))
                .map(NotificationDto::from);
    }

    @Transactional(readOnly = true)
    public long unreadCount(Long userId) {
        return notifications.countByUserIdAndReadFalse(userId);
    }

    @Transactional
    public void markRead(Long userId, Long id) {
        notifications.findByIdAndUserId(id, userId).ifPresent(Notification::markRead);
    }

    @Transactional
    public int markAllRead(Long userId) {
        return notifications.markAllRead(userId);
    }
}
