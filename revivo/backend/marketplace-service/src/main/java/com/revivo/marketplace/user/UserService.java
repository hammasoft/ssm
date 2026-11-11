package com.revivo.marketplace.user;

import com.revivo.events.DomainEvent;
import com.revivo.events.UserFollowed;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.NotFoundException;
import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.item.ItemRepository;
import com.revivo.marketplace.item.ItemStatus;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.user.UserDtos.ChangePasswordRequest;
import com.revivo.marketplace.user.UserDtos.MeResponse;
import com.revivo.marketplace.user.UserDtos.PublicProfile;
import com.revivo.marketplace.user.UserDtos.UpdateProfileRequest;
import com.revivo.marketplace.user.UserDtos.UserSummary;
import org.springframework.data.domain.Pageable;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Clock;

@Service
@Transactional(readOnly = true)
public class UserService {

    private final UserRepository users;
    private final FollowRepository follows;
    private final ItemRepository items;
    private final RefreshTokenRepository refreshTokens;
    private final PasswordEncoder passwordEncoder;
    private final DomainEventPublisher events;
    private final Clock clock;

    public UserService(UserRepository users, FollowRepository follows, ItemRepository items,
                       RefreshTokenRepository refreshTokens, PasswordEncoder passwordEncoder,
                       DomainEventPublisher events, Clock clock) {
        this.users = users;
        this.follows = follows;
        this.items = items;
        this.refreshTokens = refreshTokens;
        this.passwordEncoder = passwordEncoder;
        this.events = events;
        this.clock = clock;
    }

    public User getUser(Long id) {
        return users.findById(id).orElseThrow(() -> new NotFoundException("Utilisateur", id));
    }

    public MeResponse me(Long userId) {
        return MeResponse.from(getUser(userId));
    }

    public PublicProfile profile(Long userId, Long viewerId) {
        User u = getUser(userId);
        boolean followed = viewerId != null && follows.existsByFollowerIdAndFollowedId(viewerId, userId);
        return new PublicProfile(u.getId(), u.getUsername(), u.getBio(), u.getCity(), u.getCountry(),
                u.getAvatarUrl(), u.isHolidayMode(), u.getRatingAvg(), u.getRatingCount(),
                follows.countByFollowedId(userId), follows.countByFollowerId(userId),
                items.countBySellerIdAndStatus(userId, ItemStatus.ACTIVE), followed, u.getCreatedAt());
    }

    @Transactional
    public MeResponse updateProfile(Long userId, UpdateProfileRequest request) {
        User user = getUser(userId);
        user.updateProfile(request.bio(), request.city(), request.country(), request.avatarUrl());
        if (request.holidayMode() != null) {
            user.setHolidayMode(request.holidayMode());
        }
        return MeResponse.from(user);
    }

    @Transactional
    public void changePassword(Long userId, ChangePasswordRequest request) {
        User user = getUser(userId);
        if (!passwordEncoder.matches(request.currentPassword(), user.getPasswordHash())) {
            throw new BusinessException("WRONG_PASSWORD", "Mot de passe actuel incorrect.");
        }
        user.changePasswordHash(passwordEncoder.encode(request.newPassword()));
        refreshTokens.revokeAllForUser(userId);
    }

    @Transactional
    public void follow(Long followerId, Long followedId) {
        if (followerId.equals(followedId)) {
            throw new BusinessException("FOLLOW_SELF", "Vous ne pouvez pas vous suivre vous-même.");
        }
        if (follows.existsByFollowerIdAndFollowedId(followerId, followedId)) {
            return; // idempotent
        }
        User follower = getUser(followerId);
        follows.save(new Follow(follower, getUser(followedId)));
        events.publish(new UserFollowed(DomainEvent.newId(), clock.instant(), followerId, follower.getUsername(),
                followedId));
    }

    @Transactional
    public void unfollow(Long followerId, Long followedId) {
        follows.findByFollowerIdAndFollowedId(followerId, followedId).ifPresent(follows::delete);
    }

    public PageResponse<UserSummary> following(Long userId, Pageable pageable) {
        return PageResponse.of(follows.findFollowing(userId, pageable), f -> UserSummary.from(f.getFollowed()));
    }
}
