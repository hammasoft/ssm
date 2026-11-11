package com.revivo.notification.web;

import com.revivo.notification.application.NotificationDto;
import com.revivo.notification.application.NotificationService;
import com.revivo.notification.application.SseHub;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.data.domain.Page;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.servlet.mvc.method.annotation.SseEmitter;

import java.util.Map;

@Tag(name = "Notifications")
@RestController
@RequestMapping("/api/notifications")
public class NotificationController {

    private final NotificationService notificationService;
    private final SseHub sseHub;

    public NotificationController(NotificationService notificationService, SseHub sseHub) {
        this.notificationService = notificationService;
        this.sseHub = sseHub;
    }

    @Operation(summary = "Mes notifications (paginées, plus récentes d'abord)")
    @GetMapping
    public Page<NotificationDto> list(@AuthenticationPrincipal Long userId,
                                      @RequestParam(defaultValue = "0") int page,
                                      @RequestParam(defaultValue = "20") int size) {
        return notificationService.list(userId, page, size);
    }

    @Operation(summary = "Nombre de notifications non lues")
    @GetMapping("/unread-count")
    public Map<String, Long> unread(@AuthenticationPrincipal Long userId) {
        return Map.of("count", notificationService.unreadCount(userId));
    }

    @Operation(summary = "Flux temps réel (Server-Sent Events) — jeton via ?access_token=")
    @GetMapping(path = "/stream", produces = MediaType.TEXT_EVENT_STREAM_VALUE)
    public SseEmitter stream(@AuthenticationPrincipal Long userId) {
        return sseHub.subscribe(userId);
    }

    @Operation(summary = "Marquer une notification comme lue")
    @PostMapping("/{id}/read")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void read(@AuthenticationPrincipal Long userId, @PathVariable Long id) {
        notificationService.markRead(userId, id);
    }

    @Operation(summary = "Tout marquer comme lu")
    @PostMapping("/read-all")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void readAll(@AuthenticationPrincipal Long userId) {
        notificationService.markAllRead(userId);
    }
}
