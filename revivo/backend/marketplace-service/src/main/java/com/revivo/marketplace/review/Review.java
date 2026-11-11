package com.revivo.marketplace.review;

import com.revivo.marketplace.common.BaseEntity;
import com.revivo.marketplace.order.Order;
import com.revivo.marketplace.user.User;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

@Entity
@Table(name = "reviews")
public class Review extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "order_id")
    private Order order;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "author_id")
    private User author;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "target_user_id")
    private User target;

    @Column(nullable = false)
    private short rating;

    private String comment;

    protected Review() {
    }

    public Review(Order order, User author, User target, int rating, String comment) {
        this.order = order;
        this.author = author;
        this.target = target;
        this.rating = (short) rating;
        this.comment = comment;
    }

    public Order getOrder() {
        return order;
    }

    public User getAuthor() {
        return author;
    }

    public User getTarget() {
        return target;
    }

    public int getRating() {
        return rating;
    }

    public String getComment() {
        return comment;
    }
}
