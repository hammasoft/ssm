package com.revivo.notification.application;

import com.revivo.notification.config.NotificationProperties;
import com.revivo.notification.domain.UserDirectoryRepository;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.mail.MailException;
import org.springframework.mail.SimpleMailMessage;
import org.springframework.mail.javamail.JavaMailSender;
import org.springframework.scheduling.annotation.Async;
import org.springframework.stereotype.Component;
import org.springframework.transaction.event.TransactionPhase;
import org.springframework.transaction.event.TransactionalEventListener;

/**
 * Envoi des e-mails transactionnels, après commit et de façon asynchrone :
 * une panne SMTP ne bloque jamais la consommation Kafka.
 * En local, les e-mails sont visibles dans Mailpit (http://localhost:8025).
 */
@Component
public class EmailNotifier {

    private static final Logger log = LoggerFactory.getLogger(EmailNotifier.class);

    private final JavaMailSender mailSender;
    private final UserDirectoryRepository directory;
    private final NotificationProperties properties;

    public EmailNotifier(JavaMailSender mailSender, UserDirectoryRepository directory,
                         NotificationProperties properties) {
        this.mailSender = mailSender;
        this.directory = directory;
        this.properties = properties;
    }

    @Async
    @TransactionalEventListener(phase = TransactionPhase.AFTER_COMMIT)
    public void onNotificationsCreated(NotificationsCreated event) {
        if (!properties.mail().enabled()) {
            return;
        }
        event.drafts().stream().filter(NotificationDraft::sendEmail).forEach(this::send);
    }

    private void send(NotificationDraft draft) {
        directory.findById(draft.userId()).ifPresentOrElse(user -> {
            SimpleMailMessage message = new SimpleMailMessage();
            message.setFrom(properties.mail().from());
            message.setTo(user.getEmail());
            message.setSubject("[Revivo] " + draft.title());
            message.setText("Bonjour " + user.getUsername() + ",\n\n" + draft.body() + "\n\n"
                    + properties.frontendUrl() + draft.link() + "\n\n— L'équipe Revivo");
            try {
                mailSender.send(message);
            } catch (MailException e) {
                log.warn("Échec d'envoi d'e-mail à l'utilisateur {} : {}", draft.userId(), e.getMessage());
            }
        }, () -> log.debug("Pas d'adresse connue pour l'utilisateur {}", draft.userId()));
    }
}
