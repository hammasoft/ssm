package com.revivo.marketplace.order;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.EntityGraph;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;

import java.math.BigDecimal;
import java.time.Instant;
import java.util.Collection;
import java.util.List;

public interface OrderRepository extends JpaRepository<Order, Long> {

    boolean existsByItemId(Long itemId);

    @EntityGraph(attributePaths = {"item", "buyer", "seller"})
    Page<Order> findByBuyerId(Long buyerId, Pageable pageable);

    @EntityGraph(attributePaths = {"item", "buyer", "seller"})
    Page<Order> findBySellerId(Long sellerId, Pageable pageable);

    @EntityGraph(attributePaths = {"item", "buyer", "seller"})
    Page<Order> findByStatus(OrderStatus status, Pageable pageable);

    List<Order> findTop100ByStatusAndDeliveredAtBefore(OrderStatus status, Instant before);

    long countByStatusIn(Collection<OrderStatus> statuses);

    @Query("SELECT COALESCE(SUM(o.itemPrice), 0) FROM Order o WHERE o.status = com.revivo.marketplace.order.OrderStatus.COMPLETED")
    BigDecimal completedVolume();
}
