package com.revivo.marketplace.item;

import java.math.BigDecimal;
import java.util.List;

/**
 * Critères de recherche (liés automatiquement depuis les query params).
 *
 * @param sort {@code newest} (défaut), {@code price_asc}, {@code price_desc}, {@code popular}
 */
public record ItemSearchCriteria(String q, Long categoryId, List<Long> brandIds, List<String> sizes,
                                 List<String> colors, List<ItemCondition> conditions, BigDecimal minPrice,
                                 BigDecimal maxPrice, String sort) {
}
