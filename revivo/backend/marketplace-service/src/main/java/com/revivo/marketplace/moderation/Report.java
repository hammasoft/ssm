package com.revivo.marketplace.moderation;

import com.revivo.marketplace.common.BaseEntity;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.user.User;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

import java.time.Instant;

@Entity
@Table(name = "reports")
public class Report extends BaseEntity {

    public enum Reason { COUNTERFEIT, PROHIBITED_ITEM, INAPPROPRIATE_CONTENT, SCAM, HARASSMENT, OTHER }

    public enum Status { OPEN, RESOLVED, DISMISSED }

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "reporter_id")
    private User reporter;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "item_id")
    private Item item;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "reported_user_id")
    private User reportedUser;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private Reason reason;

    private String details;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private Status status = Status.OPEN;

    @Column(name = "resolution_note")
    private String resolutionNote;

    @Column(name = "resolved_at")
    private Instant resolvedAt;

    protected Report() {
    }

    public Report(User reporter, Item item, User reportedUser, Reason reason, String details) {
        this.reporter = reporter;
        this.item = item;
        this.reportedUser = reportedUser;
        this.reason = reason;
        this.details = details;
    }

    void close(Status status, String note, Instant now) {
        this.status = status;
        this.resolutionNote = note;
        this.resolvedAt = now;
    }

    public User getReporter() {
        return reporter;
    }

    public Item getItem() {
        return item;
    }

    public User getReportedUser() {
        return reportedUser;
    }

    public Reason getReason() {
        return reason;
    }

    public String getDetails() {
        return details;
    }

    public Status getStatus() {
        return status;
    }

    public String getResolutionNote() {
        return resolutionNote;
    }
}
