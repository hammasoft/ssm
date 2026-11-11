package com.revivo.marketplace.favorite;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.util.Collection;
import java.util.List;
import java.util.Optional;

public interface FavoriteRepository extends JpaRepository<Favorite, Long> {

    boolean existsByUserIdAndItemId(Long userId, Long itemId);

    Optional<Favorite> findByUserIdAndItemId(Long userId, Long itemId);

    @Query("SELECT f.item.id FROM Favorite f WHERE f.user.id = :userId AND f.item.id IN :itemIds")
    List<Long> findFavoritedItemIds(@Param("userId") Long userId, @Param("itemIds") Collection<Long> itemIds);

    @Query("SELECT f.user.id FROM Favorite f WHERE f.item.id = :itemId")
    List<Long> findUserIdsByItemId(@Param("itemId") Long itemId);

    @Query(value = "SELECT f FROM Favorite f JOIN FETCH f.item i JOIN FETCH i.seller WHERE f.user.id = :userId",
            countQuery = "SELECT count(f) FROM Favorite f WHERE f.user.id = :userId")
    Page<Favorite> findByUser(@Param("userId") Long userId, Pageable pageable);
}
