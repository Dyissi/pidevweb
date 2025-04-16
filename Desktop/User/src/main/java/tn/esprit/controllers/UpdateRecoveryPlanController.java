package tn.esprit.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import tn.esprit.entities.RecoveryPlan;
import tn.esprit.entities.RecoveryGoal;
import tn.esprit.entities.RecoveryStatus;
import tn.esprit.entities.user;
import tn.esprit.services.RecoveryPlanServices;
import tn.esprit.services.UserServices;

import java.io.IOException;
import java.time.LocalDate;
import java.util.List;

public class UpdateRecoveryPlanController {

    @FXML
    private ChoiceBox<String> AthleteNameTypeChoiceBox;

    @FXML
    private ChoiceBox<String> AthleteLastNameTypeChoiceBox;

    @FXML
    private TextField recoveryDescriptionField;

    @FXML
    private DatePicker recoveryStartDatePicker;

    @FXML
    private DatePicker recoveryEndDatePicker;

    @FXML
    private ChoiceBox<RecoveryGoal> recoveryGoalChoiceBox;

    @FXML
    private ChoiceBox<RecoveryStatus> recoveryStatusChoiceBox;

    @FXML
    private Button UpdateRecoveryPlanButton;

    @FXML
    private Button viewRecoveryPlansButton;

    private RecoveryPlan selectedRecoveryPlan;
    private final RecoveryPlanServices recoveryPlanServices = new RecoveryPlanServices();
    private final UserServices userServices = new UserServices();

    public void initData(RecoveryPlan recoveryPlan) {
        this.selectedRecoveryPlan = recoveryPlan;

        // Populate choice boxes
        loadAthletes();
        loadRecoveryGoals();
        loadRecoveryStatuses();

        // Set values of fields based on the recovery plan object
        recoveryGoalChoiceBox.setValue(recoveryPlan.getRecovery_Goal());
        recoveryStartDatePicker.setValue(recoveryPlan.getRecovery_StartDate());
        recoveryEndDatePicker.setValue(recoveryPlan.getRecovery_EndDate());
        recoveryStatusChoiceBox.setValue(recoveryPlan.getRecovery_Status());
        recoveryDescriptionField.setText(recoveryPlan.getRecovery_Description());

        // Set selected athlete's first name and last name
        user athlete = recoveryPlan.getUser(); // Get the user object directly
        if (athlete != null) {
            AthleteNameTypeChoiceBox.setValue(athlete.getUser_fname());
            loadLastNames(athlete.getUser_fname());
            AthleteLastNameTypeChoiceBox.setValue(athlete.getUser_lname());
        }

        // Add event handlers
        UpdateRecoveryPlanButton.setOnAction(event -> updateRecoveryPlanAction());
        viewRecoveryPlansButton.setOnAction(event -> navigateToListRecoveryPlans());
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

    private void loadRecoveryGoals() {
        ObservableList<RecoveryGoal> recoveryGoals = FXCollections.observableArrayList(RecoveryGoal.values());
        recoveryGoalChoiceBox.setItems(recoveryGoals);
    }

    private void loadRecoveryStatuses() {
        ObservableList<RecoveryStatus> recoveryStatuses = FXCollections.observableArrayList(RecoveryStatus.values());
        recoveryStatusChoiceBox.setItems(recoveryStatuses);
    }

    @FXML
    private void updateRecoveryPlanAction() {
        if (!validateInputs()) {
            return;
        }

        try {
            if (selectedRecoveryPlan == null) {
                System.out.println("Error: No recovery plan selected for update.");
                return;
            }

            // Set recovery plan details from the form fields
            selectedRecoveryPlan.setRecovery_Goal(recoveryGoalChoiceBox.getValue());
            selectedRecoveryPlan.setRecovery_StartDate(recoveryStartDatePicker.getValue());
            selectedRecoveryPlan.setRecovery_EndDate(recoveryEndDatePicker.getValue());
            selectedRecoveryPlan.setRecovery_Status(recoveryStatusChoiceBox.getValue());
            selectedRecoveryPlan.setRecovery_Description(recoveryDescriptionField.getText());

            // Attempt to update the recovery plan in the database
            recoveryPlanServices.update(selectedRecoveryPlan.getRecovery_id(), selectedRecoveryPlan);

            // Show success alert
            showSuccessAlert("Update Successful", "The recovery plan has been successfully updated!");

            // Navigate back to ListRecoveryPlans.fxml
            navigateToListRecoveryPlans();

        } catch (Exception e) {
            System.out.println("An error occurred while updating the recovery plan: " + e.getMessage());
        }
    }

    private void navigateToListRecoveryPlans() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListRecoveryPlan.fxml"));
            Stage stage = (Stage) viewRecoveryPlansButton.getScene().getWindow();
            stage.setScene(new Scene(loader.load()));
            stage.show();
        } catch (IOException e) {
            System.out.println("Error loading ListRecoveryPlans.fxml: " + e.getMessage());
        }
    }

    private boolean validateInputs() {
        if (recoveryGoalChoiceBox.getValue() == null) {
            showAlert("Validation Error", "Please select a recovery goal.");
            return false;
        }

        LocalDate recoveryStartDate = recoveryStartDatePicker.getValue();
        if (recoveryStartDate == null || recoveryStartDate.isAfter(LocalDate.now())) {
            showAlert("Validation Error", "Start date must not be in the future.");
            return false;
        }

        LocalDate recoveryEndDate = recoveryEndDatePicker.getValue();
        if (recoveryEndDate == null || recoveryEndDate.isBefore(recoveryStartDate)) {
            showAlert("Validation Error", "End date must be after the start date.");
            return false;
        }

        if (recoveryStatusChoiceBox.getValue() == null) {
            showAlert("Validation Error", "Please select the recovery status.");
            return false;
        }

        String recoveryDescription = recoveryDescriptionField.getText().trim();
        if (recoveryDescription.isEmpty()) {
            showAlert("Validation Error", "Please enter a recovery description.");
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
