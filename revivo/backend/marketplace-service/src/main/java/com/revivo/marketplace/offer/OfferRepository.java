package com.revivo.marketplace.offer;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.EntityGraph;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.time.Instant;
import java.util.Collection;
import java.util.List;

public interface OfferRepository extends JpaRepository<Offer, Long> {

    List<Offer> findByItemIdAndBuyerIdAndStatusIn(Long itemId, Long buyerId, Collection<OfferStatus> statuses);

    List<Offer> findByItemIdAndStatusIn(Long itemId, Collection<OfferStatus> statuses);

    @EntityGraph(attributePaths = {"item", "item.seller", "buyer"})
    Page<Offer> findByBuyerId(Long buyerId, Pageable pageable);

    @EntityGraph(attributePaths = {"item", "item.seller", "buyer"})
    Page<Offer> findByItemSellerId(Long sellerId, Pageable pageable);

    @Modifying
    @Query("""
            UPDATE Offer o SET o.status = com.revivo.marketplace.offer.OfferStatus.EXPIRED
            WHERE o.status IN (com.revivo.marketplace.offer.OfferStatus.PENDING,
                               com.revivo.marketplace.offer.OfferStatus.ACCEPTED)
              AND o.expiresAt < :now
            """)
    int expireAll(@Param("now") Instant now);
}
