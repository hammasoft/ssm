package com.revivo.marketplace.user;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.util.List;
import java.util.Optional;

public interface FollowRepository extends JpaRepository<Follow, Long> {

    Optional<Follow> findByFollowerIdAndFollowedId(Long followerId, Long followedId);

    boolean existsByFollowerIdAndFollowedId(Long followerId, Long followedId);

    long countByFollowedId(Long followedId);

    long countByFollowerId(Long followerId);

    @Query("SELECT f.follower.id FROM Follow f WHERE f.followed.id = :userId")
    List<Long> findFollowerIds(@Param("userId") Long userId);

    @Query("SELECT f.followed.id FROM Follow f WHERE f.follower.id = :userId")
    List<Long> findFollowedIds(@Param("userId") Long userId);

    @Query(value = "SELECT f FROM Follow f JOIN FETCH f.followed WHERE f.follower.id = :userId",
            countQuery = "SELECT count(f) FROM Follow f WHERE f.follower.id = :userId")
    Page<Follow> findFollowing(@Param("userId") Long userId, Pageable pageable);
}
