package com.revivo.marketplace.item;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

@Entity
@Table(name = "item_photos")
public class ItemPhoto {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "item_id")
    private Item item;

    @Column(nullable = false)
    private String url;

    @Column(nullable = false)
    private int position;

    protected ItemPhoto() {
    }

    ItemPhoto(Item item, String url, int position) {
        this.item = item;
        this.url = url;
        this.position = position;
    }

    public String getUrl() {
        return url;
    }

    public int getPosition() {
        return position;
    }
}
