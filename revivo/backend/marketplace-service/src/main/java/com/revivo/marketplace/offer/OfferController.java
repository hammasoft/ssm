package com.revivo.marketplace.offer;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.offer.OfferDtos.MakeOfferRequest;
import com.revivo.marketplace.offer.OfferDtos.OfferDto;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.security.CurrentUser;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.bind.annotation.RestController;

@Tag(name = "Offres (négociation)")
@RestController
@RequestMapping("/api")
public class OfferController {

    private final OfferService offerService;

    public OfferController(OfferService offerService) {
        this.offerService = offerService;
    }

    @Operation(summary = "Faire une offre sur un article")
    @PostMapping("/items/{itemId}/offers")
    @ResponseStatus(HttpStatus.CREATED)
    public OfferDto make(@CurrentUser AuthenticatedUser user, @PathVariable Long itemId,
                         @Valid @RequestBody MakeOfferRequest request) {
        return offerService.make(user.id(), itemId, request.amount());
    }

    @Operation(summary = "Offres que j'ai envoyées")
    @GetMapping("/offers/sent")
    public PageResponse<OfferDto> sent(@CurrentUser AuthenticatedUser user,
                                       @RequestParam(defaultValue = "0") int page,
                                       @RequestParam(defaultValue = "20") int size) {
        return offerService.sent(user.id(), page, Math.min(size, 100));
    }

    @Operation(summary = "Offres reçues sur mes articles")
    @GetMapping("/offers/received")
    public PageResponse<OfferDto> received(@CurrentUser AuthenticatedUser user,
                                           @RequestParam(defaultValue = "0") int page,
                                           @RequestParam(defaultValue = "20") int size) {
        return offerService.received(user.id(), page, Math.min(size, 100));
    }

    @Operation(summary = "Accepter une offre (vendeur)")
    @PostMapping("/offers/{id}/accept")
    public OfferDto accept(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        return offerService.accept(user.id(), id);
    }

    @Operation(summary = "Refuser une offre (vendeur)")
    @PostMapping("/offers/{id}/decline")
    public OfferDto decline(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        return offerService.decline(user.id(), id);
    }

    @Operation(summary = "Annuler mon offre (acheteur)")
    @PostMapping("/offers/{id}/cancel")
    public OfferDto cancel(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        return offerService.cancel(user.id(), id);
    }
}
