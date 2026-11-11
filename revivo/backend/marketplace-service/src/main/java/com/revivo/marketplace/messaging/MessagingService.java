package com.revivo.marketplace.messaging;

import com.revivo.events.DomainEvent;
import com.revivo.events.MessageSent;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.ForbiddenException;
import com.revivo.marketplace.common.NotFoundException;
import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.item.ItemService;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserDtos.UserSummary;
import com.revivo.marketplace.user.UserService;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Clock;
import java.time.Instant;

@Service
@Transactional(readOnly = true)
public class MessagingService {

    public record StartConversationRequest(@NotNull Long recipientId, Long itemId,
                                           @NotBlank @Size(max = 2000) String content) {
    }

    public record SendMessageRequest(@NotBlank @Size(max = 2000) String content) {
    }

    public record MessageDto(Long id, Long senderId, String content, boolean mine, Instant readAt, Instant createdAt) {
    }

    public record ConversationDto(Long id, UserSummary other, Long itemId, String itemTitle, String itemPhotoUrl,
                                  String lastMessage, Instant lastMessageAt, long unread) {
    }

    private final ConversationRepository conversations;
    private final MessageRepository messages;
    private final UserService userService;
    private final ItemService itemService;
    private final DomainEventPublisher events;
    private final Clock clock;

    public MessagingService(ConversationRepository conversations, MessageRepository messages,
                            UserService userService, ItemService itemService, DomainEventPublisher events,
                            Clock clock) {
        this.conversations = conversations;
        this.messages = messages;
        this.userService = userService;
        this.itemService = itemService;
        this.events = events;
        this.clock = clock;
    }

    /** Ouvre (ou reprend) le fil avec un membre à propos d'un article, et y poste un message. */
    @Transactional
    public ConversationDto start(Long senderId, StartConversationRequest request) {
        if (senderId.equals(request.recipientId())) {
            throw new BusinessException("MESSAGE_SELF", "Vous ne pouvez pas vous écrire à vous-même.");
        }
        User sender = userService.getUser(senderId);
        User recipient = userService.getUser(request.recipientId());
        Item item = request.itemId() == null ? null : itemService.find(request.itemId());
        Conversation conversation = conversations.findThread(senderId, recipient.getId(), request.itemId())
                .orElseGet(() -> conversations.save(new Conversation(item, sender, recipient)));
        post(conversation, sender, request.content());
        return toDto(conversation, senderId);
    }

    @Transactional
    public MessageDto send(Long senderId, Long conversationId, String content) {
        Conversation conversation = findFor(senderId, conversationId);
        return toDto(post(conversation, userService.getUser(senderId), content), senderId);
    }

    @Transactional
    public PageResponse<MessageDto> messages(Long userId, Long conversationId, int page, int size) {
        findFor(userId, conversationId);
        messages.markRead(conversationId, userId, clock.instant());
        return PageResponse.of(messages.findByConversationId(conversationId,
                        PageRequest.of(page, Math.min(size, 100), Sort.by(Sort.Direction.DESC, "createdAt"))),
                m -> toDto(m, userId));
    }

    public PageResponse<ConversationDto> inbox(Long userId, int page, int size) {
        return PageResponse.of(conversations.findForUser(userId,
                        PageRequest.of(page, Math.min(size, 50), Sort.by(Sort.Direction.DESC, "lastMessageAt"))),
                c -> toDto(c, userId));
    }

    public long unreadCount(Long userId) {
        return messages.countAllUnread(userId);
    }

    private Message post(Conversation conversation, User sender, String content) {
        String text = content.trim();
        Message message = messages.save(new Message(conversation, sender, text));
        Instant now = clock.instant();
        conversation.touch(now);
        User recipient = conversation.other(sender.getId());
        events.publish(new MessageSent(DomainEvent.newId(), now, message.getId(), conversation.getId(),
                sender.getId(), sender.getUsername(), recipient.getId(),
                text.length() > 80 ? text.substring(0, 77) + "..." : text));
        return message;
    }

    private Conversation findFor(Long userId, Long conversationId) {
        Conversation c = conversations.findById(conversationId)
                .orElseThrow(() -> new NotFoundException("Conversation", conversationId));
        if (!c.hasParticipant(userId)) {
            throw new ForbiddenException("Vous ne participez pas à cette conversation.");
        }
        return c;
    }

    private ConversationDto toDto(Conversation c, Long viewerId) {
        Item item = c.getItem();
        String last = messages.findFirstByConversationIdOrderByCreatedAtDesc(c.getId())
                .map(Message::getContent).orElse(null);
        return new ConversationDto(c.getId(), UserSummary.from(c.other(viewerId)),
                item == null ? null : item.getId(), item == null ? null : item.getTitle(),
                item == null ? null : item.firstPhotoUrl(), last, c.getLastMessageAt(),
                messages.countUnread(c.getId(), viewerId));
    }

    private static MessageDto toDto(Message m, Long viewerId) {
        return new MessageDto(m.getId(), m.getSender().getId(), m.getContent(), m.getSender().getId().equals(viewerId),
                m.getReadAt(), m.getCreatedAt());
    }
}
