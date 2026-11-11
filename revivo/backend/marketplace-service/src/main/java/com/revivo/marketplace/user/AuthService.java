package com.revivo.marketplace.user;

import com.revivo.events.DomainEvent;
import com.revivo.events.UserRegistered;
import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.config.RevivoProperties;
import com.revivo.marketplace.outbox.DomainEventPublisher;
import com.revivo.marketplace.security.JwtService;
import com.revivo.marketplace.user.UserDtos.AuthResponse;
import com.revivo.marketplace.user.UserDtos.LoginRequest;
import com.revivo.marketplace.user.UserDtos.MeResponse;
import com.revivo.marketplace.user.UserDtos.RegisterRequest;
import com.revivo.marketplace.wallet.WalletService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.time.Clock;
import java.util.Base64;
import java.util.HexFormat;

@Service
public class AuthService {

    private static final Logger log = LoggerFactory.getLogger(AuthService.class);
    private static final SecureRandom RANDOM = new SecureRandom();

    private final UserRepository users;
    private final RefreshTokenRepository refreshTokens;
    private final PasswordEncoder passwordEncoder;
    private final JwtService jwtService;
    private final WalletService walletService;
    private final DomainEventPublisher events;
    private final RevivoProperties properties;
    private final Clock clock;

    public AuthService(UserRepository users, RefreshTokenRepository refreshTokens, PasswordEncoder passwordEncoder,
                       JwtService jwtService, WalletService walletService, DomainEventPublisher events,
                       RevivoProperties properties, Clock clock) {
        this.users = users;
        this.refreshTokens = refreshTokens;
        this.passwordEncoder = passwordEncoder;
        this.jwtService = jwtService;
        this.walletService = walletService;
        this.events = events;
        this.properties = properties;
        this.clock = clock;
    }

    @Transactional
    public AuthResponse register(RegisterRequest request) {
        if (users.existsByEmailIgnoreCase(request.email())) {
            throw new BusinessException("EMAIL_TAKEN", "Cet email est déjà utilisé.");
        }
        if (users.existsByUsernameIgnoreCase(request.username())) {
            throw new BusinessException("USERNAME_TAKEN", "Ce pseudo est déjà pris.");
        }
        User user = users.save(new User(request.username(), request.email().toLowerCase(),
                passwordEncoder.encode(request.password())));
        walletService.createFor(user);
        events.publish(new UserRegistered(DomainEvent.newId(), clock.instant(), user.getId(), user.getUsername(),
                user.getEmail()));
        log.info("Nouvel utilisateur inscrit : {}", user.getId());
        return issueTokens(user);
    }

    @Transactional
    public AuthResponse login(LoginRequest request) {
        User user = users.findByEmailIgnoreCase(request.email())
                .filter(u -> passwordEncoder.matches(request.password(), u.getPasswordHash()))
                .orElseThrow(() -> new BadCredentialsException("Email ou mot de passe incorrect."));
        if (user.isBanned()) {
            throw new BusinessException("ACCOUNT_BANNED", "Ce compte a été suspendu par la modération.");
        }
        return issueTokens(user);
    }

    /**
     * Rotation des refresh tokens : chaque token n'est utilisable qu'une fois. La réutilisation
     * d'un token déjà révoqué est le signe d'un vol : toutes les sessions sont alors révoquées.
     */
    @Transactional(noRollbackFor = BadCredentialsException.class)
    public AuthResponse refresh(String rawToken) {
        RefreshToken token = refreshTokens.findByTokenHash(hash(rawToken))
                .orElseThrow(() -> new BadCredentialsException("Refresh token invalide."));
        if (token.isRevoked()) {
            refreshTokens.revokeAllForUser(token.getUser().getId());
            log.warn("Réutilisation d'un refresh token révoqué pour l'utilisateur {}", token.getUser().getId());
            throw new BadCredentialsException("Session expirée, merci de vous reconnecter.");
        }
        if (!token.isUsable(clock.instant()) || token.getUser().isBanned()) {
            throw new BadCredentialsException("Session expirée, merci de vous reconnecter.");
        }
        token.revoke();
        return issueTokens(token.getUser());
    }

    @Transactional
    public void logout(String rawToken) {
        refreshTokens.findByTokenHash(hash(rawToken)).ifPresent(RefreshToken::revoke);
    }

    private AuthResponse issueTokens(User user) {
        byte[] bytes = new byte[48];
        RANDOM.nextBytes(bytes);
        String raw = Base64.getUrlEncoder().withoutPadding().encodeToString(bytes);
        refreshTokens.save(new RefreshToken(user, hash(raw),
                clock.instant().plus(properties.security().refreshTokenTtl())));
        String access = jwtService.generateAccessToken(user.getId(), user.getUsername(), user.getRole().name());
        return new AuthResponse(access, raw, jwtService.accessTokenTtlSeconds(), MeResponse.from(user));
    }

    static String hash(String raw) {
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            return HexFormat.of().formatHex(digest.digest(raw.getBytes(StandardCharsets.UTF_8)));
        } catch (NoSuchAlgorithmException e) {
            throw new IllegalStateException(e);
        }
    }
}
