package tn.esprit.entities;
import java.time.LocalDate;

public class RecoveryPlan {
    private int recovery_id;
    private Injury injury;
    private user user;
    private RecoveryGoal recovery_Goal;
    private String recovery_Description;
    private LocalDate recovery_StartDate;
    private LocalDate recovery_EndDate;
    private RecoveryStatus recovery_Status;

    public RecoveryPlan(int recovery_id, Injury injury, user user, RecoveryGoal recovery_Goal,
                        String recovery_Description, LocalDate recovery_StartDate,
                        LocalDate recovery_EndDate, RecoveryStatus recovery_Status) {
        this.recovery_id = recovery_id;
        this.injury = injury;
        this.user = user;
        this.recovery_Goal = recovery_Goal;
        this.recovery_Description = recovery_Description;
        this.recovery_StartDate = recovery_StartDate;
        this.recovery_EndDate = recovery_EndDate;
        this.recovery_Status = recovery_Status;
    }

    public RecoveryPlan(Injury injury, user user, RecoveryGoal recovery_Goal,
                        String recovery_Description, LocalDate recovery_StartDate,
                        LocalDate recovery_EndDate, RecoveryStatus recovery_Status) {
        this.injury = injury;
        this.user = user;
        this.recovery_Goal = recovery_Goal;
        this.recovery_Description = recovery_Description;
        this.recovery_StartDate = recovery_StartDate;
        this.recovery_EndDate = recovery_EndDate;
        this.recovery_Status = recovery_Status;
    }

    public RecoveryPlan() {
    }

    public int getRecovery_id() {
        return recovery_id;
    }

    public void setRecovery_id(int recovery_id) {
        this.recovery_id = recovery_id;
    }

    public Injury getInjury() {
        return injury;
    }

    public void setInjury(Injury injury) {
        this.injury = injury;
    }

    public user getUser() {
        return user;
    }

    public void setUser(user user) {
        this.user = user;
    }

    public RecoveryGoal getRecovery_Goal() {
        return recovery_Goal;
    }

    public void setRecovery_Goal(RecoveryGoal recovery_Goal) {
        this.recovery_Goal = recovery_Goal;
    }

    public String getRecovery_Description() {
        return recovery_Description;
    }

    public void setRecovery_Description(String recovery_Description) {
        this.recovery_Description = recovery_Description;
    }

    public LocalDate getRecovery_StartDate() {
        return recovery_StartDate;
    }

    public void setRecovery_StartDate(LocalDate recovery_StartDate) {
        this.recovery_StartDate = recovery_StartDate;
    }

    public LocalDate getRecovery_EndDate() {
        return recovery_EndDate;
    }

    public void setRecovery_EndDate(LocalDate recovery_EndDate) {
        this.recovery_EndDate = recovery_EndDate;
    }

    public RecoveryStatus getRecovery_Status() {
        return recovery_Status;
    }

    public void setRecovery_Status(RecoveryStatus recovery_Status) {
        this.recovery_Status = recovery_Status;
    }

    @Override
    public String toString() {
        return "RecoveryPlan{" +
                "recovery_id=" + recovery_id +
                ", injury=" + injury +
                ", user=" + user +
                ", recovery_Goal=" + recovery_Goal +
                ", recovery_Description='" + recovery_Description + '\'' +
                ", recovery_StartDate=" + recovery_StartDate +
                ", recovery_EndDate=" + recovery_EndDate +
                ", recovery_Status=" + recovery_Status +
                '}';
    }
}
