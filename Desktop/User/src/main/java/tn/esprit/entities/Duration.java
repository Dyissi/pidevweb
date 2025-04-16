package tn.esprit.entities;

public enum Duration {
    FORTY_FIVE("45"),
    SIXTY("60"),
    NINETY("90"),
    ONE_TWENTY("120");
    private final String minutes;

    Duration(String minutes) {
        this.minutes = minutes;
    }

    public String getMinutes() {
        return minutes;
    }

    public static Duration fromString(String value) {
        for (Duration duration : Duration.values()) {
            if (duration.getMinutes().equals(value)) {
                return duration;
            }
        }
        throw new IllegalArgumentException("Unknown value: " + value);
    }
}