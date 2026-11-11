package com.revivo.marketplace.item;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.item.ItemDtos.ItemCard;
import com.revivo.marketplace.item.ItemDtos.ItemDetail;
import com.revivo.marketplace.item.ItemDtos.ItemRequest;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.security.CurrentUser;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import jakarta.validation.constraints.Max;
import jakarta.validation.constraints.Min;
import org.springframework.http.HttpStatus;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@Tag(name = "Articles")
@Validated
@RestController
@RequestMapping("/api")
public class ItemController {

    private final ItemService itemService;

    public ItemController(ItemService itemService) {
        this.itemService = itemService;
    }

    @Operation(summary = "Rechercher des articles (texte, catégorie, marque, taille, état, prix, tri)")
    @GetMapping("/items")
    public PageResponse<ItemCard> search(@ModelAttribute ItemSearchCriteria criteria,
                                         @RequestParam(defaultValue = "0") @Min(0) int page,
                                         @RequestParam(defaultValue = "24") @Min(1) @Max(60) int size,
                                         @CurrentUser AuthenticatedUser viewer) {
        return itemService.search(criteria, page, size, idOf(viewer));
    }

    @Operation(summary = "Détail d'un article (incrémente le compteur de vues)")
    @GetMapping("/items/{id}")
    public ItemDetail get(@PathVariable Long id, @CurrentUser AuthenticatedUser viewer) {
        ItemDetail detail = itemService.get(id, idOf(viewer));
        itemService.recordView(id, idOf(viewer));
        return detail;
    }

    @Operation(summary = "Articles similaires (même catégorie)")
    @GetMapping("/items/{id}/similar")
    public List<ItemCard> similar(@PathVariable Long id, @CurrentUser AuthenticatedUser viewer) {
        return itemService.similar(id, idOf(viewer));
    }

    @Operation(summary = "Fil d'actualité : nouveautés des membres que je suis")
    @GetMapping("/items/feed")
    public PageResponse<ItemCard> feed(@CurrentUser AuthenticatedUser user,
                                       @RequestParam(defaultValue = "0") int page,
                                       @RequestParam(defaultValue = "24") @Max(60) int size) {
        return itemService.feed(user.id(), page, size);
    }

    @Operation(summary = "Dressing d'un membre")
    @GetMapping("/users/{userId}/items")
    public PageResponse<ItemCard> bySeller(@PathVariable Long userId, @CurrentUser AuthenticatedUser viewer,
                                           @RequestParam(defaultValue = "0") int page,
                                           @RequestParam(defaultValue = "24") @Max(60) int size) {
        return itemService.bySeller(userId, idOf(viewer), page, size);
    }

    @Operation(summary = "Mettre un article en vente")
    @PostMapping("/items")
    @ResponseStatus(HttpStatus.CREATED)
    public ItemDetail create(@CurrentUser AuthenticatedUser user, @Valid @RequestBody ItemRequest request) {
        return itemService.create(user.id(), request);
    }

    @Operation(summary = "Modifier mon annonce (une baisse de prix notifie les membres intéressés)")
    @PutMapping("/items/{id}")
    public ItemDetail update(@CurrentUser AuthenticatedUser user, @PathVariable Long id,
                             @Valid @RequestBody ItemRequest request) {
        return itemService.update(user.id(), id, request);
    }

    @Operation(summary = "Masquer / réafficher mon annonce")
    @PostMapping("/items/{id}/visibility")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void visibility(@CurrentUser AuthenticatedUser user, @PathVariable Long id,
                           @RequestParam boolean hidden) {
        itemService.hide(user.id(), id, hidden);
    }

    @Operation(summary = "Supprimer mon annonce")
    @DeleteMapping("/items/{id}")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void delete(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        itemService.delete(user.id(), id);
    }

    private static Long idOf(AuthenticatedUser user) {
        return user == null ? null : user.id();
    }
}
