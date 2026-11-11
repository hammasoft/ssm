package com.revivo.marketplace.favorite;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.item.ItemDtos.ItemCard;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.security.CurrentUser;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.bind.annotation.RestController;

@Tag(name = "Favoris")
@RestController
@RequestMapping("/api/favorites")
public class FavoriteController {

    private final FavoriteService favoriteService;

    public FavoriteController(FavoriteService favoriteService) {
        this.favoriteService = favoriteService;
    }

    @Operation(summary = "Mes favoris")
    @GetMapping
    public PageResponse<ItemCard> list(@CurrentUser AuthenticatedUser user,
                                       @RequestParam(defaultValue = "0") int page,
                                       @RequestParam(defaultValue = "24") int size) {
        return favoriteService.list(user.id(), page, Math.min(size, 60));
    }

    @Operation(summary = "Ajouter un favori (idempotent)")
    @PutMapping("/{itemId}")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void add(@CurrentUser AuthenticatedUser user, @PathVariable Long itemId) {
        favoriteService.add(user.id(), itemId);
    }

    @Operation(summary = "Retirer un favori")
    @DeleteMapping("/{itemId}")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void remove(@CurrentUser AuthenticatedUser user, @PathVariable Long itemId) {
        favoriteService.remove(user.id(), itemId);
    }
}
