package com.revivo.marketplace.item;

import com.revivo.marketplace.catalog.Brand;
import com.revivo.marketplace.catalog.Category;
import com.revivo.marketplace.common.BaseEntity;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.user.User;
import jakarta.persistence.CascadeType;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.FetchType;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.OneToMany;
import jakarta.persistence.OrderBy;
import jakarta.persistence.Table;
import jakarta.persistence.Version;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

/**
 * Agrégat « Annonce ». Le verrou optimiste ({@link Version}) garantit qu'un article
 * ne peut pas être vendu deux fois en cas d'achats simultanés.
 */
@Entity
@Table(name = "items")
public class Item extends BaseEntity {

    public static final int MAX_PHOTOS = 10;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "seller_id")
    private User seller;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "category_id")
    private Category category;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "brand_id")
    private Brand brand;

    @Column(nullable = false, length = 100)
    private String title;

    @Column(nullable = false, length = 3000)
    private String description;

    private String size;
    private String color;

    @Enumerated(EnumType.STRING)
    @Column(name = "item_condition", nullable = false)
    private ItemCondition condition;

    @Column(nullable = false, precision = 10, scale = 2)
    private BigDecimal price;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private ItemStatus status = ItemStatus.ACTIVE;

    @Column(name = "view_count", nullable = false)
    private int viewCount;

    @Column(name = "favorite_count", nullable = false)
    private int favoriteCount;

    @Version
    private long version;

    @OneToMany(mappedBy = "item", cascade = CascadeType.ALL, orphanRemoval = true)
    @OrderBy("position ASC")
    private List<ItemPhoto> photos = new ArrayList<>();

    protected Item() {
    }

    public Item(User seller, Category category, Brand brand, String title, String description, String size,
                String color, ItemCondition condition, BigDecimal price) {
        this.seller = seller;
        update(category, brand, title, description, size, color, condition, price);
    }

    public final void update(Category category, Brand brand, String title, String description, String size,
                             String color, ItemCondition condition, BigDecimal price) {
        this.category = category;
        this.brand = brand;
        this.title = title;
        this.description = description;
        this.size = size;
        this.color = color;
        this.condition = condition;
        this.price = price;
    }

    public void replacePhotos(List<String> urls) {
        if (urls.isEmpty() || urls.size() > MAX_PHOTOS) {
            throw new BusinessException("INVALID_PHOTOS", "Entre 1 et " + MAX_PHOTOS + " photos sont requises.");
        }
        photos.clear();
        for (int i = 0; i < urls.size(); i++) {
            photos.add(new ItemPhoto(this, urls.get(i), i));
        }
    }

    public boolean isPurchasable() {
        return status == ItemStatus.ACTIVE && !seller.isHolidayMode() && !seller.isBanned();
    }

    public void markSold() {
        if (status != ItemStatus.ACTIVE) {
            throw new BusinessException("ITEM_NOT_AVAILABLE", "Cet article n'est plus disponible.");
        }
        status = ItemStatus.SOLD;
    }

    public void relist() {
        if (status == ItemStatus.SOLD) {
            status = ItemStatus.ACTIVE;
        }
    }

    public void hide() {
        if (status != ItemStatus.ACTIVE) {
            throw new BusinessException("INVALID_STATUS", "Seule une annonce active peut être masquée.");
        }
        status = ItemStatus.HIDDEN;
    }

    public void unhide() {
        if (status != ItemStatus.HIDDEN) {
            throw new BusinessException("INVALID_STATUS", "Cette annonce n'est pas masquée.");
        }
        status = ItemStatus.ACTIVE;
    }

    public void removeByModeration() {
        status = ItemStatus.REMOVED;
    }

    public boolean isOwnedBy(Long userId) {
        return seller.getId().equals(userId);
    }

    public String firstPhotoUrl() {
        return photos.isEmpty() ? null : photos.get(0).getUrl();
    }

    public User getSeller() {
        return seller;
    }

    public Category getCategory() {
        return category;
    }

    public Brand getBrand() {
        return brand;
    }

    public String getTitle() {
        return title;
    }

    public String getDescription() {
        return description;
    }

    public String getSize() {
        return size;
    }

    public String getColor() {
        return color;
    }

    public ItemCondition getCondition() {
        return condition;
    }

    public BigDecimal getPrice() {
        return price;
    }

    public ItemStatus getStatus() {
        return status;
    }

    public int getViewCount() {
        return viewCount;
    }

    public int getFavoriteCount() {
        return favoriteCount;
    }

    public List<ItemPhoto> getPhotos() {
        return photos;
    }
}
