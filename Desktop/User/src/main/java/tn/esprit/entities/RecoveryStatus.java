package tn.esprit.entities;

public enum RecoveryStatus {
    IN_PROGRESS,
    COMPLETED,
    PENDING;

    public static RecoveryStatus fromString(String value) {
        for (RecoveryStatus recoveryStatus : RecoveryStatus.values()) {
            if (recoveryStatus.name().equalsIgnoreCase(value)) {
                return recoveryStatus;
            }
        }
        throw new IllegalArgumentException("Unknown value: " + value);
    }
}
