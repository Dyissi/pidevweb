package tn.esprit.entities;

public enum Focus {
    AGILITY,
    STRENGTH,
    DRIBBLING,
    ENDURANCE,
    SPRINT,
    SPEED,
    PLYOMETRICS,
    TEAMWORK;
    public static Focus fromString(String value) {
        for (Focus focus : Focus.values()) {
            if (focus.name().equalsIgnoreCase(value)) {
                return focus;
            }
        }
        throw new IllegalArgumentException("Unknown value: " + value);
    }
}
