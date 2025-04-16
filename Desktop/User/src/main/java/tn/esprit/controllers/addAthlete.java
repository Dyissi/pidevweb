package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import tn.esprit.entities.Coach;
import tn.esprit.services.UserServices;
import tn.esprit.entities.Athlete;


import java.io.IOException;
import java.sql.SQLException;
import java.sql.Date;
import java.time.LocalDate;
import java.util.regex.Pattern;


public class addAthlete {

    @FXML
    private TextField Adress_id;

    @FXML
    private DatePicker DoB_id;

    @FXML
    private TextField Height_id;

    @FXML
    private TextField Weight_id;

    @FXML
    private Button addAthleteBtn;

    @FXML
    private TextField email_id;

    @FXML
    private TextField fname_id;

    @FXML
    private ComboBox<String> gender_id;

    @FXML
    private TextField lname_id;

    @FXML
    private TextField password_id;

    @FXML
    private TextField phone_nb_id;
    @FXML private Label toListA;
    @FXML
    private Label backBtn;


    @FXML
    private TextField role_id1;
    private final UserServices userService = new UserServices(); // Initialize UserServices

    @FXML
    public void initialize() {
        // Attach event listener to the button
        addAthleteBtn.setOnAction(event -> addUserAction());
        toListA.setOnMouseClicked(event -> switchScreenToAthleteList());
        backBtn.setOnMouseClicked(event -> switchBackToCoachFront());

    }
    @FXML
    private void addUserAction() {
        if (!validateInputs()) return;
        String fname = fname_id.getText();
        String lname = lname_id.getText();
        String email = email_id.getText();
        String password = password_id.getText();
        String phoneNumber = phone_nb_id.getText();
        String role = "athlete";
        LocalDate athlete_DoB=DoB_id.getValue() ;
        String athlete_gender = gender_id.getValue();
        String athlete_address = Adress_id.getText();
        float athlete_height = Float.parseFloat(Height_id.getText());
        float athlete_weight = Float.parseFloat(Weight_id.getText());
        Athlete newAthlete = new Athlete(fname, lname, email, password, phoneNumber, Date.valueOf(athlete_DoB), athlete_gender, athlete_address, athlete_height, athlete_weight, 0);

        try {
            userService.add(newAthlete);
            showAlert("Success", "Athlete added successfully!");
            clearFields();
        } catch (SQLException e) {
            showAlert("Database Error", "Error adding Athlete: " + e.getMessage());
        }
    }

    private void switchBackToCoachFront() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/Coachfront.fxml"));
            Parent root = loader.load();

            Coachfront controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Consult Team");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) backBtn.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
        }
    }

    private void clearFields() {
        fname_id.clear();
        lname_id.clear();
        email_id.clear();
        password_id.clear();
        phone_nb_id.clear();
        Adress_id.clear();
        Height_id.clear();
        Weight_id.clear();
        DoB_id.getEditor().clear();
        gender_id.getSelectionModel().clearSelection();

    }
    private void switchScreenToAthleteList() {


        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListAthlete.fxml"));
            Parent root = loader.load();

            ListAthlete controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Adding Tournament");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) toListA.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open tournament screen: " + e.getMessage());
        }
    }

    private void showAlert(String title, String content) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }
    private boolean validateInputs() {
        String fname = fname_id.getText().trim();
        String lname = lname_id.getText().trim();
        String email = email_id.getText().trim();
        String password = password_id.getText().trim();
        String phoneNumber = phone_nb_id.getText().trim();
        String height = Height_id.getText().trim();
        String weight = Weight_id.getText().trim();
        String address = Adress_id.getText().trim();
        String gender = gender_id.getValue();
        LocalDate dob = DoB_id.getValue();

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

        // Validate date of birth (must not be in the future)
        if (dob == null) {
            showAlert("Validation Error", "Please enter a valid date of birth.");
            return false;
        }
        if (dob.isAfter(LocalDate.now())) {
            showAlert("Validation Error", "Date of birth cannot be in the future.");
            return false;
        }
        // Validate gender (must not be empty)
        if (gender == null || gender.trim().isEmpty()) {
            showAlert("Validation Error", "Gender must be selected.");
            return false;
        }

        // Validate address (must not be empty and should not contain special characters)
        if (address.isEmpty() || !address.matches("[a-zA-Z0-9 ]+")) {
            showAlert("Validation Error", "Address must not be empty and should not contain special characters.");
            return false;
        }
        // Validate height (must be a float or integer)
        try {
            float heightValue = Float.parseFloat(height);
            if (heightValue <= 0) {
                showAlert("Validation Error", "Height must be a positive number.");
                return false;
            }
        } catch (NumberFormatException e) {
            showAlert("Validation Error", "Height must be a valid number (integer or decimal).");
            return false;
        }

        // Validate weight (must be a float or integer)
        try {
            float weightValue = Float.parseFloat(weight);
            if (weightValue <= 0) {
                showAlert("Validation Error", "Weight must be a positive number.");
                return false;
            }
        } catch (NumberFormatException e) {
            showAlert("Validation Error", "Weight must be a valid number (integer or decimal).");
            return false;
        }





        return true;
    }

}