package com.revivo.marketplace.messaging;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.util.Optional;

public interface ConversationRepository extends JpaRepository<Conversation, Long> {

    Optional<Conversation> findByParticipantAIdAndParticipantBIdAndItemId(Long a, Long b, Long itemId);

    Optional<Conversation> findByParticipantAIdAndParticipantBIdAndItemIsNull(Long a, Long b);

    default Optional<Conversation> findThread(Long u1, Long u2, Long itemId) {
        Long a = Math.min(u1, u2);
        Long b = Math.max(u1, u2);
        return itemId == null
                ? findByParticipantAIdAndParticipantBIdAndItemIsNull(a, b)
                : findByParticipantAIdAndParticipantBIdAndItemId(a, b, itemId);
    }

    @Query(value = """
            SELECT c FROM Conversation c
            JOIN FETCH c.participantA JOIN FETCH c.participantB LEFT JOIN FETCH c.item
            WHERE (c.participantA.id = :userId OR c.participantB.id = :userId) AND c.lastMessageAt IS NOT NULL
            """,
            countQuery = """
            SELECT count(c) FROM Conversation c
            WHERE (c.participantA.id = :userId OR c.participantB.id = :userId) AND c.lastMessageAt IS NOT NULL
            """)
    Page<Conversation> findForUser(@Param("userId") Long userId, Pageable pageable);
}
