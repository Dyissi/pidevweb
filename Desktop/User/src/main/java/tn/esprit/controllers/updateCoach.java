package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.ComboBox;
import javafx.scene.control.TextField;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import tn.esprit.entities.Coach;
import tn.esprit.entities.user;
import tn.esprit.services.UserServices;


public class updateCoach {

    @FXML
    private Button addUserBtn;

    @FXML
    private TextField email_id;

    @FXML
    private TextField fname_id;

    @FXML
    private TextField lname_id;

    @FXML
    private TextField nbteams_id;

    @FXML
    private TextField password_id;

    @FXML
    private TextField phone_nb_id;

    @FXML
    private TextField role_id1;

    private Coach selectedCoach;
    private final UserServices userServices = new UserServices();

    public void initData(Coach c) {
        addUserBtn.setOnAction(event -> updateCoachAction());
        this.selectedCoach = c;
        fname_id.setText(c.getUser_fname());
        lname_id.setText(c.getUser_lname());
        email_id.setText(c.getUser_email());
        password_id.setText(c.getUser_pwd());
        phone_nb_id.setText(c.getUser_nbr());
        nbteams_id.setText(String.valueOf(c.getNb_teams()));

    }
    @FXML
    private void updateCoachAction() {
        if (!validateInputs()) {
            return;
        }
        selectedCoach.setUser_fname(fname_id.getText());
        selectedCoach.setUser_lname(lname_id.getText());
        selectedCoach.setUser_email(email_id.getText());
        selectedCoach.setUser_pwd(password_id.getText());
        selectedCoach.setUser_nbr(phone_nb_id.getText());
        selectedCoach.setNb_teams(Integer.parseInt(nbteams_id.getText()));
        selectedCoach.setUser_role(user.user_role.COACH);

        userServices.update(selectedCoach.getUser_id(), selectedCoach);
        System.out.println("Coach updated successfully!");

        // Close the update window
        addUserBtn.getScene().getWindow().hide();

    }



    private void showAlert(String title, String message) {
        Alert alert = new Alert(AlertType.ERROR);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private boolean validateInputs() {
        String fname = fname_id.getText().trim();
        String lname = lname_id.getText().trim();
        String email = email_id.getText().trim();
        String password = password_id.getText().trim();
        String phoneNumber = phone_nb_id.getText().trim();

        // Validate first name
        if (fname.isEmpty() || !fname.matches("[a-zA-Z]+")) {
            showAlert("Validation Error", "First name should only contain letters (no numbers or special characters).");
            return false;
        }

        // Validate last name
        if (lname.isEmpty() || !lname.matches("[a-zA-Z]+")) {
            showAlert("Validation Error", "Last name should only contain letters (no numbers or special characters).");
            return false;
        }

        // Validate email
        if (!email.contains("@") || !email.contains(".") || email.startsWith("@") || email.endsWith(".")) {
            showAlert("Validation Error", "Invalid email format. Example: example@domain.com");
            return false;
        }


        // Validate phone number (must start with + and have at least 8 digits)
        if (!phoneNumber.startsWith("+") || phoneNumber.length() < 9 || !phoneNumber.substring(1).matches("\\d+")) {
            showAlert("Validation Error", "Phone number must start with '+' and contain at least 8 digits.");
            return false;
        }

        return true;
    }
}