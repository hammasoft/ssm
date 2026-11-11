package com.revivo.marketplace;

import com.revivo.marketplace.catalog.Category;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.item.ItemCondition;
import com.revivo.marketplace.order.Order;
import com.revivo.marketplace.order.ShippingOption;
import com.revivo.marketplace.user.User;
import org.springframework.test.util.ReflectionTestUtils;

import java.lang.reflect.Constructor;
import java.math.BigDecimal;
import java.time.Instant;
import java.util.List;

/** Fabrique d'objets du domaine pour les tests unitaires (ids positionnés par réflexion). */
public final class TestFixtures {

    private TestFixtures() {
    }

    public static User user(long id, String username) {
        User u = new User(username, username + "@test.dev", "hash");
        ReflectionTestUtils.setField(u, "id", id);
        return u;
    }

    public static Category category(long id) {
        try {
            Constructor<Category> ctor = Category.class.getDeclaredConstructor();
            ctor.setAccessible(true);
            Category c = ctor.newInstance();
            ReflectionTestUtils.setField(c, "id", id);
            ReflectionTestUtils.setField(c, "name", "Cat " + id);
            return c;
        } catch (ReflectiveOperationException e) {
            throw new IllegalStateException(e);
        }
    }

    public static Item item(long id, User seller, String price) {
        Item item = new Item(seller, category(10), null, "Veste en jean", "Une belle veste en jean", "M", "Bleu",
                ItemCondition.VERY_GOOD, new BigDecimal(price));
        item.replacePhotos(List.of("/uploads/a.jpg"));
        ReflectionTestUtils.setField(item, "id", id);
        return item;
    }

    public static Order order(long id, Item item, User buyer, ShippingOption option) {
        Order order = new Order(item, buyer, null, item.getPrice(), new BigDecimal("3.49"), new BigDecimal("1.30"),
                option, "1 rue de la Paix, Paris", "pay_123", Instant.parse("2026-01-01T10:00:00Z"));
        ReflectionTestUtils.setField(order, "id", id);
        return order;
    }
}
