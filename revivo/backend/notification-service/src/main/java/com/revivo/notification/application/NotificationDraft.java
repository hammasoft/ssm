package com.revivo.notification.application;

/**
 * Notification à créer pour un destinataire.
 *
 * @param sendEmail si vrai, un e-mail transactionnel est également envoyé
 */
public record NotificationDraft(Long userId, String type, String title, String body, String link,
                                boolean sendEmail) {

    static NotificationDraft inApp(Long userId, String type, String title, String body, String link) {
        return new NotificationDraft(userId, type, title, body, link, false);
    }

    static NotificationDraft withEmail(Long userId, String type, String title, String body, String link) {
        return new NotificationDraft(userId, type, title, body, link, true);
    }
}
