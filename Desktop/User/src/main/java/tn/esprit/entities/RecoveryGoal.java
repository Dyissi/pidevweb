package tn.esprit.entities;

public enum RecoveryGoal {
    REHABILITATION,
    PREVENTION,
    PERFORMANCE_OPTIMIZATION,
    PAIN_MANAGEMENT,
    STRENGTHENING,
    MOBILITY_RESTORATION,
    ENDURANCE_BUILDING,
    FUNCTIONAL_RECOVERY,
    PSYCHOSOCIAL_RECOVERY;

    public static RecoveryGoal fromString(String value) {
        for (RecoveryGoal recoveryGoal : RecoveryGoal.values()) {
            if (recoveryGoal.name().equalsIgnoreCase(value)) {
                return recoveryGoal;
            }
        }
        throw new IllegalArgumentException("Unknown value: " + value);
    }
}
