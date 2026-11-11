package com.revivo.marketplace.review;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.review.ReviewService.ReviewDto;
import com.revivo.marketplace.review.ReviewService.ReviewRequest;
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

@Tag(name = "Avis")
@RestController
@RequestMapping("/api")
public class ReviewController {

    private final ReviewService reviewService;

    public ReviewController(ReviewService reviewService) {
        this.reviewService = reviewService;
    }

    @Operation(summary = "Évaluer l'autre partie d'une commande finalisée")
    @PostMapping("/orders/{orderId}/review")
    @ResponseStatus(HttpStatus.CREATED)
    public ReviewDto post(@CurrentUser AuthenticatedUser user, @PathVariable Long orderId,
                          @Valid @RequestBody ReviewRequest request) {
        return reviewService.post(user.id(), orderId, request);
    }

    @Operation(summary = "Avis reçus par un membre")
    @GetMapping("/users/{id}/reviews")
    public PageResponse<ReviewDto> forUser(@PathVariable Long id, @RequestParam(defaultValue = "0") int page,
                                           @RequestParam(defaultValue = "20") int size) {
        return reviewService.forUser(id, page, size);
    }
}
