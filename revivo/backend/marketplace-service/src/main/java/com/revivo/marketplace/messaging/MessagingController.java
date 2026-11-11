package com.revivo.marketplace.messaging;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.messaging.MessagingService.ConversationDto;
import com.revivo.marketplace.messaging.MessagingService.MessageDto;
import com.revivo.marketplace.messaging.MessagingService.SendMessageRequest;
import com.revivo.marketplace.messaging.MessagingService.StartConversationRequest;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.security.CurrentUser;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.bind.annotation.RestController;

import java.util.Map;

@Tag(name = "Messagerie")
@RestController
@RequestMapping("/api/conversations")
public class MessagingController {

    private final MessagingService messagingService;

    public MessagingController(MessagingService messagingService) {
        this.messagingService = messagingService;
    }

    @Operation(summary = "Ma boîte de réception")
    @GetMapping
    public PageResponse<ConversationDto> inbox(@CurrentUser AuthenticatedUser user,
                                               @RequestParam(defaultValue = "0") int page,
                                               @RequestParam(defaultValue = "20") int size) {
        return messagingService.inbox(user.id(), page, size);
    }

    @Operation(summary = "Nombre de messages non lus")
    @GetMapping("/unread-count")
    public Map<String, Long> unread(@CurrentUser AuthenticatedUser user) {
        return Map.of("count", messagingService.unreadCount(user.id()));
    }

    @Operation(summary = "Contacter un membre (à propos d'un article)")
    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public ConversationDto start(@CurrentUser AuthenticatedUser user,
                                 @Valid @RequestBody StartConversationRequest request) {
        return messagingService.start(user.id(), request);
    }

    @Operation(summary = "Messages d'une conversation (les marque comme lus)")
    @GetMapping("/{id}/messages")
    public PageResponse<MessageDto> messages(@CurrentUser AuthenticatedUser user, @PathVariable Long id,
                                             @RequestParam(defaultValue = "0") int page,
                                             @RequestParam(defaultValue = "50") int size) {
        return messagingService.messages(user.id(), id, page, size);
    }

    @Operation(summary = "Envoyer un message")
    @PostMapping("/{id}/messages")
    @ResponseStatus(HttpStatus.CREATED)
    public MessageDto send(@CurrentUser AuthenticatedUser user, @PathVariable Long id,
                           @Valid @RequestBody SendMessageRequest request) {
        return messagingService.send(user.id(), id, request.content());
    }
}
