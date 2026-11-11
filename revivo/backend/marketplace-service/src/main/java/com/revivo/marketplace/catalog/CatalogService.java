package com.revivo.marketplace.catalog;

import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.ArrayList;
import java.util.Comparator;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

@Service
@Transactional(readOnly = true)
public class CatalogService {

    public record CategoryNode(Long id, String name, String slug, List<CategoryNode> children) {
    }

    public record BrandDto(Long id, String name) {
    }

    private final CategoryRepository categories;
    private final BrandRepository brands;

    public CatalogService(CategoryRepository categories, BrandRepository brands) {
        this.categories = categories;
        this.brands = brands;
    }

    /** Arbre des catégories (2 niveaux). */
    public List<CategoryNode> categoryTree() {
        List<Category> all = categories.findAll();
        all.sort(Comparator.comparing(Category::getId));
        Map<Long, CategoryNode> roots = new LinkedHashMap<>();
        for (Category c : all) {
            if (c.getParent() == null) {
                roots.put(c.getId(), new CategoryNode(c.getId(), c.getName(), c.getSlug(), new ArrayList<>()));
            }
        }
        for (Category c : all) {
            if (c.getParent() != null && roots.containsKey(c.getParent().getId())) {
                roots.get(c.getParent().getId()).children()
                        .add(new CategoryNode(c.getId(), c.getName(), c.getSlug(), List.of()));
            }
        }
        return List.copyOf(roots.values());
    }

    /** Une catégorie et ses sous-catégories (pour filtrer « Femmes » = toutes les sous-catégories). */
    public List<Long> withDescendants(Long categoryId) {
        List<Long> ids = new ArrayList<>();
        ids.add(categoryId);
        ids.addAll(categories.findChildIds(categoryId));
        return ids;
    }

    public List<BrandDto> brands() {
        return brands.findAllByOrderByNameAsc().stream().map(b -> new BrandDto(b.getId(), b.getName())).toList();
    }
}
