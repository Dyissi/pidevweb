package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import tn.esprit.entities.Claim;
import tn.esprit.entities.ClaimCategory;
import tn.esprit.entities.ClaimStatus;
import tn.esprit.services.ClaimServices;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;

public class AddClaimController {

    @FXML
    private TextField claimDescriptionField;

    @FXML
    private DatePicker claimDatePicker;

    @FXML
    private ChoiceBox<ClaimCategory> claimCategoryBox;

    @FXML
    private Button addClaimButton;

    @FXML private Label teamsBtn;
    @FXML private Label tournBtn;
    @FXML private Label performanceBtn;
    @FXML private Label TrainingSesh;

    @FXML
    public void initialize() {
        // Populate ChoiceBox with enum values
        claimCategoryBox.getItems().addAll(ClaimCategory.values());

        // Set default values (optional)
        claimCategoryBox.setValue(ClaimCategory.MISCONDUCT);

        // Set button action
        addClaimButton.setOnAction(event -> addClaim());

        // Set the date picker to not allow dates before September 1st of the current academic year
        setDatePickerConstraints();

        teamsBtn.setOnMouseClicked(event -> switchScreenTeam());
        tournBtn.setOnMouseClicked(event -> switchScreenTournament());
        performanceBtn.setOnMouseClicked(event -> switchScreenPerformance());
        TrainingSesh.setOnMouseClicked(event -> switchScreenTraining());
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

    private void addClaim() {
        ClaimServices claimService = new ClaimServices();

        String description = claimDescriptionField.getText();
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

        // Create Claim object with status IN_REVIEW
        Claim claim = new Claim(description, ClaimStatus.IN_REVIEW, claimDate, category);

        try {
            // Add claim to database
            claimService.add(claim);
            showAlert(Alert.AlertType.INFORMATION, "Success", "Claim added successfully!");

            // Clear fields after successful addition
            clearFields();
        } catch (SQLException e) {
            e.printStackTrace();
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to add claim.");
        }
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
        claimCategoryBox.setValue(ClaimCategory.MISCONDUCT);

        // Remove invalid-input class
        claimDescriptionField.getStyleClass().remove("invalid-input");
        claimDatePicker.getStyleClass().remove("invalid-input");
        claimCategoryBox.getStyleClass().remove("invalid-input");
    }
    private void switchScreenTeam() {

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addTeam.fxml"));
            Parent root = loader.load();

            addTeam controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Adding Team");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) teamsBtn.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open add screen: " + e.getMessage());
        }
    }
    private void switchScreenTournament() {


        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addTournament.fxml"));
            Parent root = loader.load();

            addTournament controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Adding Tournament");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) tournBtn.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open tournament screen: " + e.getMessage());
        }
    }
    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }
    // joujou (Performance and training session )

    private void switchScreenPerformance() {

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addPerformance.fxml"));
            Parent root = loader.load();

            AddPerformanceData controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Performance");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) performanceBtn.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open performance screen: " + e.getMessage());
        }
    }

    private void switchScreenTraining() {

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addTrainingSession.fxml"));
            Parent root = loader.load();

            AddTrainingSession controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Adding Training Session");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) teamsBtn.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open Training Session screen: " + e.getMessage());
        }
    }
}