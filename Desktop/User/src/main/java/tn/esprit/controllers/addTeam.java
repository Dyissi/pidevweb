package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.ComboBox;
import javafx.scene.control.Button;
import javafx.scene.control.TextField;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.paint.Color;
import javafx.scene.control.Label;

import java.awt.*;
import java.io.IOException;
import java.net.URL;
import java.sql.SQLException;
import java.util.ResourceBundle;
import java.util.regex.Pattern;

import javafx.scene.layout.*;
import javafx.stage.Stage;
import tn.esprit.entities.team;
import tn.esprit.services.teamServices;

public class addTeam {
    @FXML
    private Label consultTeamLabel;
    @FXML
    private Label addTeamLabel;


    @FXML
    private Button addTeambtn;

    @FXML
    private TextField teamName;

    @FXML
    private ComboBox<String> teamTOS;

    @FXML
    private Label backBtn;


    private final teamServices ts = new teamServices();

    @FXML
    public void initialize() {
        addTeambtn.setOnAction(event -> addTeamAction());
        consultTeamLabel.setOnMouseClicked(event -> switchScreenConsult());
        backBtn.setOnMouseClicked(event -> switchBackToCoachFront());
    }

    @FXML
    private void addTeamAction() {
        if (!validateInputs()) {
            return;
        }
        team newTeam = null;
        try {
            String team_Name = teamName.getText();
            String selectedSport = teamTOS.getValue(); // Ensure this isn't null
            if (team_Name.isEmpty() || selectedSport == null) {
                System.out.println("Please enter a team name and select a sport.");
                return;
            }

            newTeam = new team(team_Name, 0, selectedSport, 0, 0);
            ts.add(newTeam);
            showAlert("Success", " Team Added Successfully!");

            clearFields();
        } catch (NumberFormatException e) {
            showAlert("Error", "Please enter valid values!");
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
        System.out.println("Added team: " + newTeam);
    }
    private void switchScreenConsult() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/consultTeam.fxml"));
            Parent root = loader.load();

           consultTeam controller = loader.getController();
           controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Consult Team");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) addTeambtn.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
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

    private boolean validateInputs() {
        boolean valid = true;
        String namePattern = "^[a-zA-Z]+$";

        if (!teamName.getText().isEmpty() && Pattern.matches(namePattern, teamName.getText())) {
            if (teamTOS.getValue() == null) {
                showAlert("Validation Error", "Please select a sport.");
                valid = false;
            }
        } else {
            showAlert("Validation Error", "Team name must contain only letters and cannot be empty.");
            valid = false;
        }


        return valid;
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }
    private void clearFields() {
        teamName.clear();
        teamTOS.setValue(null);
    }

}