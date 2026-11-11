package com.revivo.marketplace.wallet;

import com.revivo.marketplace.common.PageResponse;
import com.revivo.marketplace.user.User;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Pattern;
import org.springframework.data.domain.PageRequest;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Propagation;
import org.springframework.transaction.annotation.Transactional;

import java.math.BigDecimal;
import java.time.Instant;

@Service
@Transactional(readOnly = true)
public class WalletService {

    public record WalletSummary(BigDecimal available, BigDecimal pending) {
    }

    public record TransactionDto(Long id, String type, BigDecimal amount, Long orderId, String description,
                                 Instant createdAt) {
    }

    public record WithdrawRequest(@NotNull @DecimalMin("1.00") BigDecimal amount,
                                  @NotNull @Pattern(regexp = "^[A-Z]{2}[0-9]{2}[A-Z0-9]{11,30}$",
                                          message = "IBAN invalide") String iban) {
    }

    private final WalletRepository wallets;
    private final WalletTransactionRepository transactions;

    public WalletService(WalletRepository wallets, WalletTransactionRepository transactions) {
        this.wallets = wallets;
        this.transactions = transactions;
    }

    @Transactional(propagation = Propagation.MANDATORY)
    public void createFor(User user) {
        wallets.save(new Wallet(user));
    }

    public WalletSummary summary(Long userId) {
        Wallet w = walletOf(userId);
        return new WalletSummary(w.getAvailable(), w.getPending());
    }

    public PageResponse<TransactionDto> history(Long userId, int page, int size) {
        Wallet w = walletOf(userId);
        return PageResponse.of(transactions.findByWalletIdOrderByCreatedAtDesc(w.getId(), PageRequest.of(page, size)),
                t -> new TransactionDto(t.getId(), t.getType().name(), t.getAmount(), t.getOrderId(),
                        t.getDescription(), t.getCreatedAt()));
    }

    @Transactional(propagation = Propagation.MANDATORY)
    public void escrow(Long sellerId, Long orderId, BigDecimal amount) {
        Wallet w = walletOf(sellerId);
        w.creditPending(amount);
        transactions.save(new WalletTransaction(w, WalletTransactionType.SALE_ESCROW, amount, orderId,
                "Vente — paiement sous séquestre"));
    }

    @Transactional(propagation = Propagation.MANDATORY)
    public void release(Long sellerId, Long orderId, BigDecimal amount) {
        Wallet w = walletOf(sellerId);
        w.releasePending(amount);
        transactions.save(new WalletTransaction(w, WalletTransactionType.SALE_RELEASED, amount, orderId,
                "Vente validée — fonds disponibles"));
    }

    @Transactional(propagation = Propagation.MANDATORY)
    public void cancelEscrow(Long sellerId, Long orderId, BigDecimal amount) {
        Wallet w = walletOf(sellerId);
        w.cancelPending(amount);
        transactions.save(new WalletTransaction(w, WalletTransactionType.SALE_CANCELLED, amount.negate(), orderId,
                "Commande annulée — séquestre restitué à l'acheteur"));
    }

    @Transactional
    public WalletSummary withdraw(Long userId, WithdrawRequest request) {
        Wallet w = walletOf(userId);
        w.withdraw(request.amount());
        String masked = "****" + request.iban().substring(request.iban().length() - 4);
        transactions.save(new WalletTransaction(w, WalletTransactionType.WITHDRAWAL, request.amount().negate(), null,
                "Virement vers " + masked));
        return new WalletSummary(w.getAvailable(), w.getPending());
    }

    private Wallet walletOf(Long userId) {
        return wallets.findByUserId(userId)
                .orElseThrow(() -> new IllegalStateException("Porte-monnaie manquant pour l'utilisateur " + userId));
    }
}
