package com.revivo.marketplace.moderation;

import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.common.NotFoundException;
import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.item.Item;
import com.revivo.marketplace.item.ItemService;
import com.revivo.marketplace.user.RefreshTokenRepository;
import com.revivo.marketplace.user.User;
import com.revivo.marketplace.user.UserService;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.Clock;
import java.time.Instant;

@Service
@Transactional(readOnly = true)
public class ModerationService {

    public enum Action { REMOVE_ITEM, BAN_USER, DISMISS }

    public record ReportRequest(Long itemId, Long userId, @NotNull Report.Reason reason,
                                @Size(max = 1000) String details) {
    }

    public record ResolveRequest(@NotNull Action action, @Size(max = 500) String note) {
    }

    public record ReportDto(Long id, String reason, String details, String status, Long itemId, String itemTitle,
                            Long reportedUserId, String reportedUsername, Long reporterId, String reporterUsername,
                            String resolutionNote, Instant createdAt) {
    }

    private final ReportRepository reports;
    private final ItemService itemService;
    private final UserService userService;
    private final RefreshTokenRepository refreshTokens;
    private final Clock clock;

    public ModerationService(ReportRepository reports, ItemService itemService, UserService userService,
                             RefreshTokenRepository refreshTokens, Clock clock) {
        this.reports = reports;
        this.itemService = itemService;
        this.userService = userService;
        this.refreshTokens = refreshTokens;
        this.clock = clock;
    }

    @Transactional
    public ReportDto report(Long reporterId, ReportRequest request) {
        if (request.itemId() == null && request.userId() == null) {
            throw new BusinessException("REPORT_TARGET_REQUIRED", "Indiquez un article ou un membre à signaler.");
        }
        Item item = request.itemId() == null ? null : itemService.find(request.itemId());
        User reported = request.userId() != null ? userService.getUser(request.userId())
                : item.getSeller();
        Report report = reports.save(new Report(userService.getUser(reporterId), item, reported, request.reason(),
                request.details()));
        return toDto(report);
    }

    public PageResponse<ReportDto> open(int page, int size) {
        return PageResponse.of(reports.findByStatus(Report.Status.OPEN,
                PageRequest.of(page, Math.min(size, 100), Sort.by("createdAt"))), this::toDto);
    }

    @Transactional
    public ReportDto resolve(Long reportId, ResolveRequest request) {
        Report report = reports.findById(reportId).orElseThrow(() -> new NotFoundException("Signalement", reportId));
        if (report.getStatus() != Report.Status.OPEN) {
            throw new BusinessException("REPORT_CLOSED", "Ce signalement est déjà traité.");
        }
        Instant now = clock.instant();
        switch (request.action()) {
            case REMOVE_ITEM -> {
                if (report.getItem() == null) {
                    throw new BusinessException("NO_ITEM", "Ce signalement ne concerne pas un article.");
                }
                report.getItem().removeByModeration();
                report.close(Report.Status.RESOLVED, note(request, "Article retiré"), now);
            }
            case BAN_USER -> {
                ban(report.getReportedUser().getId(), true);
                report.close(Report.Status.RESOLVED, note(request, "Membre suspendu"), now);
            }
            case DISMISS -> report.close(Report.Status.DISMISSED, note(request, "Sans suite"), now);
        }
        return toDto(report);
    }

    @Transactional
    public void ban(Long userId, boolean banned) {
        User user = userService.getUser(userId);
        user.setBanned(banned);
        if (banned) {
            refreshTokens.revokeAllForUser(userId);
        }
    }

    private static String note(ResolveRequest request, String fallback) {
        return request.note() == null || request.note().isBlank() ? fallback : request.note();
    }

    private ReportDto toDto(Report r) {
        return new ReportDto(r.getId(), r.getReason().name(), r.getDetails(), r.getStatus().name(),
                r.getItem() == null ? null : r.getItem().getId(),
                r.getItem() == null ? null : r.getItem().getTitle(),
                r.getReportedUser() == null ? null : r.getReportedUser().getId(),
                r.getReportedUser() == null ? null : r.getReportedUser().getUsername(),
                r.getReporter().getId(), r.getReporter().getUsername(), r.getResolutionNote(), r.getCreatedAt());
    }
}
