package com.revivo.marketplace.item;

public enum ItemCondition {
    NEW_WITH_TAGS("Neuf avec étiquette"),
    NEW_WITHOUT_TAGS("Neuf sans étiquette"),
    VERY_GOOD("Très bon état"),
    GOOD("Bon état"),
    SATISFACTORY("Satisfaisant");

    private final String label;

    ItemCondition(String label) {
        this.label = label;
    }

    public String label() {
        return label;
    }
}
