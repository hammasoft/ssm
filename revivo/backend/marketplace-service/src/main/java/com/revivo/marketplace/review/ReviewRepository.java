package com.revivo.marketplace.review;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.EntityGraph;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ReviewRepository extends JpaRepository<Review, Long> {

    boolean existsByOrderIdAndAuthorId(Long orderId, Long authorId);

    @EntityGraph(attributePaths = {"author", "order", "order.item"})
    Page<Review> findByTargetId(Long targetId, Pageable pageable);
}
