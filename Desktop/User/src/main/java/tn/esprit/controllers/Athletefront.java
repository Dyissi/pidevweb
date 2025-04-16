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

public class Athletefront {
    @FXML
    private Label claimButton;
    @FXML
    private Button MyInjuriesButton;





    @FXML
    public void initialize() {
        // Set action for claimButton
        claimButton.setOnMouseClicked(event -> switchScreenClaim2());
        MyInjuriesButton.setOnMouseClicked(event -> switchScreenToMyInjuries());




    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // Switch to Add Claim screen (already exists)
    private void switchScreenClaim2() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/AddClaimAthlete.fxml"));
            Parent root = loader.load();

            AddClaimAthlete controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Adding Claim");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) claimButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open Claim screen: " + e.getMessage());
        }
    }

    // Switch to My injuries
    private void switchScreenToMyInjuries() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/MyInjuries.fxml"));
            Parent root = loader.load();

            MyInjuriesController controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("View My injuries");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) MyInjuriesButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open Claim screen: " + e.getMessage());
        }
    }




}
