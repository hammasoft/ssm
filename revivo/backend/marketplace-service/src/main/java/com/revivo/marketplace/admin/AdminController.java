package com.revivo.marketplace.admin;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.item.ItemRepository;
import com.revivo.marketplace.item.ItemStatus;
import com.revivo.marketplace.moderation.ModerationService;
import com.revivo.marketplace.moderation.ModerationService.ReportDto;
import com.revivo.marketplace.moderation.ModerationService.ResolveRequest;
import com.revivo.marketplace.moderation.Report;
import com.revivo.marketplace.moderation.ReportRepository;
import com.revivo.marketplace.order.OrderDtos.OrderDto;
import com.revivo.marketplace.order.OrderRepository;
import com.revivo.marketplace.order.OrderService;
import com.revivo.marketplace.order.OrderStatus;
import com.revivo.marketplace.outbox.OutboxEventRepository;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.security.CurrentUser;
import com.revivo.marketplace.user.UserRepository;
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

import java.math.BigDecimal;
import java.util.EnumSet;

/** Back-office de modération (rôle ADMIN requis, cf. SecurityConfig). */
@Tag(name = "Administration")
@RestController
@RequestMapping("/api/admin")
public class AdminController {

    public record Stats(long users, long activeItems, long soldItems, long ordersInProgress, long openDisputes,
                        long openReports, BigDecimal completedVolume, long outboxBacklog) {
    }

    private final ModerationService moderationService;
    private final OrderService orderService;
    private final UserRepository users;
    private final ItemRepository items;
    private final OrderRepository orders;
    private final ReportRepository reports;
    private final OutboxEventRepository outbox;

    public AdminController(ModerationService moderationService, OrderService orderService, UserRepository users,
                           ItemRepository items, OrderRepository orders, ReportRepository reports,
                           OutboxEventRepository outbox) {
        this.moderationService = moderationService;
        this.orderService = orderService;
        this.users = users;
        this.items = items;
        this.orders = orders;
        this.reports = reports;
        this.outbox = outbox;
    }

    @Operation(summary = "Indicateurs clés de la plateforme")
    @GetMapping("/stats")
    public Stats stats() {
        return new Stats(users.count(), items.countByStatus(ItemStatus.ACTIVE), items.countByStatus(ItemStatus.SOLD),
                orders.countByStatusIn(EnumSet.of(OrderStatus.PAID, OrderStatus.SHIPPED, OrderStatus.DELIVERED)),
                orders.countByStatusIn(EnumSet.of(OrderStatus.DISPUTED)), reports.countByStatus(Report.Status.OPEN),
                orders.completedVolume(), outbox.countByPublishedAtIsNull());
    }

    @Operation(summary = "Signalements à traiter")
    @GetMapping("/reports")
    public PageResponse<ReportDto> reports(@RequestParam(defaultValue = "0") int page,
                                           @RequestParam(defaultValue = "20") int size) {
        return moderationService.open(page, size);
    }

    @Operation(summary = "Traiter un signalement (retrait d'article, suspension, classement)")
    @PostMapping("/reports/{id}/resolve")
    public ReportDto resolve(@PathVariable Long id, @Valid @RequestBody ResolveRequest request) {
        return moderationService.resolve(id, request);
    }

    @Operation(summary = "Suspendre / réactiver un membre")
    @PostMapping("/users/{id}/ban")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void ban(@PathVariable Long id, @RequestParam boolean banned) {
        moderationService.ban(id, banned);
    }

    @Operation(summary = "Commandes en litige")
    @GetMapping("/disputes")
    public PageResponse<OrderDto> disputes(@CurrentUser AuthenticatedUser admin,
                                           @RequestParam(defaultValue = "0") int page,
                                           @RequestParam(defaultValue = "20") int size) {
        return orderService.byStatus(OrderStatus.DISPUTED, admin.id(), page, size);
    }

    @Operation(summary = "Arbitrer un litige")
    @PostMapping("/orders/{id}/resolve-dispute")
    public OrderDto resolveDispute(@CurrentUser AuthenticatedUser admin, @PathVariable Long id,
                                   @RequestParam boolean refundBuyer) {
        return orderService.resolveDispute(admin.id(), id, refundBuyer);
    }
}
