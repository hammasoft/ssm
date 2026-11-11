package com.revivo.marketplace.security;

import org.springframework.security.core.GrantedAuthority;
import org.springframework.security.core.authority.SimpleGrantedAuthority;

import java.util.Collection;
import java.util.List;

/** Principal léger reconstruit depuis le JWT (aucun accès base par requête). */
public record AuthenticatedUser(Long id, String username, String role) {

    public Collection<? extends GrantedAuthority> authorities() {
        return List.of(new SimpleGrantedAuthority("ROLE_" + role));
    }

    public boolean isAdmin() {
        return "ADMIN".equals(role);
    }
}
