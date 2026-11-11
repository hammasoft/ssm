package com.revivo.marketplace.messaging;

import com.revivo.marketplace.common.BaseEntity;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.user.User;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

import java.time.Instant;

/**
 * Fil de discussion entre deux membres, optionnellement rattaché à un article.
 * Les participants sont normalisés (a &lt; b) pour garantir l'unicité du fil.
 */
@Entity
@Table(name = "conversations")
public class Conversation extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "item_id")
    private Item item;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "participant_a")
    private User participantA;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "participant_b")
    private User participantB;

    @Column(name = "last_message_at")
    private Instant lastMessageAt;

    protected Conversation() {
    }

    public Conversation(Item item, User u1, User u2) {
        this.item = item;
        boolean ordered = u1.getId() < u2.getId();
        this.participantA = ordered ? u1 : u2;
        this.participantB = ordered ? u2 : u1;
    }

    public boolean hasParticipant(Long userId) {
        return participantA.getId().equals(userId) || participantB.getId().equals(userId);
    }

    public User other(Long userId) {
        return participantA.getId().equals(userId) ? participantB : participantA;
    }

    void touch(Instant when) {
        this.lastMessageAt = when;
    }

    public Item getItem() {
        return item;
    }

    public Instant getLastMessageAt() {
        return lastMessageAt;
    }
}
