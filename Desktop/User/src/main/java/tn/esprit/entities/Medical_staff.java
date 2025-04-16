package tn.esprit.entities;

public class Medical_staff extends user {
    private String speciality;
    public Medical_staff() {
        super();
        this.speciality = "";
    }
    public Medical_staff(int user_id, String user_fname, String user_lname, String user_email, String user_pwd, String user_nbr, String speciality) {
        super(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr, user_role.MEDICAL_STAFF);
        this.speciality = speciality;
    }
    public Medical_staff(String user_fname, String user_lname, String user_email, String user_pwd, String user_nbr, String speciality) {
        super(user_fname, user_lname, user_email, user_pwd, user_nbr, user_role.MEDICAL_STAFF);
        this.speciality = speciality;
    }
    public String getSpeciality() {
        return speciality;
    }
    public void setSpeciality(String speciality) {
        this.speciality = speciality;
    }
    @Override
    public String toString() {
        return super.toString() + "Medical_staff{" + "speciality=" + speciality + '}';
    }

}
