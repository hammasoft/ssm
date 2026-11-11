package com.revivo.marketplace.catalog;

import com.revivo.marketplace.item.ItemCondition;
import com.revivo.marketplace.order.ShippingOption;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.Arrays;
import java.util.List;
import java.util.Map;

@Tag(name = "Catalogue")
@RestController
@RequestMapping("/api")
public class CatalogController {

    private static final List<String> SIZES = List.of("XXS", "XS", "S", "M", "L", "XL", "XXL",
            "34", "36", "38", "40", "42", "44", "Unique");
    private static final List<String> COLORS = List.of("Noir", "Blanc", "Gris", "Beige", "Marron", "Bleu",
            "Marine", "Vert", "Jaune", "Orange", "Rouge", "Rose", "Violet", "Multicolore");

    private final CatalogService catalogService;

    public CatalogController(CatalogService catalogService) {
        this.catalogService = catalogService;
    }

    @Operation(summary = "Arbre des catégories")
    @GetMapping("/categories")
    public List<CatalogService.CategoryNode> categories() {
        return catalogService.categoryTree();
    }

    @Operation(summary = "Liste des marques")
    @GetMapping("/brands")
    public List<CatalogService.BrandDto> brands() {
        return catalogService.brands();
    }

    @Operation(summary = "Référentiels (états, tailles, couleurs, livraisons)")
    @GetMapping("/meta")
    public Map<String, Object> meta() {
        return Map.of(
                "conditions", Arrays.stream(ItemCondition.values())
                        .map(c -> Map.of("code", c.name(), "label", c.label())).toList(),
                "sizes", SIZES,
                "colors", COLORS,
                "shippingOptions", Arrays.stream(ShippingOption.values())
                        .map(s -> Map.of("code", s.name(), "label", s.label())).toList());
    }
}
