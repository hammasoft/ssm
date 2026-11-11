package com.revivo.marketplace.item;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.data.jpa.repository.EntityGraph;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.data.jpa.repository.Lock;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.util.Collection;
import java.util.List;
import java.util.Optional;
import jakarta.persistence.LockModeType;

public interface ItemRepository extends JpaRepository<Item, Long>, JpaSpecificationExecutor<Item> {

    @Override
    @EntityGraph(attributePaths = {"seller", "brand"})
    Page<Item> findAll(Specification<Item> spec, Pageable pageable);

    /** Verrou pessimiste pendant le checkout : sérialise les achats concurrents d'un même article. */
    @Lock(LockModeType.PESSIMISTIC_WRITE)
    @Query("SELECT i FROM Item i WHERE i.id = :id")
    Optional<Item> lockById(@Param("id") Long id);

    long countBySellerIdAndStatus(Long sellerId, ItemStatus status);

    long countByStatus(ItemStatus status);

    @EntityGraph(attributePaths = {"seller", "brand"})
    Page<Item> findBySellerIdAndStatusIn(Long sellerId, Collection<ItemStatus> statuses, Pageable pageable);

    @EntityGraph(attributePaths = {"seller", "brand"})
    Page<Item> findBySellerIdInAndStatus(Collection<Long> sellerIds, ItemStatus status, Pageable pageable);

    @EntityGraph(attributePaths = {"seller", "brand"})
    List<Item> findTop12ByCategoryIdAndStatusAndIdNotOrderByCreatedAtDesc(Long categoryId, ItemStatus status, Long id);

    /** Mises à jour de compteurs sans incrémenter la version (pas de conflit optimiste). */
    @Modifying
    @Query("UPDATE Item i SET i.viewCount = i.viewCount + 1 WHERE i.id = :id")
    void incrementViews(@Param("id") Long id);

    @Modifying
    @Query("UPDATE Item i SET i.favoriteCount = i.favoriteCount + :delta WHERE i.id = :id")
    void adjustFavoriteCount(@Param("id") Long id, @Param("delta") int delta);
}
