package tn.esprit.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import tn.esprit.entities.*;
import tn.esprit.services.RecoveryPlanServices;
import tn.esprit.services.UserServices;
import tn.esprit.services.InjuryServices;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.List;

public class AddRecoveryPlanController {

    @FXML private ChoiceBox<String> athleteFirstNameChoiceBox;
    @FXML private ChoiceBox<String> athleteLastNameChoiceBox;
    @FXML private TextField recoveryDescriptionTextField;
    @FXML private DatePicker recoveryStartDatePicker;
    @FXML private DatePicker recoveryEndDatePicker;
    @FXML private ChoiceBox<RecoveryGoal> recoveryGoalChoiceBox;
    @FXML private ChoiceBox<RecoveryStatus> recoveryStatusChoiceBox;
    @FXML private ChoiceBox<String> injuryTypechoiceBox;
    @FXML private Button addRecoveryPlanButton;
    @FXML private Button viewRecoveryPlanButton;

    private UserServices userServices;
    private InjuryServices injuryServices;
    private RecoveryPlanServices recoveryPlanServices;

    public AddRecoveryPlanController() {
        userServices = new UserServices();
        injuryServices = new InjuryServices();
        recoveryPlanServices = new RecoveryPlanServices();
    }

    @FXML
    public void initialize() {
        loadAthletes();
        loadRecoveryGoals();
        loadRecoveryStatuses();

        addRecoveryPlanButton.setOnAction(this::handleAddRecoveryPlan);
        viewRecoveryPlanButton.setOnAction(event -> switchToListRecoveryPlan());
    }

    private void switchToListRecoveryPlan() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListRecoveryPlan.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Recovery Plan List");
            stage.show();

            Stage currentStage = (Stage) viewRecoveryPlanButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open Recovery Plan List screen: " + e.getMessage());
        }
    }

    private void loadAthletes() {
        List<user> athletes = userServices.getUsersByRole("Athlete");
        ObservableList<String> athleteNames = FXCollections.observableArrayList();

        for (user athlete : athletes) {
            athleteNames.add(athlete.getUser_fname());
        }

        athleteFirstNameChoiceBox.setItems(athleteNames);
        athleteFirstNameChoiceBox.getSelectionModel().selectedItemProperty().addListener((observable, oldValue, newValue) -> loadLastNames(newValue));
    }

    private void loadLastNames(String firstName) {
        List<user> athletes = userServices.getUsersByRole("Athlete");
        ObservableList<String> athleteLastNames = FXCollections.observableArrayList();

        for (user athlete : athletes) {
            if (athlete.getUser_fname().equals(firstName)) {
                athleteLastNames.add(athlete.getUser_lname());
            }
        }

        athleteLastNameChoiceBox.setItems(athleteLastNames);

        // Load injury type when last name is selected
        athleteLastNameChoiceBox.getSelectionModel().selectedItemProperty().addListener((observable, oldValue, newValue) -> {
            try {
                loadInjuryType(firstName, newValue);
            } catch (SQLException e) {
                throw new RuntimeException(e);
            }
        });
    }

    private void loadInjuryType(String firstName, String lastName) throws SQLException {
        if (firstName == null || lastName == null) return;

        user athlete = userServices.getAthleteByFullName(firstName, lastName);
        if (athlete == null) return;

        Injury injury = injuryServices.getInjuryByAthleteId(athlete);
        if (injury == null) {
            injuryTypechoiceBox.setItems(FXCollections.observableArrayList());
            return;
        }

        ObservableList<String> injuryTypes = FXCollections.observableArrayList();
        injuryTypes.add(injury.getInjuryType().toString()); // Convert InjuryType to string
        injuryTypechoiceBox.setItems(injuryTypes);
        injuryTypechoiceBox.getSelectionModel().selectFirst(); // Auto-select the first available type
    }


    private void loadRecoveryGoals() {
        ObservableList<RecoveryGoal> recoveryGoals = FXCollections.observableArrayList(RecoveryGoal.values());
        recoveryGoalChoiceBox.setItems(recoveryGoals);
    }

    private void loadRecoveryStatuses() {
        ObservableList<RecoveryStatus> recoveryStatuses = FXCollections.observableArrayList(RecoveryStatus.values());
        recoveryStatusChoiceBox.setItems(recoveryStatuses);
    }

    private void handleAddRecoveryPlan(ActionEvent event) {
        try {
            String selectedFirstName = athleteFirstNameChoiceBox.getValue();
            String selectedLastName = athleteLastNameChoiceBox.getValue();
            String recoveryDescription = recoveryDescriptionTextField.getText();
            LocalDate recoveryStartDate = recoveryStartDatePicker.getValue();
            LocalDate recoveryEndDate = recoveryEndDatePicker.getValue();
            RecoveryGoal recoveryGoal = recoveryGoalChoiceBox.getValue();
            RecoveryStatus recoveryStatus = recoveryStatusChoiceBox.getValue();
            String injuryType = injuryTypechoiceBox.getValue();

            if (selectedFirstName == null || selectedLastName == null || recoveryDescription.isEmpty() || recoveryStartDate == null || recoveryEndDate == null || recoveryGoal == null || recoveryStatus == null || injuryType == null) {
                showAlert("Error", "All fields must be filled!");
                return;
            }

            user athlete = userServices.getAthleteByFullName(selectedFirstName, selectedLastName);
            if (athlete == null) {
                showAlert("Error", "Athlete not found!");
                return;
            }

            Injury injury = injuryServices.getInjuryByAthleteId(athlete);
            if (injury == null) {
                showAlert("Error", "No injury found for this athlete!");
                return;
            }

            RecoveryPlan recoveryPlan = new RecoveryPlan();
            recoveryPlan.setRecovery_Description(recoveryDescription);
            recoveryPlan.setRecovery_StartDate(recoveryStartDate);
            recoveryPlan.setRecovery_EndDate(recoveryEndDate);
            recoveryPlan.setRecovery_Goal(recoveryGoal);
            recoveryPlan.setRecovery_Status(recoveryStatus);
            recoveryPlan.setInjury(injury);
            recoveryPlan.setUser(athlete);

            recoveryPlanServices.addP(recoveryPlan); // Use addP for persistence
            showAlert("Success", "Recovery Plan added successfully!");

        } catch (SQLException e) {
            showAlert("Error", "SQL error occurred: " + e.getMessage());
        } catch (Exception e) {
            showAlert("Error", "Failed to add Recovery Plan: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
