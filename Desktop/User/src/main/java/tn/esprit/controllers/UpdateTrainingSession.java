package tn.esprit.controllers;

import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.ChoiceBox;
import javafx.scene.control.TextField;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import javafx.scene.layout.Border;
import javafx.scene.layout.BorderStroke;
import javafx.scene.layout.BorderStrokeStyle;
import javafx.scene.layout.BorderWidths;
import javafx.scene.layout.CornerRadii;
import javafx.scene.paint.Color;
import tn.esprit.entities.Duration;
import tn.esprit.entities.Focus;
import tn.esprit.entities.TrainingSession;
import tn.esprit.services.TrainingSessionServices;

import java.sql.SQLException;
import java.time.LocalTime;

public class UpdateTrainingSession {
    @FXML private ChoiceBox<Focus> training_focus;
    @FXML private TextField training_start_time;
    @FXML private ChoiceBox<String> training_duration;
    @FXML private TextField training_location;
    @FXML private TextField training_notes;
    @FXML private Button updateButton;

    private TrainingSession selectedTrainingSession;
    private TrainingSessionServices trainingSessionService = new TrainingSessionServices();

    public void initData(TrainingSession trainingSession) {
        this.selectedTrainingSession = trainingSession;

        // Populate the focus ChoiceBox
        training_focus.setItems(FXCollections.observableArrayList(Focus.values()));
        training_focus.setValue(trainingSession.getFocus());

        // Populate the duration ChoiceBox
        training_duration.setItems(FXCollections.observableArrayList(
                Duration.FORTY_FIVE.getMinutes(),
                Duration.SIXTY.getMinutes(),
                Duration.NINETY.getMinutes(),
                Duration.ONE_TWENTY.getMinutes()
        ));
        training_duration.setValue(trainingSession.getDuration().getMinutes());

        training_start_time.setText(trainingSession.getStart_time().toString());
        training_location.setText(trainingSession.getLocation());
        training_notes.setText(trainingSession.getSession_notes());
    }

    @FXML
    private void updateTrainingSession() {
        if (!validateUpdateInputs()) {
            showAlert("Error", "Please correct the highlighted fields.");
            return;
        }
        selectedTrainingSession.setFocus(training_focus.getValue());
        selectedTrainingSession.setStart_time(LocalTime.parse(training_start_time.getText()));
        selectedTrainingSession.setDuration(Duration.fromString(training_duration.getValue()));
        selectedTrainingSession.setLocation(training_location.getText());
        selectedTrainingSession.setSession_notes(training_notes.getText());

        try {
            // Update the training session in the database
            trainingSessionService.update(selectedTrainingSession);
            System.out.println("Training session updated successfully!");

            // Refresh the TableView in the AddTrainingSession controller
            AddTrainingSession controller = (AddTrainingSession) updateButton.getScene().getWindow().getUserData();
            controller.updateTrainingSessionList();

            // Close the update window
            updateButton.getScene().getWindow().hide();
        } catch (SQLException e) {
            System.err.println("Failed to update training session: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(AlertType.ERROR);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private boolean validateUpdateInputs() {
        boolean valid = true;

        // Validate focus
        if (training_focus.getValue() == null) {
            training_focus.setStyle("-fx-border-color: red;");
            valid = false;
        } else {
            training_focus.setStyle("");
        }

        // Validate start_time
        try {
            LocalTime.parse(training_start_time.getText());
            training_start_time.setBorder(Border.EMPTY);
        } catch (Exception e) {
            training_start_time.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        }

        // Validate duration
        if (training_duration.getValue() == null) {
            training_duration.setStyle("-fx-border-color: red;");
            valid = false;
        } else {
            training_duration.setStyle("");
        }

        // Validate location
        if (training_location.getText().isEmpty()) {
            training_location.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        } else {
            training_location.setBorder(Border.EMPTY);
        }

        // Validate notes
        if (training_notes.getText().isEmpty()) {
            training_notes.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        } else {
            training_notes.setBorder(Border.EMPTY);
        }

        return valid;
    }
}