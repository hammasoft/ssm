package com.revivo.marketplace.favorite;

import com.revivo.marketplace.common.BaseEntity;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.user.User;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

@Entity
@Table(name = "favorites")
public class Favorite extends BaseEntity {

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "user_id")
    private User user;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "item_id")
    private Item item;

    protected Favorite() {
    }

    public Favorite(User user, Item item) {
        this.user = user;
        this.item = item;
    }

    public Item getItem() {
        return item;
    }
}
