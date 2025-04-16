package tn.esprit.main;

import tn.esprit.entities.Athlete;
import tn.esprit.entities.Coach;
import tn.esprit.entities.Medical_staff;
import tn.esprit.entities.user;
import tn.esprit.services.UserServices;
import tn.esprit.utils.MyDatabase;
import java.text.SimpleDateFormat;

import java.sql.SQLException;
import java.time.LocalDate;
import java.util.List;

public class Main {
    public static void main(String[] args) {
        MyDatabase db1 =MyDatabase.getInstance();
        UserServices us = new UserServices();
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd");
        //add
        /*Coach coach = new Coach( "asma",  "drissi",  "asmadrissi@gmail.com",  "katousksir",  "97749017",  5);
        try {
            us.add(coach);
        } catch (SQLException e) {
            System.out.println(e.getMessage());;
        }
        System.out.println(db1);
        //singleton? to restrict instance of connection to just one time
        Athlete athlete = new Athlete( "jouja",  "athlita",  "joujaathlita@gmail.japan",  "emochoucha123",  "1234567", new java.sql.Date(90,1,23),  "microplastik",  "bahdhahind",  0.25f,  1f, 1, new java.sql.Date(90,1,23));
        try {
            us.add(athlete);
        } catch (SQLException e) {
            System.out.println(e.getMessage());;
        }
        Medical_staff medical_staff = new Medical_staff("noura",  "amouri",  "rrrrrrr@gmail.com",  "nourdin",  "3310",  "orthopediste");
        try {
            us.add(medical_staff);
        } catch (SQLException e) {
            System.out.println(e.getMessage());;
        }
        //us.delete(7);
        //list
        System.out.println("All Users:");
        for (user user : us.getAll()) {
            System.out.println(user);
        }
        //update
        Coach updatedCoach = new Coach("ons",  "drissi",  "asmadrissi@gmail.com",  "katousksir",  "97749017",  5);
        us.update(3, updatedCoach);
        System.out.println("User updated!");*/


    }



}
