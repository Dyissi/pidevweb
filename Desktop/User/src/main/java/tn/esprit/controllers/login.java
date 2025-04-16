package tn.esprit.controllers;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.scene.control.Label;
import javafx.stage.Stage;
import javafx.event.ActionEvent;
import tn.esprit.utils.MyDatabase;
import java.sql.Connection;

import java.sql.ResultSet;
import java.sql.Statement;


public class login {
    @FXML
    private Button cancel_id;

    @FXML
    private Button login_id;

    @FXML
    private PasswordField password_id;

    @FXML
    private TextField username_id;

    @FXML
    private Label error_id;

    public void login_idOnAction(ActionEvent e) {

        if (username_id.getText().isBlank() == false && password_id.getText().isBlank()==false) {
            //error_id.setText("Invalid username or password!");
            validatelogin();
        } else {
            error_id.setText("Please enter Email and password");
        }
    }

    public void cancel_idOnAction(ActionEvent e) {
        Stage stage = (Stage) cancel_id.getScene().getWindow();
        stage.close();
    }

    public void validatelogin() {
        MyDatabase connectNow = new MyDatabase();
        Connection connectDB = connectNow.getCon();

        String verifyLoginQuery = "SELECT user_role FROM user WHERE user_email = ? AND user_pwd = ?";

        try {
            java.sql.PreparedStatement preparedStatement = connectDB.prepareStatement(verifyLoginQuery);
            preparedStatement.setString(1, username_id.getText());
            preparedStatement.setString(2, password_id.getText());

            ResultSet queryResult = preparedStatement.executeQuery();

            if (queryResult.next()) { // If a record is found
                String userRole = queryResult.getString("user_role");
                error_id.setText("Login Successful");

                // Open the corresponding interface
                openUserInterface(userRole);

            } else {
                error_id.setText("Invalid username or password!");
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void openUserInterface(String role) {
        try {
            Stage stage = (Stage) login_id.getScene().getWindow(); // Get current stage
            FXMLLoader loader = new FXMLLoader();
            Scene scene = null;

            switch (role.toLowerCase()) {
                case "coach":
                    loader.setLocation(getClass().getResource("/Coachfront.fxml"));
                    scene = new Scene(loader.load());
                    break;
                case "medical_staff":
                    loader.setLocation(getClass().getResource("/Medicalfront.fxml"));
                    scene = new Scene(loader.load());
                    break;
                case "athlete":
                    loader.setLocation(getClass().getResource("/Athletefront.fxml"));
                    scene = new Scene(loader.load());
                    break;
                default:
                    error_id.setText("Unknown role. Contact Admin.");
                    return;
            }

            stage.setScene(scene);
            stage.show();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }



}
