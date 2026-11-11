package com.revivo.marketplace.wallet;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.security.AuthenticatedUser;
import com.revivo.marketplace.security.CurrentUser;
import com.revivo.marketplace.wallet.WalletService.TransactionDto;
import com.revivo.marketplace.wallet.WalletService.WalletSummary;
import com.revivo.marketplace.wallet.WalletService.WithdrawRequest;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

@Tag(name = "Porte-monnaie")
@RestController
@RequestMapping("/api/wallet")
public class WalletController {

    private final WalletService walletService;

    public WalletController(WalletService walletService) {
        this.walletService = walletService;
    }

    @Operation(summary = "Soldes disponible et en attente")
    @GetMapping
    public WalletSummary summary(@CurrentUser AuthenticatedUser user) {
        return walletService.summary(user.id());
    }

    @Operation(summary = "Historique des mouvements")
    @GetMapping("/transactions")
    public PageResponse<TransactionDto> history(@CurrentUser AuthenticatedUser user,
                                                @RequestParam(defaultValue = "0") int page,
                                                @RequestParam(defaultValue = "20") int size) {
        return walletService.history(user.id(), page, Math.min(size, 100));
    }

    @Operation(summary = "Retirer le solde disponible vers un IBAN")
    @PostMapping("/withdrawals")
    public WalletSummary withdraw(@CurrentUser AuthenticatedUser user, @Valid @RequestBody WithdrawRequest request) {
        return walletService.withdraw(user.id(), request);
    }
}
