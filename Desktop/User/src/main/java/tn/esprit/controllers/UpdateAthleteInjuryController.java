package tn.esprit.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import tn.esprit.entities.Injury;
import tn.esprit.entities.InjuryType;
import tn.esprit.entities.Severity;
import tn.esprit.entities.user;
import tn.esprit.services.InjuryServices;
import tn.esprit.services.UserServices;

import java.io.IOException;
import java.time.LocalDate;
import java.util.List;

public class UpdateAthleteInjuryController {

    @FXML
    private ChoiceBox<String> AthleteNameTypeChoiceBox;

    @FXML
    private ChoiceBox<String> AthleteLastNameTypeChoiceBox;

    @FXML
    private TextField injuryDescriptionField;

    @FXML
    private DatePicker injuryDatePicker;

    @FXML
    private ChoiceBox<Severity> severityChoiceBox;

    @FXML
    private ChoiceBox<InjuryType> injuryTypeChoiceBox;

    @FXML
    private Button UpdateInjuryButton;

    @FXML
    private Button viewInjuryButton;

    private Injury selectedInjury;
    private final InjuryServices injuryServices = new InjuryServices();
    private final UserServices userServices = new UserServices();

    public void initData(Injury injury) {
        this.selectedInjury = injury;

        // Populate choice boxes
        loadAthletes();
        loadInjuryTypes();
        loadSeverities();

        // Set values of fields based on the injury object
        injuryTypeChoiceBox.setValue(injury.getInjuryType());
        injuryDatePicker.setValue(injury.getInjuryDate());
        severityChoiceBox.setValue(injury.getInjury_severity());
        injuryDescriptionField.setText(injury.getInjury_description());

        // Set selected athlete's first name and last name
        user athlete = injury.getUser(); // Get the user object directly
        if (athlete != null) {
            AthleteNameTypeChoiceBox.setValue(athlete.getUser_fname());
            loadLastNames(athlete.getUser_fname());
            AthleteLastNameTypeChoiceBox.setValue(athlete.getUser_lname());
        }

        // Add event handlers
        UpdateInjuryButton.setOnAction(event -> updateInjuryAction());
        viewInjuryButton.setOnAction(event -> navigateToListInjury());
    }


    private void loadAthletes() {
        List<user> athletes = userServices.getUsersByRole("Athlete");
        ObservableList<String> athleteNames = FXCollections.observableArrayList();

        for (user athlete : athletes) {
            athleteNames.add(athlete.getUser_fname());
        }

        AthleteNameTypeChoiceBox.setItems(athleteNames);
        AthleteNameTypeChoiceBox.getSelectionModel().selectedItemProperty().addListener((observable, oldValue, newValue) -> loadLastNames(newValue));
    }

    private void loadLastNames(String firstName) {
        List<user> athletes = userServices.getUsersByRole("Athlete");
        ObservableList<String> athleteLastNames = FXCollections.observableArrayList();

        for (user athlete : athletes) {
            if (athlete.getUser_fname().equals(firstName)) {
                athleteLastNames.add(athlete.getUser_lname());
            }
        }

        AthleteLastNameTypeChoiceBox.setItems(athleteLastNames);
    }

    private void loadInjuryTypes() {
        ObservableList<InjuryType> injuryTypes = FXCollections.observableArrayList(InjuryType.values());
        injuryTypeChoiceBox.setItems(injuryTypes);
    }

    private void loadSeverities() {
        ObservableList<Severity> severities = FXCollections.observableArrayList(Severity.values());
        severityChoiceBox.setItems(severities);
    }

    @FXML
    private void updateInjuryAction() {
        if (!validateInputs()) {
            return;
        }

        try {
            if (selectedInjury == null) {
                System.out.println("Error: No injury selected for update.");
                return;
            }

            // Set injury details from the form fields
            selectedInjury.setInjuryType(injuryTypeChoiceBox.getValue());
            selectedInjury.setInjuryDate(injuryDatePicker.getValue());
            selectedInjury.setInjury_severity(severityChoiceBox.getValue());
            selectedInjury.setInjury_description(injuryDescriptionField.getText());

            // Attempt to update the injury in the database
            injuryServices.update(selectedInjury.getInjury_id(), selectedInjury);

            // Show success alert
            showSuccessAlert("Update Successful", "The injury has been successfully updated!");

            // Navigate back to ListInjury.fxml
            navigateToListInjury();

        } catch (Exception e) {
            System.out.println("An error occurred while updating the injury: " + e.getMessage());
        }
    }

    private void navigateToListInjury() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListInjury.fxml"));
            Stage stage = (Stage) viewInjuryButton.getScene().getWindow();
            stage.setScene(new Scene(loader.load()));
            stage.show();
        } catch (IOException e) {
            System.out.println("Error loading ListInjury.fxml: " + e.getMessage());
        }
    }

    private boolean validateInputs() {
        if (injuryTypeChoiceBox.getValue() == null) {
            showAlert("Validation Error", "Please select an injury type.");
            return false;
        }

        LocalDate injuryDate = injuryDatePicker.getValue();
        if (injuryDate == null || injuryDate.isAfter(LocalDate.now())) {
            showAlert("Validation Error", "Injury date must not be in the future.");
            return false;
        }

        if (severityChoiceBox.getValue() == null) {
            showAlert("Validation Error", "Please select the injury severity.");
            return false;
        }

        String injuryDescription = injuryDescriptionField.getText().trim();
        if (injuryDescription.isEmpty()) {
            showAlert("Validation Error", "Please enter an injury description.");
            return false;
        }

        return true;
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void showSuccessAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
