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
import tn.esprit.entities.Injury;
import tn.esprit.entities.InjuryType;
import tn.esprit.entities.Severity;
import tn.esprit.entities.user;
import tn.esprit.services.InjuryServices;
import tn.esprit.services.UserServices;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.List;

public class AddAthleteInjuryController {

    @FXML private ChoiceBox<String> AthleteNameTypeChoiceBox;
    @FXML private ChoiceBox<String> AthleteLastNameTypeChoiceBox;
    @FXML private TextField injuryDescriptionField;
    @FXML private DatePicker injuryDatePicker;
    @FXML private ChoiceBox<Severity> severityChoiceBox;
    @FXML private ChoiceBox<InjuryType> injuryTypeChoiceBox;
    @FXML private Button addInjuryButton;
    @FXML private Button viewInjuryButton;

    private UserServices userServices;
    private InjuryServices injuryServices;

    public AddAthleteInjuryController() {
        userServices = new UserServices();
        injuryServices = new InjuryServices();
    }

    @FXML
    public void initialize() {
        loadAthletes();
        loadInjuryTypes();
        loadSeverities();

        addInjuryButton.setOnAction(this::handleAddInjury);
        viewInjuryButton.setOnAction(event -> switchToListInjury()); // Button action to switch screen
    }

    private void switchToListInjury() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListInjury.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Injury List");
            stage.show();

            // Close the current window
            Stage currentStage = (Stage) viewInjuryButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open Injury List screen: " + e.getMessage());
        }
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

    private void handleAddInjury(ActionEvent event) {
        String selectedFirstName = AthleteNameTypeChoiceBox.getValue();
        String selectedLastName = AthleteLastNameTypeChoiceBox.getValue();
        String injuryDescription = injuryDescriptionField.getText();
        LocalDate injuryDate = injuryDatePicker.getValue();
        Severity injury_severity = severityChoiceBox.getValue();
        InjuryType injuryType = injuryTypeChoiceBox.getValue();

        if (selectedFirstName == null || selectedLastName == null || injuryDescription.isEmpty() || injuryDate == null || injury_severity == null || injuryType == null) {
            showAlert("Error", "All fields must be filled!");
            return;
        }

        user athlete = userServices.getAthleteByFullName(selectedFirstName, selectedLastName);
        if (athlete == null) {
            showAlert("Error", "Athlete not found!");
            return;
        }

        Injury injury = new Injury();
        injury.setInjury_description(injuryDescription);
        injury.setInjuryDate(injuryDate);
        injury.setInjury_severity(injury_severity);
        injury.setInjuryType(injuryType);
        injury.setUser(athlete);

        try {
            injuryServices.addP(injury);
            showAlert("Success", "Injury added successfully!");
        } catch (SQLException e) {
            showAlert("Error", "Error adding injury: " + e.getMessage());
        } catch (Exception e) {
            showAlert("Error", "Failed to add injury: " + e.getMessage());
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
