package tn.esprit.utils;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class MyDatabase {
    private final String URL = "jdbc:mysql://localhost:3306/pidev";

    private final String USERNAME = "root";

    private final String PWD = "";

    private Connection con;

    private static MyDatabase instance ;

    public MyDatabase() {
        try {
            con = DriverManager.getConnection(URL,USERNAME,PWD);
            System.out.println("Connected to database");
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
    }
    public static MyDatabase getInstance() {
        if (instance == null)
            instance = new MyDatabase();
        return instance;
    }

    public Connection getCon() {
        return con;
    }
}
