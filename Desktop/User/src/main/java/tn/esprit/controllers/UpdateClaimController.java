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
import tn.esprit.entities.Claim;
import tn.esprit.entities.ClaimCategory;
import tn.esprit.entities.ClaimStatus;
import tn.esprit.services.ClaimServices;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;

public class UpdateClaimController {

    @FXML
    private TextField claimDescriptionField;

    @FXML
    private ChoiceBox<ClaimStatus> claimStatusBox;

    @FXML
    private DatePicker claimDatePicker;

    @FXML
    private ChoiceBox<ClaimCategory> claimCategoryBox;

    @FXML
    private Button updateClaimButton;

    @FXML
    private Button cancelButton;

    private ClaimServices claimService;
    private Claim selectedClaim; // To hold the claim being updated
    private ShowClaimController showClaimController;

    @FXML
    private Label claimsButton;
    @FXML
    private Label claimActionsButton;


    public void setClaimData(Claim selectedClaim) {
        this.selectedClaim = selectedClaim; // Pass the selected claim when opening this controller
        // Fill fields with the selected claim's data
        if (selectedClaim != null) {
            claimDescriptionField.setText(selectedClaim.getClaimDescription());
            claimStatusBox.setValue(selectedClaim.getClaimStatus());
            claimDatePicker.setValue(selectedClaim.getClaimDate());
            claimCategoryBox.setValue(selectedClaim.getClaimCategory());
        }
    }

    public void setShowClaimController(ShowClaimController showClaimController) {
        this.showClaimController = showClaimController; // Set the reference to ShowClaimController
    }

    @FXML
    public void initialize() {
        claimService = new ClaimServices();

        // Populate ChoiceBoxes with enum values
        claimStatusBox.getItems().addAll(ClaimStatus.values());
        claimCategoryBox.getItems().addAll(ClaimCategory.values());

        // Set default values (optional)
        claimStatusBox.setValue(ClaimStatus.IN_REVIEW);
        claimCategoryBox.setValue(ClaimCategory.MISCONDUCT);

        // Set button actions
        updateClaimButton.setOnAction(event -> updateClaim());
        cancelButton.setOnAction(event -> closeWindow());

        // Set the date picker to not allow dates before September 1st of the current academic year
        setDatePickerConstraints();
        claimsButton.setOnMouseClicked((MouseEvent event) -> {
            switchToClaimsScreen(event);
        });
        claimActionsButton.setOnMouseClicked((MouseEvent event) -> {
            switchToClaimActionsScreen(event);
        });
    }

    private void setDatePickerConstraints() {
        LocalDate now = LocalDate.now();
        LocalDate startOfAcademicYear = LocalDate.of(now.getYear(), 9, 1);

        // If the current date is before September 1st, set the start date to September 1st of the previous year
        if (now.isBefore(startOfAcademicYear)) {
            startOfAcademicYear = startOfAcademicYear.minusYears(1);
        }

        LocalDate finalStartOfAcademicYear = startOfAcademicYear;
        claimDatePicker.setDayCellFactory(picker -> new DateCell() {
            @Override
            public void updateItem(LocalDate item, boolean empty) {
                super.updateItem(item, empty);
                setDisable(empty || item.isBefore(finalStartOfAcademicYear));
            }
        });
    }

    private void updateClaim() {
        String description = claimDescriptionField.getText();
        ClaimStatus status = claimStatusBox.getValue();
        LocalDate claimDate = claimDatePicker.getValue();
        ClaimCategory category = claimCategoryBox.getValue();

        // Validate inputs
        boolean valid = true;

        if (description.isEmpty()) {
            claimDescriptionField.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimDescriptionField.getStyleClass().remove("invalid-input");
        }

        if (status == null) {
            claimStatusBox.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimStatusBox.getStyleClass().remove("invalid-input");
        }

        if (claimDate == null) {
            claimDatePicker.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimDatePicker.getStyleClass().remove("invalid-input");
        }

        if (category == null) {
            claimCategoryBox.getStyleClass().add("invalid-input");
            valid = false;
        } else {
            claimCategoryBox.getStyleClass().remove("invalid-input");
        }

        if (!valid) {
            showAlert(Alert.AlertType.ERROR, "Form Error", "Please fill in all fields correctly.");
            return;
        }

        // Update the selected claim with the new data
        selectedClaim.setClaimDescription(description);
        selectedClaim.setClaimStatus(status);
        selectedClaim.setClaimDate(claimDate);
        selectedClaim.setClaimCategory(category);

        try {
            // Update claim in the database
            claimService.update(selectedClaim);
            showAlert(Alert.AlertType.INFORMATION, "Success", "Claim updated successfully!");
            showClaimController.refreshTable(); // Refresh the table in ShowClaimController
            closeWindow(); // Close the window after update
        } catch (SQLException e) {
            e.printStackTrace();
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to update claim.");
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
        claimDescriptionField.clear();
        claimDatePicker.setValue(null);
        claimStatusBox.setValue(ClaimStatus.IN_REVIEW);
        claimCategoryBox.setValue(ClaimCategory.MISCONDUCT);

        // Remove invalid-input class
        claimDescriptionField.getStyleClass().remove("invalid-input");
        claimStatusBox.getStyleClass().remove("invalid-input");
        claimDatePicker.getStyleClass().remove("invalid-input");
        claimCategoryBox.getStyleClass().remove("invalid-input");
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