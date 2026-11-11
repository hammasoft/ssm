package com.revivo.marketplace.review;

import com.revivo.events.DomainEvent;
import com.revivo.events.ReviewPosted;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.ForbiddenException;
import com.revivo.marketplace.common.NotFoundException;
import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.order.Order;
import com.revivo.marketplace.order.OrderRepository;
import com.revivo.marketplace.order.OrderStatus;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserDtos.UserSummary;
import jakarta.validation.constraints.Max;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.Size;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Clock;
import java.time.Instant;

@Service
@Transactional(readOnly = true)
public class ReviewService {

    public record ReviewRequest(@Min(1) @Max(5) int rating, @Size(max = 1000) String comment) {
    }

    public record ReviewDto(Long id, Long orderId, String itemTitle, int rating, String comment, String authorRole,
                            UserSummary author, Instant createdAt) {
    }

    private final ReviewRepository reviews;
    private final OrderRepository orders;
    private final DomainEventPublisher events;
    private final Clock clock;

    public ReviewService(ReviewRepository reviews, OrderRepository orders, DomainEventPublisher events, Clock clock) {
        this.reviews = reviews;
        this.orders = orders;
        this.events = events;
        this.clock = clock;
    }

    /** Évaluation croisée : acheteur et vendeur peuvent chacun noter l'autre, une fois, après finalisation. */
    @Transactional
    public ReviewDto post(Long authorId, Long orderId, ReviewRequest request) {
        Order order = orders.findById(orderId).orElseThrow(() -> new NotFoundException("Commande", orderId));
        if (!order.involves(authorId)) {
            throw new ForbiddenException("Vous n'avez pas participé à cette transaction.");
        }
        if (order.getStatus() != OrderStatus.COMPLETED) {
            throw new BusinessException("ORDER_NOT_COMPLETED", "Vous pourrez laisser un avis une fois la commande finalisée.");
        }
        if (reviews.existsByOrderIdAndAuthorId(orderId, authorId)) {
            throw new BusinessException("ALREADY_REVIEWED", "Vous avez déjà évalué cette transaction.");
        }
        User author = order.isBuyer(authorId) ? order.getBuyer() : order.getSeller();
        User target = order.isBuyer(authorId) ? order.getSeller() : order.getBuyer();
        Review review = reviews.save(new Review(order, author, target, request.rating(),
                request.comment() == null ? null : request.comment().trim()));
        target.addRating(request.rating());
        events.publish(new ReviewPosted(DomainEvent.newId(), clock.instant(), review.getId(), orderId, authorId,
                author.getUsername(), target.getId(), request.rating()));
        return toDto(review);
    }

    public PageResponse<ReviewDto> forUser(Long userId, int page, int size) {
        return PageResponse.of(reviews.findByTargetId(userId,
                PageRequest.of(page, Math.min(size, 50), Sort.by(Sort.Direction.DESC, "createdAt"))), this::toDto);
    }

    private ReviewDto toDto(Review r) {
        String role = r.getOrder().isBuyer(r.getAuthor().getId()) ? "BUYER" : "SELLER";
        return new ReviewDto(r.getId(), r.getOrder().getId(), r.getOrder().getItem().getTitle(), r.getRating(),
                r.getComment(), role, UserSummary.from(r.getAuthor()), r.getCreatedAt());
    }
}
