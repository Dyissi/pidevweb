package tn.esprit.entities;

import java.sql.Date;
import java.time.LocalDate;

public class Athlete extends user {
    private float athlete_height;
    private float athlete_weight;
    private String athlete_gender;
    private String athlete_address;
    private int isInjured;
    private Date athlete_DoB;
    public Athlete() {
        super();
        this.athlete_height = 0;
        this.athlete_weight = 0;
        this.athlete_gender = "";
        this.athlete_address = "";
        this.isInjured = 0;
        this.athlete_DoB=null;
    }

    public Athlete(int user_id, String user_fname, String user_lname, String user_email, String user_pwd, String user_nbr, Date athlete_DoB, String athlete_gender, String athlete_address, float athlete_height, float athlete_weight,   int isInjured) {
        super(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr, user_role.ATHLETE);
        this.athlete_height = athlete_height;
        this.athlete_weight = athlete_weight;
        this.athlete_gender = athlete_gender;
        this.athlete_address = athlete_address;
        this.isInjured = isInjured;
        this.athlete_DoB = athlete_DoB;
    }
    public Athlete(String user_fname, String user_lname, String user_email, String user_pwd, String user_nbr, Date athlete_DoB, String athlete_gender, String athlete_address, float athlete_height, float athlete_weight,   int isInjured) {
        super(user_fname, user_lname, user_email, user_pwd, user_nbr, user_role.ATHLETE);
        this.athlete_height = athlete_height;
        this.athlete_weight = athlete_weight;
        this.athlete_gender = athlete_gender;
        this.athlete_address = athlete_address;
        this.isInjured = isInjured;
        this.athlete_DoB = athlete_DoB;
    }

    public float getAthlete_height() {
        return athlete_height;
    }

    public void setAthlete_height(float athlete_height) {
        this.athlete_height = athlete_height;
    }

    public float getAthlete_weight() {
        return athlete_weight;
    }

    public void setAthlete_weight(float athlete_weight) {
        this.athlete_weight = athlete_weight;
    }

    public String getAthlete_gender() {
        return athlete_gender;
    }

    public void setAthlete_gender(String athlete_gender) {
        this.athlete_gender = athlete_gender;
    }

    public String getAthlete_address() {
        return athlete_address;
    }

    public void setAthlete_address(String athlete_address) {
        this.athlete_address = athlete_address;
    }

    public int getIsInjured() {
        return isInjured;
    }

    public void setIsInjured(int isInjured) {
        this.isInjured = isInjured;
    }

    public Date getAthlete_DoB() {
        return athlete_DoB;
    }

    public void setAthlete_DoB(Date athlete_DoB) {
        this.athlete_DoB = athlete_DoB;
    }


    @Override
    public String toString() {
        return super.toString() + "Athlete{" +
                "athlete_height=" + athlete_height +
                ", athlete_weight=" + athlete_weight +
                ", athlete_gender='" + athlete_gender + '\'' +
                ", athlete_address='" + athlete_address + '\'' +
                ", isInjured=" + isInjured +
                ", athlete_DoB=" + athlete_DoB +
                '}';
    }
}

