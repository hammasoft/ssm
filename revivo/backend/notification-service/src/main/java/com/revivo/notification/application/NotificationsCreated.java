package com.revivo.notification.application;

import java.util.List;

/** Événement applicatif interne, traité APRÈS commit (push SSE + e-mails). */
public record NotificationsCreated(List<NotificationDto> notifications, List<Long> userIds,
                                   List<NotificationDraft> drafts) {
}
