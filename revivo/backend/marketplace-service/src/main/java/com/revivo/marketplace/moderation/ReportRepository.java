package com.revivo.marketplace.moderation;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.EntityGraph;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ReportRepository extends JpaRepository<Report, Long> {

    @EntityGraph(attributePaths = {"reporter", "item", "reportedUser"})
    Page<Report> findByStatus(Report.Status status, Pageable pageable);

    long countByStatus(Report.Status status);
}
