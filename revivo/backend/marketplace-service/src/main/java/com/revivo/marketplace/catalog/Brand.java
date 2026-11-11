package com.revivo.marketplace.catalog;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

@Entity
@Table(name = "brands")
public class Brand {

    @Id
    private Long id;

    @Column(nullable = false, unique = true)
    private String name;

    protected Brand() {
    }

    public Long getId() {
        return id;
    }

    public String getName() {
        return name;
    }
}
