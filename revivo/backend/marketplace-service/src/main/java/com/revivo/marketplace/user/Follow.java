package com.revivo.marketplace.user;

import com.revivo.marketplace.common.BaseEntity;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

@Entity
@Table(name = "follows")
public class Follow extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "follower_id")
    private User follower;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "followed_id")
    private User followed;

    protected Follow() {
    }

    public Follow(User follower, User followed) {
        this.follower = follower;
        this.followed = followed;
    }

    public User getFollower() {
        return follower;
    }

    public User getFollowed() {
        return followed;
    }
}
