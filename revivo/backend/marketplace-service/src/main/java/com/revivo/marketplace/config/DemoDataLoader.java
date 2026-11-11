package com.revivo.marketplace.config;

import com.revivo.marketplace.item.ItemCondition;
import com.revivo.marketplace.item.ItemDtos.ItemRequest;
import com.revivo.marketplace.item.ItemService;
import com.revivo.marketplace.user.AuthService;
import com.revivo.marketplace.user.UserDtos.RegisterRequest;
import com.revivo.marketplace.user.UserDtos.UpdateProfileRequest;
import com.revivo.marketplace.user.UserRepository;
import com.revivo.marketplace.user.UserService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.boot.ApplicationArguments;
import org.springframework.boot.ApplicationRunner;
import org.springframework.boot.autoconfigure.condition.ConditionalOnProperty;
import org.springframework.stereotype.Component;
import org.springframework.transaction.support.TransactionTemplate;

import javax.imageio.ImageIO;
import java.awt.Color;
import java.awt.GradientPaint;
import java.awt.Graphics2D;
import java.awt.RenderingHints;
import java.awt.image.BufferedImage;
import java.io.IOException;
import java.math.BigDecimal;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.List;

/**
 * Jeu de données de démonstration (désactivable via {@code DEMO_DATA=false}).
 * Passe par les services métier : les événements Kafka correspondants sont donc émis.
 */
@Component
@ConditionalOnProperty(name = "revivo.demo-data", havingValue = "true")
class DemoDataLoader implements ApplicationRunner {

    private static final Logger log = LoggerFactory.getLogger(DemoDataLoader.class);
    static final String DEMO_PASSWORD = "Password123!";

    private record Demo(String title, String description, long categoryId, Long brandId, String size, String color,
                        ItemCondition condition, String price, Color c1, Color c2) {
    }

    private final UserRepository users;
    private final AuthService authService;
    private final UserService userService;
    private final ItemService itemService;
    private final TransactionTemplate tx;
    private final Path uploadDir;

    DemoDataLoader(UserRepository users, AuthService authService, UserService userService, ItemService itemService,
                   TransactionTemplate tx, RevivoProperties properties) {
        this.users = users;
        this.authService = authService;
        this.userService = userService;
        this.itemService = itemService;
        this.tx = tx;
        this.uploadDir = Path.of(properties.storage().uploadDir()).toAbsolutePath().normalize();
    }

    @Override
    public void run(ApplicationArguments args) {
        if (users.count() > 0) {
            return;
        }
        log.info("Chargement des données de démonstration…");
        Long admin = register("admin", "admin@revivo.dev");
        tx.executeWithoutResult(s -> users.findById(admin).orElseThrow().promoteToAdmin());
        Long alice = register("alice", "alice@revivo.dev");
        Long bob = register("bob", "bob@revivo.dev");
        Long chloe = register("chloe", "chloe@revivo.dev");

        userService.updateProfile(alice, new UpdateProfileRequest("Passionnée de vintage, envoi rapide 📦", "Lyon", "FR", null, false));
        userService.updateProfile(bob, new UpdateProfileRequest("Je vide mon dressing !", "Paris", "FR", null, false));
        userService.updateProfile(chloe, new UpdateProfileRequest("Maman de 2, vêtements enfants en super état", "Nantes", "FR", null, false));
        tx.executeWithoutResult(s -> {
            userService.follow(bob, alice);
            userService.follow(chloe, alice);
        });

        List<Demo> aliceItems = List.of(
                new Demo("Veste en jean vintage", "Veste en jean oversize des années 90, très bon état, aucune tache.", 12, 5L, "M", "Bleu", ItemCondition.VERY_GOOD, "24.00", new Color(0x3B5BA5), new Color(0x8FB3E8)),
                new Demo("Robe fleurie d'été", "Robe légère à fleurs, portée deux fois. Idéale pour l'été.", 10, 3L, "S", "Multicolore", ItemCondition.VERY_GOOD, "15.00", new Color(0xE76F51), new Color(0xF4A261)),
                new Demo("Sac à main en cuir camel", "Sac en cuir véritable, bandoulière réglable, quelques traces d'usage.", 15, 18L, "Unique", "Marron", ItemCondition.GOOD, "45.00", new Color(0x8D5524), new Color(0xC68642)),
                new Demo("Baskets blanches", "Baskets en cuir blanc, pointure 38, nettoyées.", 14, 1L, "38", "Blanc", ItemCondition.GOOD, "30.00", new Color(0xDADADA), new Color(0xFFFFFF)));
        List<Demo> bobItems = List.of(
                new Demo("Doudoune légère noire", "Doudoune compressible, parfaite pour la mi-saison.", 22, 7L, "L", "Noir", ItemCondition.VERY_GOOD, "35.00", new Color(0x1D1D1D), new Color(0x555555)),
                new Demo("Sweat à capuche gris", "Sweat épais et chaud, coupe droite.", 21, 21L, "M", "Gris", ItemCondition.GOOD, "18.00", new Color(0x6C757D), new Color(0xADB5BD)),
                new Demo("Jean brut coupe droite", "Jean brut 501, W32 L32, très peu porté.", 23, 5L, "40", "Marine", ItemCondition.NEW_WITHOUT_TAGS, "40.00", new Color(0x14213D), new Color(0x3A506B)),
                new Demo("Console rétro + 2 manettes", "Console rétro fonctionnelle avec câbles et deux manettes.", 51, null, null, "Gris", ItemCondition.GOOD, "60.00", new Color(0x2A9D8F), new Color(0x264653)));
        List<Demo> chloeItems = List.of(
                new Demo("Lot bodies bébé 6 mois", "Lot de 5 bodies coton bio, lavés et repassés.", 30, 15L, "Unique", "Blanc", ItemCondition.VERY_GOOD, "12.00", new Color(0xFFD6E0), new Color(0xFFF1F4)),
                new Demo("Puzzle bois 24 pièces", "Puzzle en bois complet, dès 3 ans.", 32, null, null, "Multicolore", ItemCondition.GOOD, "8.00", new Color(0xFFB703), new Color(0xFB8500)),
                new Demo("Plaid en laine", "Grand plaid tout doux 130×170 cm.", 41, 17L, null, "Beige", ItemCondition.NEW_WITH_TAGS, "22.00", new Color(0xD4A373), new Color(0xFAEDCD)));

        int n = 0;
        n += publish(alice, aliceItems);
        n += publish(bob, bobItems);
        n += publish(chloe, chloeItems);
        log.info("Démo prête : 4 comptes (mot de passe « {} »), {} annonces.", DEMO_PASSWORD, n);
    }

    private Long register(String username, String email) {
        return authService.register(new RegisterRequest(username, email, DEMO_PASSWORD)).user().id();
    }

    private int publish(Long sellerId, List<Demo> demos) {
        for (Demo d : demos) {
            String url = placeholder(d.c1(), d.c2());
            itemService.create(sellerId, new ItemRequest(d.title(), d.description(), d.categoryId(), d.brandId(),
                    d.size(), d.color(), d.condition(), new BigDecimal(d.price()), List.of(url)));
        }
        return demos.size();
    }

    /** Génère une image dégradée (pas de dépendance à des polices ni à Internet). */
    private String placeholder(Color c1, Color c2) {
        try {
            Files.createDirectories(uploadDir);
            BufferedImage img = new BufferedImage(600, 800, BufferedImage.TYPE_INT_RGB);
            Graphics2D g = img.createGraphics();
            g.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
            g.setPaint(new GradientPaint(0, 0, c1, 600, 800, c2));
            g.fillRect(0, 0, 600, 800);
            g.setColor(new Color(255, 255, 255, 60));
            g.fillOval(150, 250, 300, 300);
            g.dispose();
            String name = "demo-" + java.util.UUID.randomUUID() + ".png";
            ImageIO.write(img, "png", uploadDir.resolve(name).toFile());
            return "/uploads/" + name;
        } catch (IOException e) {
            throw new IllegalStateException(e);
        }
    }
}
