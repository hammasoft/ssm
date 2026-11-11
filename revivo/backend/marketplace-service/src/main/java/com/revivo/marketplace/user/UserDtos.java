package com.revivo.marketplace.user;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Pattern;
import jakarta.validation.constraints.Size;

import java.math.BigDecimal;
import java.time.Instant;

/** DTOs du contexte utilisateur (records immuables, validés par Bean Validation). */
public final class UserDtos {

    private UserDtos() {
    }

    public record RegisterRequest(
            @NotBlank @Size(min = 3, max = 30) @Pattern(regexp = "^[a-zA-Z0-9_.-]+$",
                    message = "lettres, chiffres, '.', '_' et '-' uniquement") String username,
            @NotBlank @Email String email,
            @NotBlank @Size(min = 8, max = 100) String password) {
    }

    public record LoginRequest(@NotBlank String email, @NotBlank String password) {
    }

    public record RefreshRequest(@NotBlank String refreshToken) {
    }

    public record AuthResponse(String accessToken, String refreshToken, long expiresIn, MeResponse user) {
    }

    public record MeResponse(Long id, String username, String email, String role, String bio, String city,
                             String country, String avatarUrl, boolean holidayMode, BigDecimal ratingAvg,
                             int ratingCount, Instant createdAt) {

        static MeResponse from(User u) {
            return new MeResponse(u.getId(), u.getUsername(), u.getEmail(), u.getRole().name(), u.getBio(),
                    u.getCity(), u.getCountry(), u.getAvatarUrl(), u.isHolidayMode(), u.getRatingAvg(),
                    u.getRatingCount(), u.getCreatedAt());
        }
    }

    public record PublicProfile(Long id, String username, String bio, String city, String country,
                                String avatarUrl, boolean holidayMode, BigDecimal ratingAvg, int ratingCount,
                                long followers, long following, long activeItems, boolean followedByMe,
                                Instant memberSince) {
    }

    public record UserSummary(Long id, String username, String avatarUrl, BigDecimal ratingAvg, int ratingCount) {

        public static UserSummary from(User u) {
            return new UserSummary(u.getId(), u.getUsername(), u.getAvatarUrl(), u.getRatingAvg(), u.getRatingCount());
        }
    }

    public record UpdateProfileRequest(@Size(max = 500) String bio, @Size(max = 100) String city,
                                       @Pattern(regexp = "^[A-Z]{2}$", message = "code pays ISO-3166 (ex : FR)") String country,
                                       @Size(max = 500) String avatarUrl, Boolean holidayMode) {
    }

    public record ChangePasswordRequest(@NotBlank String currentPassword,
                                        @NotBlank @Size(min = 8, max = 100) String newPassword) {
    }
}
