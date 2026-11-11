package com.revivo.marketplace.storage;

import com.revivo.marketplace.common.BusinessException;
import com.revivo.marketplace.config.RevivoProperties;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import java.io.IOException;
import java.io.InputStream;
import java.io.UncheckedIOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.util.Map;
import java.util.UUID;

/**
 * Stockage des photos sur disque local. L'interface publique (URL {@code /uploads/...})
 * permet de basculer vers S3/MinIO sans impact sur le reste de l'application.
 */
@Service
public class StorageService {

    public static final String PUBLIC_PREFIX = "/uploads/";

    private static final Map<String, String> ALLOWED = Map.of(
            "image/jpeg", ".jpg", "image/png", ".png", "image/webp", ".webp");

    private final Path root;

    public StorageService(RevivoProperties properties) {
        this.root = Path.of(properties.storage().uploadDir()).toAbsolutePath().normalize();
        try {
            Files.createDirectories(root);
        } catch (IOException e) {
            throw new UncheckedIOException("Impossible de créer le dossier d'upload " + root, e);
        }
    }

    public String store(MultipartFile file) {
        String extension = ALLOWED.get(file.getContentType());
        if (extension == null || file.isEmpty()) {
            throw new BusinessException("INVALID_FILE", "Formats acceptés : JPEG, PNG, WEBP.");
        }
        try (InputStream in = file.getInputStream()) {
            byte[] header = in.readNBytes(12);
            if (!looksLikeImage(header)) {
                throw new BusinessException("INVALID_FILE", "Le contenu du fichier n'est pas une image valide.");
            }
        } catch (IOException e) {
            throw new UncheckedIOException(e);
        }
        String filename = UUID.randomUUID() + extension;
        try (InputStream in = file.getInputStream()) {
            Files.copy(in, root.resolve(filename), StandardCopyOption.REPLACE_EXISTING);
        } catch (IOException e) {
            throw new UncheckedIOException(e);
        }
        return PUBLIC_PREFIX + filename;
    }

    public static boolean isManagedUrl(String url) {
        return url != null && (url.startsWith(PUBLIC_PREFIX) || url.startsWith("https://"));
    }

    /** Vérification des « magic numbers » : on ne se fie pas au Content-Type déclaré par le client. */
    static boolean looksLikeImage(byte[] h) {
        if (h.length >= 3 && (h[0] & 0xFF) == 0xFF && (h[1] & 0xFF) == 0xD8 && (h[2] & 0xFF) == 0xFF) return true; // JPEG
        if (h.length >= 8 && (h[0] & 0xFF) == 0x89 && h[1] == 'P' && h[2] == 'N' && h[3] == 'G') return true;       // PNG
        return h.length >= 12 && h[0] == 'R' && h[1] == 'I' && h[2] == 'F' && h[3] == 'F'
                && h[8] == 'W' && h[9] == 'E' && h[10] == 'B' && h[11] == 'P';                                     // WEBP
    }
}
