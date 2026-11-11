package com.revivo.marketplace.messaging;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.time.Instant;
import java.util.Optional;

public interface MessageRepository extends JpaRepository<Message, Long> {

    Page<Message> findByConversationId(Long conversationId, Pageable pageable);

    Optional<Message> findFirstByConversationIdOrderByCreatedAtDesc(Long conversationId);

    @Query("""
            SELECT count(m) FROM Message m
            WHERE m.conversation.id = :conversationId AND m.sender.id <> :userId AND m.readAt IS NULL
            """)
    long countUnread(@Param("conversationId") Long conversationId, @Param("userId") Long userId);

    @Query("""
            SELECT count(m) FROM Message m
            WHERE (m.conversation.participantA.id = :userId OR m.conversation.participantB.id = :userId)
              AND m.sender.id <> :userId AND m.readAt IS NULL
            """)
    long countAllUnread(@Param("userId") Long userId);

    @Modifying
    @Query("""
            UPDATE Message m SET m.readAt = :now
            WHERE m.conversation.id = :conversationId AND m.sender.id <> :userId AND m.readAt IS NULL
            """)
    int markRead(@Param("conversationId") Long conversationId, @Param("userId") Long userId,
                 @Param("now") Instant now);
}
