package com.revivo.marketplace.item;

import com.revivo.marketplace.user.User;
import jakarta.persistence.criteria.Join;
import org.springframework.data.jpa.domain.Specification;

import java.math.BigDecimal;
import java.util.Collection;

/** Briques de requêtes composables (Criteria API) pour la recherche d'articles. */
final class ItemSpecifications {

    private ItemSpecifications() {
    }

    static Specification<Item> visibleInSearch() {
        return (root, query, cb) -> {
            Join<Item, User> seller = root.join("seller");
            return cb.and(
                    cb.equal(root.get("status"), ItemStatus.ACTIVE),
                    cb.isFalse(seller.get("holidayMode")),
                    cb.isFalse(seller.get("banned")));
        };
    }

    static Specification<Item> fullText(String q) {
        if (q == null || q.isBlank()) return null;
        String pattern = "%" + q.trim().toLowerCase().replace("%", "\\%").replace("_", "\\_") + "%";
        return (root, query, cb) -> cb.or(
                cb.like(cb.lower(root.get("title")), pattern, '\\'),
                cb.like(cb.lower(root.get("description")), pattern, '\\'),
                cb.like(cb.lower(root.join("brand", jakarta.persistence.criteria.JoinType.LEFT).get("name")),
                        pattern, '\\'));
    }

    static Specification<Item> inCategories(Collection<Long> ids) {
        if (ids == null || ids.isEmpty()) return null;
        return (root, query, cb) -> root.get("category").get("id").in(ids);
    }

    static Specification<Item> brandIn(Collection<Long> ids) {
        if (ids == null || ids.isEmpty()) return null;
        return (root, query, cb) -> root.get("brand").get("id").in(ids);
    }

    static <T> Specification<Item> attributeIn(String attribute, Collection<T> values) {
        if (values == null || values.isEmpty()) return null;
        return (root, query, cb) -> root.get(attribute).in(values);
    }

    static Specification<Item> priceBetween(BigDecimal min, BigDecimal max) {
        if (min == null && max == null) return null;
        return (root, query, cb) -> {
            if (min != null && max != null) return cb.between(root.get("price"), min, max);
            if (min != null) return cb.greaterThanOrEqualTo(root.get("price"), min);
            return cb.lessThanOrEqualTo(root.get("price"), max);
        };
    }
}
