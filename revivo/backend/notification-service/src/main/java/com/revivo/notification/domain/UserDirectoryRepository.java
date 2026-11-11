package com.revivo.notification.domain;

import org.springframework.data.jpa.repository.JpaRepository;

public interface UserDirectoryRepository extends JpaRepository<UserDirectoryEntry, Long> {
}
