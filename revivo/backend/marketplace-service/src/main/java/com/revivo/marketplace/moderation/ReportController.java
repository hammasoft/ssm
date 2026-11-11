package com.revivo.marketplace.moderation;

import com.revivo.marketplace.moderation.ModerationService.ReportDto;
import com.revivo.marketplace.moderation.ModerationService.ReportRequest;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.security.CurrentUser;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.bind.annotation.RestController;

@Tag(name = "Signalements")
@RestController
@RequestMapping("/api/reports")
public class ReportController {

    private final ModerationService moderationService;

    public ReportController(ModerationService moderationService) {
        this.moderationService = moderationService;
    }

    @Operation(summary = "Signaler un article ou un membre")
    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public ReportDto report(@CurrentUser AuthenticatedUser user, @Valid @RequestBody ReportRequest request) {
        return moderationService.report(user.id(), request);
    }
}
