package tn.esprit.entities;
import java.time.LocalDate;


public class Injury{
    private int injury_id;
    private InjuryType injuryType;
    private LocalDate injuryDate ;
    private Severity injury_severity;
    private String injury_description;
    private user user;

    public Injury(int injury_id, InjuryType injuryType, LocalDate injuryDate, Severity injury_severity, String injury_description, user user) {
        this.injury_id = injury_id;
        this.injuryType = injuryType;
        this.injuryDate = injuryDate;
        this.injury_severity = injury_severity;
        this.injury_description = injury_description;
        this.user = user;
    }

    public Injury(InjuryType injuryType, LocalDate injuryDate, Severity severity, String injury_description, user user) {
        this.injuryType = injuryType;
        this.injuryDate = injuryDate;
        this.injury_severity = severity;
        this.injury_description= injury_description;
        this.user = user;
    }

    public Injury() {
    }

    public int getInjury_id() {
        return injury_id;
    }

    public void setInjury_id(int injury_id) {
        this.injury_id = injury_id;
    }

    public InjuryType getInjuryType() {
        return injuryType;
    }

    public void setInjuryType(InjuryType injuryType) {
        this.injuryType = injuryType;
    }

    public LocalDate getInjuryDate() {
        return injuryDate;
    }

    public void setInjuryDate(LocalDate injuryDate) {
        this.injuryDate = injuryDate;
    }

    public Severity getInjury_severity() {
        return injury_severity;
    }

    public void setInjury_severity(Severity injury_severity) {
        this.injury_severity = injury_severity;
    }

    public String getInjury_description() {
        return injury_description;
    }

    public void setInjury_description(String injury_description) {
        this.injury_description = injury_description;
    }

    public user getUser() {
        return user;
    }

    public void setUser(user user) {
        this.user = user;
    }

    @Override
    public String toString() {
        return "Injury{" +
                "injury_id=" + injury_id +
                ", injuryType=" + injuryType +
                ", injuryDate=" + injuryDate +
                ", injury_severity=" + injury_severity +
                ", injury_description='" + injury_description + '\'' +
                ", user='" + user + '\'' +
                '}';
    }
}
