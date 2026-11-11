package com.revivo.marketplace.item;

import com.revivo.events.DomainEvent;
import com.revivo.events.ItemPriceDropped;
import com.revivo.events.ItemPublished;
import com.revivo.marketplace.catalog.Brand;
import com.revivo.marketplace.catalog.BrandRepository;
import com.revivo.marketplace.catalog.CatalogService;
import com.revivo.marketplace.catalog.Category;
import com.revivo.marketplace.catalog.CategoryRepository;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.ForbiddenException;
import com.revivo.marketplace.common.NotFoundException;
import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.favorite.FavoriteRepository;
import com.revivo.marketplace.item.ItemDtos.ItemCard;
import com.revivo.marketplace.item.ItemDtos.ItemDetail;
import com.revivo.marketplace.item.ItemDtos.ItemRequest;
import com.revivo.marketplace.order.FeeCalculator;
import com.revivo.marketplace.order.OrderRepository;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.storage.StorageService;
import com.revivo.marketplace.user.FollowRepository;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserDtos.UserSummary;
import com.revivo.marketplace.user.UserService;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Clock;
import java.util.EnumSet;
import java.util.List;
import java.util.Objects;
import java.util.Set;
import java.util.stream.Stream;

@Service
@Transactional(readOnly = true)
public class ItemService {

    private final ItemRepository items;
    private final CategoryRepository categories;
    private final BrandRepository brands;
    private final CatalogService catalogService;
    private final FavoriteRepository favorites;
    private final FollowRepository follows;
    private final OrderRepository orders;
    private final UserService userService;
    private final FeeCalculator feeCalculator;
    private final DomainEventPublisher events;
    private final Clock clock;

    public ItemService(ItemRepository items, CategoryRepository categories, BrandRepository brands,
                       CatalogService catalogService, FavoriteRepository favorites, FollowRepository follows,
                       OrderRepository orders, UserService userService, FeeCalculator feeCalculator,
                       DomainEventPublisher events, Clock clock) {
        this.items = items;
        this.categories = categories;
        this.brands = brands;
        this.catalogService = catalogService;
        this.favorites = favorites;
        this.follows = follows;
        this.orders = orders;
        this.userService = userService;
        this.feeCalculator = feeCalculator;
        this.events = events;
        this.clock = clock;
    }

    // ------------------------------------------------------------------ lecture

    public PageResponse<ItemCard> search(ItemSearchCriteria c, int page, int size, Long viewerId) {
        List<Specification<Item>> specs = Stream.of(
                        ItemSpecifications.visibleInSearch(),
                        ItemSpecifications.fullText(c.q()),
                        ItemSpecifications.inCategories(c.categoryId() == null ? null
                                : catalogService.withDescendants(c.categoryId())),
                        ItemSpecifications.brandIn(c.brandIds()),
                        ItemSpecifications.attributeIn("size", c.sizes()),
                        ItemSpecifications.attributeIn("color", c.colors()),
                        ItemSpecifications.attributeIn("condition", c.conditions()),
                        ItemSpecifications.priceBetween(c.minPrice(), c.maxPrice()))
                .filter(Objects::nonNull)
                .toList();
        Pageable pageable = PageRequest.of(page, Math.min(size, 60), sortOf(c.sort()));
        return toCards(items.findAll(Specification.allOf(specs), pageable), viewerId);
    }

    public ItemDetail get(Long id, Long viewerId) {
        Item item = find(id);
        boolean mine = item.isOwnedBy(viewerId == null ? -1L : viewerId);
        if (item.getStatus() == ItemStatus.REMOVED || (item.getStatus() == ItemStatus.HIDDEN && !mine)) {
            throw new NotFoundException("Article", id);
        }
        boolean favorited = viewerId != null && favorites.existsByUserIdAndItemId(viewerId, id);
        return toDetail(item, favorited, mine);
    }

    @Transactional
    public void recordView(Long id, Long viewerId) {
        Item item = find(id);
        if (!item.isOwnedBy(viewerId == null ? -1L : viewerId)) {
            items.incrementViews(id);
        }
    }

    public List<ItemCard> similar(Long id, Long viewerId) {
        Item item = find(id);
        List<Item> similar = items.findTop12ByCategoryIdAndStatusAndIdNotOrderByCreatedAtDesc(
                item.getCategory().getId(), ItemStatus.ACTIVE, id);
        Set<Long> favs = favoritedAmong(similar, viewerId);
        return similar.stream().map(i -> toCard(i, favs.contains(i.getId()))).toList();
    }

    /** Fil d'actualité : nouveautés des membres suivis. */
    public PageResponse<ItemCard> feed(Long userId, int page, int size) {
        List<Long> followed = follows.findFollowedIds(userId);
        if (followed.isEmpty()) {
            return new PageResponse<>(List.of(), page, size, 0, 0, true);
        }
        return toCards(items.findBySellerIdInAndStatus(followed, ItemStatus.ACTIVE,
                PageRequest.of(page, size, Sort.by(Sort.Direction.DESC, "createdAt"))), userId);
    }

    /** Dressing d'un membre : le propriétaire voit aussi ses annonces masquées. */
    public PageResponse<ItemCard> bySeller(Long sellerId, Long viewerId, int page, int size) {
        Set<ItemStatus> statuses = sellerId.equals(viewerId)
                ? EnumSet.of(ItemStatus.ACTIVE, ItemStatus.HIDDEN, ItemStatus.SOLD)
                : EnumSet.of(ItemStatus.ACTIVE, ItemStatus.SOLD);
        return toCards(items.findBySellerIdAndStatusIn(sellerId, statuses,
                PageRequest.of(page, size, Sort.by(Sort.Direction.DESC, "createdAt"))), viewerId);
    }

    // ------------------------------------------------------------------ écriture

    @Transactional
    public ItemDetail create(Long sellerId, ItemRequest r) {
        User seller = userService.getUser(sellerId);
        validatePhotos(r.photoUrls());
        Item item = new Item(seller, category(r.categoryId()), brand(r.brandId()), r.title().trim(),
                r.description().trim(), r.size(), r.color(), r.condition(), r.price());
        item.replacePhotos(r.photoUrls());
        items.save(item);
        events.publish(new ItemPublished(DomainEvent.newId(), clock.instant(), item.getId(), sellerId,
                seller.getUsername(), item.getTitle(), item.getPrice(), follows.findFollowerIds(sellerId)));
        return toDetail(item, false, true);
    }

    @Transactional
    public ItemDetail update(Long sellerId, Long id, ItemRequest r) {
        Item item = findOwned(sellerId, id);
        if (item.getStatus() == ItemStatus.SOLD || item.getStatus() == ItemStatus.REMOVED) {
            throw new BusinessException("ITEM_LOCKED", "Un article vendu ou retiré ne peut plus être modifié.");
        }
        validatePhotos(r.photoUrls());
        var oldPrice = item.getPrice();
        item.update(category(r.categoryId()), brand(r.brandId()), r.title().trim(), r.description().trim(),
                r.size(), r.color(), r.condition(), r.price());
        item.replacePhotos(r.photoUrls());
        if (r.price().compareTo(oldPrice) < 0) {
            events.publish(new ItemPriceDropped(DomainEvent.newId(), clock.instant(), id, sellerId, item.getTitle(),
                    oldPrice, r.price(), favorites.findUserIdsByItemId(id)));
        }
        return toDetail(item, false, true);
    }

    @Transactional
    public void hide(Long sellerId, Long id, boolean hidden) {
        Item item = findOwned(sellerId, id);
        if (hidden) item.hide();
        else item.unhide();
    }

    @Transactional
    public void delete(Long sellerId, Long id) {
        Item item = findOwned(sellerId, id);
        if (orders.existsByItemId(id)) {
            throw new BusinessException("ITEM_HAS_ORDERS", "Un article lié à une commande ne peut pas être supprimé.");
        }
        items.delete(item);
    }

    // ------------------------------------------------------------------ helpers

    public Item find(Long id) {
        return items.findById(id).orElseThrow(() -> new NotFoundException("Article", id));
    }

    private Item findOwned(Long sellerId, Long id) {
        Item item = find(id);
        if (!item.isOwnedBy(sellerId)) {
            throw new ForbiddenException("Cet article ne vous appartient pas.");
        }
        return item;
    }

    private Category category(Long id) {
        return categories.findById(id).orElseThrow(() -> new NotFoundException("Catégorie", id));
    }

    private Brand brand(Long id) {
        return id == null ? null : brands.findById(id).orElseThrow(() -> new NotFoundException("Marque", id));
    }

    private static void validatePhotos(List<String> urls) {
        if (urls.stream().anyMatch(u -> !StorageService.isManagedUrl(u))) {
            throw new BusinessException("INVALID_PHOTOS", "URL de photo non autorisée.");
        }
    }

    private static Sort sortOf(String sort) {
        return switch (sort == null ? "newest" : sort) {
            case "price_asc" -> Sort.by("price").ascending().and(Sort.by("id"));
            case "price_desc" -> Sort.by("price").descending().and(Sort.by("id"));
            case "popular" -> Sort.by("favoriteCount").descending().and(Sort.by("createdAt").descending());
            default -> Sort.by("createdAt").descending().and(Sort.by("id").descending());
        };
    }

    private PageResponse<ItemCard> toCards(Page<Item> page, Long viewerId) {
        Set<Long> favs = favoritedAmong(page.getContent(), viewerId);
        return PageResponse.of(page, i -> toCard(i, favs.contains(i.getId())));
    }

    private Set<Long> favoritedAmong(List<Item> list, Long viewerId) {
        if (viewerId == null || list.isEmpty()) return Set.of();
        return Set.copyOf(favorites.findFavoritedItemIds(viewerId, list.stream().map(Item::getId).toList()));
    }

    public ItemCard toCard(Item i, boolean favorited) {
        return new ItemCard(i.getId(), i.getTitle(), i.getPrice(), feeCalculator.priceWithProtection(i.getPrice()),
                i.getSize(), i.getBrand() == null ? null : i.getBrand().getName(), i.getCondition().name(),
                i.firstPhotoUrl(), i.getStatus().name(), i.getFavoriteCount(), favorited,
                UserSummary.from(i.getSeller()));
    }

    private ItemDetail toDetail(Item i, boolean favorited, boolean mine) {
        return new ItemDetail(i.getId(), i.getTitle(), i.getDescription(), i.getPrice(),
                feeCalculator.priceWithProtection(i.getPrice()), i.getSize(), i.getColor(), i.getCondition().name(),
                i.getCondition().label(), i.getCategory().getId(), i.getCategory().getName(),
                i.getBrand() == null ? null : i.getBrand().getId(),
                i.getBrand() == null ? null : i.getBrand().getName(),
                i.getPhotos().stream().map(ItemPhoto::getUrl).toList(), i.getStatus().name(), i.getViewCount(),
                i.getFavoriteCount(), favorited, mine, i.isPurchasable() && !mine, UserSummary.from(i.getSeller()),
                i.getCreatedAt(), i.getUpdatedAt());
    }
}
