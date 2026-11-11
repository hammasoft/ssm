package com.revivo.marketplace.order;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.order.OrderDtos.CheckoutQuote;
import com.revivo.marketplace.order.OrderDtos.CheckoutRequest;
import com.revivo.marketplace.order.OrderDtos.OrderDto;
import com.revivo.marketplace.order.OrderDtos.ReasonRequest;
import com.revivo.marketplace.order.OrderDtos.ShipRequest;
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

@Tag(name = "Commandes")
@RestController
@RequestMapping("/api")
public class OrderController {

    private final OrderService orderService;

    public OrderController(OrderService orderService) {
        this.orderService = orderService;
    }

    @Operation(summary = "Devis avant paiement (prix, protection acheteur, livraison)")
    @GetMapping("/items/{itemId}/checkout-quote")
    public CheckoutQuote quote(@CurrentUser AuthenticatedUser user, @PathVariable Long itemId,
                               @RequestParam(defaultValue = "RELAY_POINT") ShippingOption shippingOption) {
        return orderService.quote(user.id(), itemId, shippingOption);
    }

    @Operation(summary = "Acheter un article (paiement + séquestre)")
    @PostMapping("/items/{itemId}/checkout")
    @ResponseStatus(HttpStatus.CREATED)
    public OrderDto checkout(@CurrentUser AuthenticatedUser user, @PathVariable Long itemId,
                             @Valid @RequestBody CheckoutRequest request) {
        return orderService.checkout(user.id(), itemId, request);
    }

    @Operation(summary = "Mes achats")
    @GetMapping("/orders/purchases")
    public PageResponse<OrderDto> purchases(@CurrentUser AuthenticatedUser user,
                                            @RequestParam(defaultValue = "0") int page,
                                            @RequestParam(defaultValue = "20") int size) {
        return orderService.purchases(user.id(), page, size);
    }

    @Operation(summary = "Mes ventes")
    @GetMapping("/orders/sales")
    public PageResponse<OrderDto> sales(@CurrentUser AuthenticatedUser user,
                                        @RequestParam(defaultValue = "0") int page,
                                        @RequestParam(defaultValue = "20") int size) {
        return orderService.sales(user.id(), page, size);
    }

    @Operation(summary = "Détail d'une commande")
    @GetMapping("/orders/{id}")
    public OrderDto get(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        return orderService.get(user, id);
    }

    @Operation(summary = "Déclarer l'expédition (vendeur)")
    @PostMapping("/orders/{id}/ship")
    public OrderDto ship(@CurrentUser AuthenticatedUser user, @PathVariable Long id,
                         @Valid @RequestBody ShipRequest request) {
        return orderService.ship(user.id(), id, request.trackingNumber());
    }

    @Operation(summary = "Simuler la livraison par le transporteur (vendeur / admin)")
    @PostMapping("/orders/{id}/delivered")
    public OrderDto delivered(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        return orderService.markDelivered(user, id);
    }

    @Operation(summary = "Confirmer la réception : tout est OK (acheteur) — libère les fonds")
    @PostMapping("/orders/{id}/confirm")
    public OrderDto confirm(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        return orderService.confirmReception(user.id(), id);
    }

    @Operation(summary = "Annuler une commande non expédiée (remboursement)")
    @PostMapping("/orders/{id}/cancel")
    public OrderDto cancel(@CurrentUser AuthenticatedUser user, @PathVariable Long id,
                           @Valid @RequestBody ReasonRequest request) {
        return orderService.cancel(user.id(), id, request.reason());
    }

    @Operation(summary = "Ouvrir un litige (acheteur)")
    @PostMapping("/orders/{id}/dispute")
    public OrderDto dispute(@CurrentUser AuthenticatedUser user, @PathVariable Long id,
                            @Valid @RequestBody ReasonRequest request) {
        return orderService.openDispute(user.id(), id, request.reason());
    }
}
