package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.Alert.AlertType;
import javafx.stage.Stage;
import tn.esprit.entities.team;
import tn.esprit.services.teamServices;

import java.io.IOException;
import java.sql.SQLException;
import java.util.regex.Pattern;

public class updateTeam {

    @FXML private Button updateTeambtn;
    @FXML private TextField teamLossesU;
    @FXML private TextField teamNameU;
    @FXML private ComboBox<String> teamTOSU;
    @FXML private TextField teamWinsU;
    @FXML
    private Label addTeamLabelU;
    @FXML
    private Label backBtn;

    private team selectedTeam;
    private final teamServices teamService = new teamServices();

    public void initData(team t) {
        updateTeambtn.setOnAction(event -> updateTeamAction());
        backBtn.setOnMouseClicked(event -> switchBackToCoachFront());
        this.selectedTeam = t;
        teamNameU.setText(t.getTeamName());
        teamLossesU.setText(String.valueOf(t.getTeamL()));
        teamWinsU.setText(String.valueOf(t.getTeamW()));
        teamTOSU.setValue(t.getTeamTOS());
        addTeamLabelU.setOnMouseClicked(event -> switchScreenAdd());
    }
    @FXML
    private void updateTeamAction() {
        if (!validateInputs()) {
            return;
        }
        selectedTeam.setTeamName(teamNameU.getText());
        selectedTeam.setTeamL(Integer.parseInt(teamLossesU.getText()));
        selectedTeam.setTeamW(Integer.parseInt(teamWinsU.getText()));
        selectedTeam.setTeamTOS(teamTOSU.getValue());

        try {
            selectedTeam.setTeamNath(selectedTeam.getTeamNath());
            teamService.update(selectedTeam.getTeamId(), selectedTeam);
            System.out.println("Team updated successfully!");

            // Close the update window
            updateTeambtn.getScene().getWindow().hide();

        } catch (SQLException e) {
            System.err.println("Failed to update team: " + e.getMessage());
        }
    }



    private void showAlert(String title, String message) {
        Alert alert = new Alert(AlertType.ERROR);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void switchScreenAdd() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addTeam.fxml"));
            Parent root = loader.load();

            addTeam controller = loader.getController();
            controller.initialize();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Add Team");
            stage.setUserData(this);
            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to open add screen: " + e.getMessage());
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

        if (teamNameU.getText().isEmpty() || !Pattern.matches(namePattern, teamNameU.getText())) {
            showAlert("Validation Error", "Team name must contain only letters and cannot be empty.");
            valid = false;
        } else if (teamTOSU.getValue() == null) {
            showAlert("Validation Error", "Please select a sport.");
            valid = false;
        }
        try {
            Integer.parseInt(teamLossesU.getText());
        } catch (NumberFormatException e) {
            showAlert("Validation Error", "Losses must be a valid integer.");
            valid = false;
        }

        try {
            Integer.parseInt(teamWinsU.getText());
        } catch (NumberFormatException e) {
            showAlert("Validation Error", "Wins must be a valid integer.");
            valid = false;
        }


        return valid;
    }
}
