package com.revivo.marketplace.user;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.security.CurrentUser;
import com.revivo.marketplace.user.UserDtos.ChangePasswordRequest;
import com.revivo.marketplace.user.UserDtos.MeResponse;
import com.revivo.marketplace.user.UserDtos.PublicProfile;
import com.revivo.marketplace.user.UserDtos.UpdateProfileRequest;
import com.revivo.marketplace.user.UserDtos.UserSummary;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.bind.annotation.RestController;

@Tag(name = "Membres")
@RestController
@RequestMapping("/api/users")
public class UserController {

    private final UserService userService;

    public UserController(UserService userService) {
        this.userService = userService;
    }

    @Operation(summary = "Mon compte")
    @GetMapping("/me")
    public MeResponse me(@CurrentUser AuthenticatedUser user) {
        return userService.me(user.id());
    }

    @Operation(summary = "Modifier mon profil (bio, ville, avatar, mode vacances)")
    @PutMapping("/me")
    public MeResponse update(@CurrentUser AuthenticatedUser user, @Valid @RequestBody UpdateProfileRequest request) {
        return userService.updateProfile(user.id(), request);
    }

    @Operation(summary = "Changer mon mot de passe (révoque toutes les sessions)")
    @PostMapping("/me/password")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void changePassword(@CurrentUser AuthenticatedUser user, @Valid @RequestBody ChangePasswordRequest request) {
        userService.changePassword(user.id(), request);
    }

    @Operation(summary = "Membres que je suis")
    @GetMapping("/me/following")
    public PageResponse<UserSummary> following(@CurrentUser AuthenticatedUser user,
                                               @PageableDefault(size = 20) Pageable pageable) {
        return userService.following(user.id(), pageable);
    }

    @Operation(summary = "Profil public d'un membre")
    @GetMapping("/{id}")
    public PublicProfile profile(@PathVariable Long id, @CurrentUser AuthenticatedUser viewer) {
        return userService.profile(id, viewer == null ? null : viewer.id());
    }

    @Operation(summary = "Suivre un membre")
    @PostMapping("/{id}/follow")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void follow(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        userService.follow(user.id(), id);
    }

    @Operation(summary = "Ne plus suivre un membre")
    @DeleteMapping("/{id}/follow")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void unfollow(@CurrentUser AuthenticatedUser user, @PathVariable Long id) {
        userService.unfollow(user.id(), id);
    }
}
