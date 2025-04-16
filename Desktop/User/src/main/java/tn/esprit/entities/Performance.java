package tn.esprit.entities;
import java.sql.Date;
import java.time.LocalDate;

public class Performance {
    private int performance_id;
    private float speed;
    private float agility;
    private int nbr_goals;
    private int assists ;
    private Date date_recorded;
    private int nbr_fouls ;

    public Performance(int performance_id, float speed, float agility, int nbr_goals, int assists, Date date_recorded, int nbr_fouls) {
        this.performance_id = performance_id;
        this.speed = speed;
        this.agility = agility;
        this.nbr_goals = nbr_goals;
        this.assists = assists;
        this.date_recorded = date_recorded;
        this.nbr_fouls = nbr_fouls;
    }

    public Performance(float speed, float agility, int nbr_goals, int assists, Date date_recorded, int nbr_fouls) {
        this.speed = speed;
        this.agility = agility;
        this.nbr_goals = nbr_goals;
        this.assists = assists;
        this.date_recorded = date_recorded;
        this.nbr_fouls = nbr_fouls;

    }

    public Performance(float speed, float agility, int goals, int assists, LocalDate date, int fouls) {
    }

    public int getPerformance_id() {
        return performance_id;
    }

    public void setPerformance_id(int performance_id) {
        this.performance_id = performance_id;
    }



    public float getSpeed() {
        return speed;
    }

    public void setSpeed(float speed) {
        this.speed = speed;
    }


    public float getAgility() {
        return agility;
    }

    public void setAgility(float agility) {
        this.agility = agility;
    }

    public int getNbr_goals() {
        return nbr_goals;
    }

    public void setNbr_goals(int nbr_goals) {
        this.nbr_goals = nbr_goals;
    }

    public int getAssists() {
        return assists;
    }

    public void setAssists(int assists) {
        this.assists = assists;
    }

    public Date getDate_recorded() {
        return date_recorded;
    }

    public void setDate_recorded(Date date_recorded) {
        this.date_recorded = date_recorded;
    }

    public int getNbr_fouls() {
        return nbr_fouls;
    }

    public void setNbr_fouls(int nbr_fouls) {
        this.nbr_fouls = nbr_fouls;
    }

    @Override
    public String toString() {
        return "performance{" +
                "performance_id=" + performance_id +
                ", speed=" + speed +
                ", agility=" + agility +
                ", nbr_goals=" + nbr_goals +
                ", assists=" + assists +
                ", date_recorded=" + date_recorded +
                ", nbr_fouls=" + nbr_fouls +
                '}';
    }


}
