package tn.esprit.controllers;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.input.MouseEvent;
import javafx.stage.Stage;
import tn.esprit.entities.ClaimAction;
import tn.esprit.entities.ClaimActionType;
import tn.esprit.services.ClaimActionServices;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;

public class UpdateClaimActionController {

    @FXML
    private TextField claimIdField;

    @FXML
    private ChoiceBox<ClaimActionType> claimActionTypeBox;

    @FXML
    private DatePicker claimActionStartDatePicker;

    @FXML
    private DatePicker claimActionEndDatePicker;

    @FXML
    private TextField claimActionNotesField;

    @FXML
    private Button updateClaimActionButton;

    @FXML
    private Button cancelButton;
    @FXML
    private Label claimsButton;
    @FXML
    private Label claimActionsButton;

    private ClaimActionServices claimActionService;
    private ClaimAction selectedClaimAction; // To hold the claim action being updated
    private ShowClaimActionController showClaimActionController; // Reference to the ShowClaimActionController

    public void setClaimActionData(ClaimAction selectedClaimAction) {
        this.selectedClaimAction = selectedClaimAction; // Pass the selected claim action when opening this controller
        // Fill fields with the selected claim action's data
        if (selectedClaimAction != null) {
            claimIdField.setText(String.valueOf(selectedClaimAction.getClaim().getClaimId()));
            claimActionTypeBox.setValue(selectedClaimAction.getClaimActionType());
            claimActionStartDatePicker.setValue(selectedClaimAction.getClaimActionStartDate());
            claimActionEndDatePicker.setValue(selectedClaimAction.getClaimActionEndDate());
            claimActionNotesField.setText(selectedClaimAction.getClaimActionNotes());
        }
    }

    public void setShowClaimActionController(ShowClaimActionController showClaimActionController) {
        this.showClaimActionController = showClaimActionController; // Set the reference to ShowClaimActionController
    }

    @FXML
    public void initialize() {
        claimActionService = new ClaimActionServices();

        // Populate ChoiceBox with enum values
        claimActionTypeBox.getItems().addAll(ClaimActionType.values());

        // Set default values (optional)
        claimActionTypeBox.setValue(ClaimActionType.NO_ACTION_TAKEN);

        // Set button actions
        updateClaimActionButton.setOnAction(event -> updateClaimAction());
        cancelButton.setOnAction(event -> closeWindow());

        // Set the date picker constraints
        setDatePickerConstraints();

        claimsButton.setOnMouseClicked((MouseEvent event) -> {
            switchToClaimsScreen(event);
        });
        claimActionsButton.setOnMouseClicked((MouseEvent event) -> {
            switchToClaimActionsScreen(event);
        });
    }

    private void setDatePickerConstraints() {
        LocalDate today = LocalDate.now();

        // Set the claimActionStartDatePicker to not allow dates before today
        claimActionStartDatePicker.setDayCellFactory(picker -> new DateCell() {
            @Override
            public void updateItem(LocalDate item, boolean empty) {
                super.updateItem(item, empty);
                setDisable(empty || item.isBefore(today));
            }
        });

        // Set the claimActionEndDatePicker to not allow dates before the selected start date
        claimActionStartDatePicker.valueProperty().addListener((observable, oldValue, newValue) -> {
            claimActionEndDatePicker.setDayCellFactory(picker -> new DateCell() {
                @Override
                public void updateItem(LocalDate item, boolean empty) {
                    super.updateItem(item, empty);
                    setDisable(empty || item.isBefore(newValue));
                }
            });
        });
    }

    private void updateClaimAction() {
        int claimId;
        try {
            claimId = Integer.parseInt(claimIdField.getText());
        } catch (NumberFormatException e) {
            showAlert(Alert.AlertType.ERROR, "Form Error", "Please enter a valid claim ID.");
            return;
        }

        ClaimActionType actionType = claimActionTypeBox.getValue();
        LocalDate startDate = claimActionStartDatePicker.getValue();
        LocalDate endDate = claimActionEndDatePicker.getValue();
        String notes = claimActionNotesField.getText();

        // Validate inputs
        boolean valid = true;

        if (claimIdField.getText().isEmpty()) {
            claimIdField.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimIdField.getStyleClass().remove("invalid-input");
        }

        if (actionType == null) {
            claimActionTypeBox.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimActionTypeBox.getStyleClass().remove("invalid-input");
        }

        if (startDate == null) {
            claimActionStartDatePicker.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimActionStartDatePicker.getStyleClass().remove("invalid-input");
        }

        if (endDate == null) {
            claimActionEndDatePicker.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimActionEndDatePicker.getStyleClass().remove("invalid-input");
        }

        if (notes.isEmpty()) {
            claimActionNotesField.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimActionNotesField.getStyleClass().remove("invalid-input");
        }

        if (startDate != null && endDate != null && startDate.isAfter(endDate)) {
            showAlert(Alert.AlertType.ERROR, "Date Error", "Start date cannot be after end date.");
            return; // Exit the method to prevent the second message from appearing
        }

        if (!valid) {
            showAlert(Alert.AlertType.ERROR, "Form Error", "Please fill in all fields correctly.");
            return;
        }

        // Update the selected claim action with the new data
        selectedClaimAction.setClaimActionType(actionType);
        selectedClaimAction.setClaimActionStartDate(startDate);
        selectedClaimAction.setClaimActionEndDate(endDate);
        selectedClaimAction.setClaimActionNotes(notes);

        try {
            // Update claim action in the database
            claimActionService.update(selectedClaimAction);
            showAlert(Alert.AlertType.INFORMATION, "Success", "Claim action updated successfully!");
            showClaimActionController.refreshTable(); // Refresh the table in ShowClaimActionController
            closeWindow(); // Close the window after update
        } catch (SQLException e) {
            e.printStackTrace();
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to update claim action.");
        }
    }

    private void closeWindow() {
        Stage stage = (Stage) cancelButton.getScene().getWindow();
        stage.close();
    }

    private void showAlert(Alert.AlertType alertType, String title, String content) {
        Alert alert = new Alert(alertType);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }

    private void clearFields() {
        claimIdField.clear();
        claimActionTypeBox.setValue(ClaimActionType.NO_ACTION_TAKEN);
        claimActionStartDatePicker.setValue(null);
        claimActionEndDatePicker.setValue(null);
        claimActionNotesField.clear();

        // Remove invalid-input class
        claimIdField.getStyleClass().remove("invalid-input");
        claimActionTypeBox.getStyleClass().remove("invalid-input");
        claimActionStartDatePicker.getStyleClass().remove("invalid-input");
        claimActionEndDatePicker.getStyleClass().remove("invalid-input");
        claimActionNotesField.getStyleClass().remove("invalid-input");
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

    @FXML
    private void handleBackButtonAction(ActionEvent event) {
        try {
            // Load the AdminBack interface
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/AdminBack.fxml"));
            Parent adminBackRoot = loader.load();

            // Get the current stage and switch the scene
            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            stage.setScene(new Scene(adminBackRoot));
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}