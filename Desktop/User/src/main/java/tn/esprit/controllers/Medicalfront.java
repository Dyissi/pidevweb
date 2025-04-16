package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.stage.Stage;

import java.io.IOException;


public class Medicalfront {
    @FXML private Label claimButton;

    @FXML
    private Button viewInjuryButton;

    @FXML
    private Button viewRecoveryPlanButton;
    @FXML
    public void initialize() {

        claimButton.setOnMouseClicked(event -> switchScreenClaim3());

        viewInjuryButton.setOnMouseClicked(event -> switchScreenListInjury());

        viewRecoveryPlanButton.setOnMouseClicked(event -> switchScreenListRecoveryPlans());

    }


    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }



    private void switchScreenClaim3() {

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/AddClaimMedical.fxml"));
            Parent root = loader.load();

            AddClaimMedical controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Adding Claim");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) claimButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open Training Session screen: " + e.getMessage());
        }
    }

    private void switchScreenInjury() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/AddAthleteInjury.fxml"));
            Parent root = loader.load();

            // Initialize the AddAthleteInjuryController for this screen
            AddAthleteInjuryController controller = loader.getController();
            controller.initialize();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Add Injury");
            stage.setUserData(this);
            stage.show();

            // Close the current window
            Stage currentStage = (Stage) viewInjuryButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open Add Injury screen: " + e.getMessage());
        }
    }

    private void switchScreenListInjury() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListInjury.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Injury List");
            stage.setUserData(this);
            stage.show();

            // Close the current window
            Stage currentStage = (Stage) viewInjuryButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open Injury List screen: " + e.getMessage());
        }
    }


    private void switchScreenListRecoveryPlans() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListRecoveryPlan.fxml"));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Recovery Plans List");
            stage.setUserData(this);
            stage.show();

            // Close the current window
            Stage currentStage = (Stage) viewRecoveryPlanButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            e.printStackTrace(); // Print the error in the console
            showAlert("Error", "Failed to open Recovery Plans List screen: " + e.getMessage());
        }

    }





}
