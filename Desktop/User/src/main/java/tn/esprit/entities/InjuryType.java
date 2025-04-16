package tn.esprit.entities;

public enum InjuryType{
    SPRAIN,
    FRACTURE,
    CONCUSSION,
    BRUISE;
    public static InjuryType fromString(String value) {
        for (InjuryType injuryType : InjuryType.values()) {
            if (injuryType.name().equalsIgnoreCase(value)) {
                return injuryType;
            }
        }
        throw new IllegalArgumentException("Unknown value: " + value);
    }
}
