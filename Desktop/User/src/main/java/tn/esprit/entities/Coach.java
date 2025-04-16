package tn.esprit.entities;

public class Coach extends user {
    private int nb_teams;
    public Coach() {
        super();
        this.nb_teams = 0;
    }
    public Coach(int user_id, String user_fname, String user_lname, String user_email, String user_pwd, String user_nbr, int nb_teams) {
        super(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr, user_role.COACH);
        this.nb_teams = 0;
    }
    public Coach(String user_fname, String user_lname, String user_email, String user_pwd, String user_nbr, int nb_teams) {
        super(user_fname, user_lname, user_email, user_pwd, user_nbr, user_role.COACH);
        this.nb_teams = 0;
    }
    public int getNb_teams() {
        return nb_teams;
    }
    public void setNb_teams(int nb_teams) {
        this.nb_teams = nb_teams;
    }
    @Override
    public String toString() {
        return super.toString() + ", nb_teams=" + nb_teams + "}";
    }
}
