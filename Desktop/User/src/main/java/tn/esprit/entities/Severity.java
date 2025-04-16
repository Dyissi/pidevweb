package tn.esprit.entities;

public enum Severity{
    MILD,
    MODERATE,
    SEVERE,
    CRITICAL;
    public static Severity fromString(String value) {
        for (Severity severity : Severity.values()) {
            if (severity.name().equalsIgnoreCase(value)) {
                return severity;
            }
        }
        throw new IllegalArgumentException("Unknown value: " + value);
    }
}
