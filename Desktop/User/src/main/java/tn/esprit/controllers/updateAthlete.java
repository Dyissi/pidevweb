package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.*;

import java.sql.Date;
import java.time.LocalDate;

import javafx.scene.control.Alert.AlertType;
import tn.esprit.entities.Athlete;
import tn.esprit.entities.Coach;
import tn.esprit.entities.user;
import tn.esprit.services.UserServices;

public class updateAthlete {
    @FXML
    private TextField Adress_id;

    @FXML
    private DatePicker DoB_id;

    @FXML
    private TextField Height_id;

    @FXML
    private TextField Weight_id;

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

    @FXML
    private TextField role_id1;

    @FXML
    private Button updateAthleteBtn;
    private Athlete selectedAthlete;
    private final UserServices userServices = new UserServices();

    public void initData(Athlete a) {
        updateAthleteBtn.setOnAction(event -> updateAthleteAction());
        this.selectedAthlete = a;
        fname_id.setText(a.getUser_fname());
        lname_id.setText(a.getUser_lname());
        email_id.setText(a.getUser_email());
        password_id.setText(a.getUser_pwd());
        phone_nb_id.setText(a.getUser_nbr());
        DoB_id.setValue(a.getAthlete_DoB().toLocalDate());
        Height_id.setText(String.valueOf(a.getAthlete_height()));
        Weight_id.setText(String.valueOf(a.getAthlete_weight()));
        gender_id.setValue(a.getAthlete_gender());
        Adress_id.setText(a.getAthlete_address());
    }
    @FXML
    private void updateAthleteAction() {
        if (!validateInputs()) {
            return;
        }
        try {
            // Ensure selectedAthlete is not null
            if (selectedAthlete == null) {
                System.out.println("Error: No athlete selected for update.");
                return;
            }

            // Validate input fields
            if (fname_id.getText().isEmpty() || lname_id.getText().isEmpty() ||
                    email_id.getText().isEmpty() || password_id.getText().isEmpty() ||
                    phone_nb_id.getText().isEmpty() || DoB_id.getValue() == null ||
                    Height_id.getText().isEmpty() || Weight_id.getText().isEmpty() ||
                    gender_id.getValue() == null || Adress_id.getText().isEmpty()) {

                System.out.println("Error: Please fill in all required fields.");
                return;
            }

            // Set athlete details
            selectedAthlete.setUser_fname(fname_id.getText());
            selectedAthlete.setUser_lname(lname_id.getText());
            selectedAthlete.setUser_email(email_id.getText());
            selectedAthlete.setUser_pwd(password_id.getText());
            selectedAthlete.setUser_nbr(phone_nb_id.getText());
            selectedAthlete.setUser_role(user.user_role.ATHLETE);
            selectedAthlete.setAthlete_DoB(java.sql.Date.valueOf(DoB_id.getValue()));
            selectedAthlete.setAthlete_height(Float.parseFloat(Height_id.getText()));
            selectedAthlete.setAthlete_weight(Float.parseFloat(Weight_id.getText()));
            selectedAthlete.setAthlete_gender(gender_id.getValue());
            selectedAthlete.setAthlete_address(Adress_id.getText());

            // Attempt to update the athlete in the database
            userServices.update(selectedAthlete.getUser_id(), selectedAthlete);


        } catch (NumberFormatException e) {
            System.out.println("Error: Invalid number format for height or weight.");
        } catch (Exception e) {
            System.out.println("An unexpected error occurred: " + e.getMessage());
        }
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