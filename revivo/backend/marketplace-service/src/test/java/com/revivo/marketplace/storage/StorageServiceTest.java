package com.revivo.marketplace.storage;

import org.junit.jupiter.api.Test;

import static org.assertj.core.api.Assertions.assertThat;

class StorageServiceTest {

    @Test
    void detectsImagesByMagicNumbers() {
        assertThat(StorageService.looksLikeImage(new byte[]{(byte) 0xFF, (byte) 0xD8, (byte) 0xFF, 0})).isTrue();
        assertThat(StorageService.looksLikeImage(new byte[]{(byte) 0x89, 'P', 'N', 'G', 0, 0, 0, 0})).isTrue();
        assertThat(StorageService.looksLikeImage("RIFF____WEBP".getBytes())).isTrue();
        assertThat(StorageService.looksLikeImage("<?php echo 1;".getBytes())).isFalse();
    }

    @Test
    void onlyManagedUrlsAreAccepted() {
        assertThat(StorageService.isManagedUrl("/uploads/abc.jpg")).isTrue();
        assertThat(StorageService.isManagedUrl("javascript:alert(1)")).isFalse();
        assertThat(StorageService.isManagedUrl("http://evil.example/x.png")).isFalse();
    }
}
