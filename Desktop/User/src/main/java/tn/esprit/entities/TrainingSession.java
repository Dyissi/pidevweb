package tn.esprit.entities;
import java.time.LocalTime;

public class TrainingSession {
    private int trainingSession_id;
    private Focus focus;
    private  LocalTime start_time ;
    private Duration duration;
    private String location ;
    private  String session_notes;

    public TrainingSession(int trainingSession_id, Focus focus, LocalTime start_time, Duration duration, String location, String session_notes) {
        this.trainingSession_id = trainingSession_id;
        this.focus = focus;
        this.start_time = start_time;
        this.duration = duration;
        this.location = location;
        this.session_notes = session_notes;
    }

    public TrainingSession(Focus focus, LocalTime start_time, Duration duration, String location, String session_notes) {
        this.focus = focus;
        this.start_time = start_time;
        this.duration = duration;
        this.location = location;
        this.session_notes = session_notes;
    }

    public TrainingSession() {
    }

    public int getTrainingSession_id() {
        return trainingSession_id;
    }

    public void setTrainingSession_id(int trainingSession_id) {
        this.trainingSession_id = trainingSession_id;
    }

    public Focus getFocus() {
        return focus;
    }

    public void setFocus(Focus focus) {
        this.focus = focus;
    }

    public LocalTime getStart_time() {
        return start_time;
    }

    public void setStart_time(LocalTime start_time) {
        this.start_time = start_time;
    }

    public Duration getDuration() {
        return duration;
    }

    public void setDuration(Duration duration) {
        this.duration = duration;
    }

    public String getLocation() {
        return location;
    }

    public void setLocation(String location) {
        this.location = location;
    }

    public String getSession_notes() {
        return session_notes;
    }

    public void setSession_notes(String session_notes) {
        this.session_notes = session_notes;
    }

    @Override
    public String toString() {
        return "TrainingSession{" +
                "trainingSession_id=" + trainingSession_id +
                ", focus=" + focus +
                ", start_time=" + start_time +
                ", duration=" + duration +
                ", location='" + location + '\'' +
                ", session_notes='" + session_notes + '\'' +
                '}';
    }
}
