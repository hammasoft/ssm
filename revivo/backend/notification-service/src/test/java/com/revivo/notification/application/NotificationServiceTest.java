package com.revivo.notification.application;

import com.revivo.events.DomainEvent;
import com.revivo.events.UserRegistered;
import com.revivo.notification.domain.Notification;
import com.revivo.notification.domain.NotificationRepository;
import com.revivo.notification.domain.ProcessedEventRepository;
import com.revivo.notification.domain.UserDirectoryEntry;
import com.revivo.notification.domain.UserDirectoryRepository;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.context.ApplicationEventPublisher;

import java.time.Instant;

import static org.assertj.core.api.Assertions.assertThat;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class NotificationServiceTest {

    @Mock NotificationRepository notifications;
    @Mock ProcessedEventRepository processedEvents;
    @Mock UserDirectoryRepository directory;
    @Mock ApplicationEventPublisher publisher;

    @Test
    void duplicateEventsAreIgnored() {
        var service = new NotificationService(notifications, processedEvents, directory, new NotificationFactory(), publisher);
        var event = new UserRegistered(DomainEvent.newId(), Instant.now(), 1L, "alice", "alice@test.dev");
        when(processedEvents.existsById(event.eventId())).thenReturn(true);

        assertThat(service.handle(event)).isFalse();
        verify(notifications, never()).save(any());
        verify(publisher, never()).publishEvent(any());
    }

    @Test
    void userRegisteredFeedsLocalDirectoryAndCreatesWelcomeNotification() {
        var service = new NotificationService(notifications, processedEvents, directory, new NotificationFactory(), publisher);
        var event = new UserRegistered(DomainEvent.newId(), Instant.now(), 1L, "alice", "alice@test.dev");
        when(notifications.save(any(Notification.class))).thenAnswer(inv -> inv.getArgument(0));

        assertThat(service.handle(event)).isTrue();
        verify(directory).save(any(UserDirectoryEntry.class));
        verify(publisher).publishEvent(any(NotificationsCreated.class));
    }
}
