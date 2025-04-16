package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.stage.Stage;
import javafx.scene.control.Label;
import javafx.scene.input.MouseEvent;

import javafx.event.ActionEvent; // Import this for ActionEvent
import java.io.IOException;

public class AdminBack {
    @FXML
    private Label claimsButton;
    @FXML
    private Label claimActionsButton;
    @FXML
    private Label tocoach_id;
    @FXML
    private Label toMed_id;
    @FXML
    public void initialize() {
        claimsButton.setOnMouseClicked((MouseEvent event) -> {
            switchToClaimsScreen(event);
        });
        claimActionsButton.setOnMouseClicked((MouseEvent event) -> {
            switchToClaimActionsScreen(event);
        });
        tocoach_id.setOnMouseClicked(event -> switchScreenToaddCoach());
        toMed_id.setOnMouseClicked(event -> switchScreenToaddMed());
    }
    private void switchScreenToaddCoach() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addUser.fxml"));
            Parent root = loader.load();

            addUser controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Add Coach");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) tocoach_id.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
        }
    }
    private void switchScreenToaddMed() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addMedicalTeam.fxml"));
            Parent root = loader.load();

            addMedicalTeam controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Add Coach");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) toMed_id.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
        }
    }
    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }
    private void switchToClaimsScreen(MouseEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ManageClaimsInterface.fxml"));
            Parent root = loader.load();
            Scene scene = new Scene(root);
            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            stage.setScene(scene);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private void switchToClaimActionsScreen(MouseEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ManageClaimActionsInterface.fxml"));
            Parent root = loader.load();
            Scene scene = new Scene(root);
            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            stage.setScene(scene);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
