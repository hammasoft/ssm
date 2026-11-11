package com.revivo.marketplace.favorite;

import com.revivo.events.DomainEvent;
import com.revivo.events.ItemFavorited;
import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.item.ItemDtos.ItemCard;
import com.revivo.marketplace.item.ItemRepository;
import com.revivo.marketplace.item.ItemService;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserService;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Clock;

@Service
@Transactional(readOnly = true)
public class FavoriteService {

    private final FavoriteRepository favorites;
    private final ItemRepository items;
    private final ItemService itemService;
    private final UserService userService;
    private final DomainEventPublisher events;
    private final Clock clock;

    public FavoriteService(FavoriteRepository favorites, ItemRepository items, ItemService itemService,
                           UserService userService, DomainEventPublisher events, Clock clock) {
        this.favorites = favorites;
        this.items = items;
        this.itemService = itemService;
        this.userService = userService;
        this.events = events;
        this.clock = clock;
    }

    @Transactional
    public void add(Long userId, Long itemId) {
        if (favorites.existsByUserIdAndItemId(userId, itemId)) {
            return; // idempotent
        }
        Item item = itemService.find(itemId);
        User user = userService.getUser(userId);
        favorites.save(new Favorite(user, item));
        items.adjustFavoriteCount(itemId, 1);
        if (!item.isOwnedBy(userId)) {
            events.publish(new ItemFavorited(DomainEvent.newId(), clock.instant(), itemId, item.getSeller().getId(),
                    userId, user.getUsername(), item.getTitle()));
        }
    }

    @Transactional
    public void remove(Long userId, Long itemId) {
        favorites.findByUserIdAndItemId(userId, itemId).ifPresent(f -> {
            favorites.delete(f);
            items.adjustFavoriteCount(itemId, -1);
        });
    }

    public PageResponse<ItemCard> list(Long userId, int page, int size) {
        return PageResponse.of(favorites.findByUser(userId,
                        PageRequest.of(page, size, Sort.by(Sort.Direction.DESC, "createdAt"))),
                f -> itemService.toCard(f.getItem(), true));
    }
}
