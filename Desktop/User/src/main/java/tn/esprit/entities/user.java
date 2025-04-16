package tn.esprit.entities;

import java.util.Date;

public class user {
    private int user_id;

    private String user_fname;
    private String user_lname;
    private String user_email;
    private String user_pwd;
    private String user_nbr;

    private user_role user_role;


    public enum user_role{
        ADMIN, COACH, MEDICAL_STAFF, ATHLETE
    }

    public user() {
    }

    public user(int user_id, String user_fname, String user_lname, String user_email, String user_pwd, String user_nbr, user.user_role user_role) {
        this.user_id = user_id;
        this.user_fname = user_fname;
        this.user_lname = user_lname;
        this.user_email = user_email;
        this.user_pwd = user_pwd;
        this.user_nbr = user_nbr;
        this.user_role = user_role;
    }

    public user(String user_fname, String user_lname, String user_email, String user_pwd, String user_nbr, user.user_role user_role) {
        this.user_fname = user_fname;
        this.user_lname = user_lname;
        this.user_email = user_email;
        this.user_pwd = user_pwd;
        this.user_nbr = user_nbr;
        this.user_role = user_role;
    }

    public int getUser_id() {
        return user_id;
    }

    public void setUser_id(int user_id) {
        this.user_id = user_id;
    }

    public String getUser_fname() {
        return user_fname;
    }

    public void setUser_fname(String user_fname) {
        this.user_fname = user_fname;
    }

    public String getUser_lname() {
        return user_lname;
    }

    public void setUser_lname(String user_lname) {
        this.user_lname = user_lname;
    }

    public String getUser_email() {
        return user_email;
    }

    public void setUser_email(String user_email) {
        this.user_email = user_email;
    }

    public String getUser_pwd() {
        return user_pwd;
    }

    public void setUser_pwd(String user_pwd) {
        this.user_pwd = user_pwd;
    }

    public String getUser_nbr() {
        return user_nbr;
    }

    public void setUser_nbr(String user_nbr) {
        this.user_nbr = user_nbr;
    }

    public user.user_role getUser_role() {
        return user_role;
    }

    public void setUser_role(user.user_role user_role) {
        this.user_role = user_role;
    }

    @Override
    public String toString() {
        return "user{" +
                "user_id=" + user_id +
                ", user_fname='" + user_fname + '\'' +
                ", user_lname='" + user_lname + '\'' +
                ", user_email='" + user_email + '\'' +
                ", user_pwd='" + user_pwd + '\'' +
                ", user_nbr='" + user_nbr + '\'' +
                ", user_role=" + user_role +
                '}';
    }
}

