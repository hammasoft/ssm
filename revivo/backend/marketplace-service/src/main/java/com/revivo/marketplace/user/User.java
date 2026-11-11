package com.revivo.marketplace.user;

import com.revivo.marketplace.common.BaseEntity;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.Table;

import java.math.BigDecimal;
import java.math.RoundingMode;

@Entity
@Table(name = "users")
public class User extends BaseEntity {

    @Column(nullable = false, unique = true, length = 30)
    private String username;

    @Column(nullable = false, unique = true)
    private String email;

    @Column(name = "password_hash", nullable = false)
    private String passwordHash;

    private String bio;
    private String city;
    private String country;

    @Column(name = "avatar_url")
    private String avatarUrl;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private Role role = Role.USER;

    private boolean banned;

    /** Mode vacances : les articles sont masqués des recherches et non achetables. */
    @Column(name = "holiday_mode")
    private boolean holidayMode;

    @Column(name = "rating_avg", nullable = false)
    private BigDecimal ratingAvg = BigDecimal.ZERO;

    @Column(name = "rating_count", nullable = false)
    private int ratingCount;

    protected User() {
    }

    public User(String username, String email, String passwordHash) {
        this.username = username;
        this.email = email;
        this.passwordHash = passwordHash;
    }

    /** Moyenne glissante, recalculée de façon incrémentale à chaque nouvel avis. */
    public void addRating(int rating) {
        BigDecimal total = ratingAvg.multiply(BigDecimal.valueOf(ratingCount)).add(BigDecimal.valueOf(rating));
        ratingCount++;
        ratingAvg = total.divide(BigDecimal.valueOf(ratingCount), 2, RoundingMode.HALF_UP);
    }

    public void updateProfile(String bio, String city, String country, String avatarUrl) {
        this.bio = bio;
        this.city = city;
        this.country = country;
        this.avatarUrl = avatarUrl;
    }

    public void promoteToAdmin() {
        this.role = Role.ADMIN;
    }

    public String getUsername() {
        return username;
    }

    public String getEmail() {
        return email;
    }

    public String getPasswordHash() {
        return passwordHash;
    }

    public void changePasswordHash(String passwordHash) {
        this.passwordHash = passwordHash;
    }

    public String getBio() {
        return bio;
    }

    public String getCity() {
        return city;
    }

    public String getCountry() {
        return country;
    }

    public String getAvatarUrl() {
        return avatarUrl;
    }

    public Role getRole() {
        return role;
    }

    public boolean isBanned() {
        return banned;
    }

    public void setBanned(boolean banned) {
        this.banned = banned;
    }

    public boolean isHolidayMode() {
        return holidayMode;
    }

    public void setHolidayMode(boolean holidayMode) {
        this.holidayMode = holidayMode;
    }

    public BigDecimal getRatingAvg() {
        return ratingAvg;
    }

    public int getRatingCount() {
        return ratingCount;
    }
}
