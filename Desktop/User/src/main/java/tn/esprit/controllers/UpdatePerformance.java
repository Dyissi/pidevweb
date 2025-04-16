package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.DatePicker;
import javafx.scene.control.TextField;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import javafx.scene.paint.Color;
import javafx.scene.layout.Background;
import javafx.scene.layout.BackgroundFill;
import javafx.scene.layout.CornerRadii;
import javafx.scene.layout.Pane;
import javafx.scene.layout.Border;
import javafx.scene.layout.BorderStroke;
import javafx.scene.layout.BorderStrokeStyle;
import javafx.scene.layout.BorderWidths;
import javafx.scene.layout.CornerRadii;
import javafx.scene.layout.BackgroundFill;
import javafx.scene.paint.Color;
import javafx.scene.control.TextField;
import javafx.scene.control.DatePicker;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import javafx.scene.control.Button;
import javafx.scene.layout.Background;
import javafx.scene.layout.BackgroundFill;
import javafx.scene.layout.CornerRadii;
import javafx.geometry.Insets;
import tn.esprit.entities.Performance;
import tn.esprit.services.PerformanceServices;

import java.sql.SQLException;

public class UpdatePerformance {
    @FXML private TextField performance_speed;
    @FXML private TextField performance_agility;
    @FXML private TextField performance_nbr_goals;
    @FXML private TextField performance_assists;
    @FXML private TextField performance_nbr_fouls;
    @FXML private DatePicker performance_date_recorded;
    @FXML private Button updateButton;

    private Performance selectedPerformance;
    private PerformanceServices performanceService = new PerformanceServices();

    public void initData(Performance performance) {
        this.selectedPerformance = performance;
        performance_speed.setText(String.valueOf(performance.getSpeed()));
        performance_agility.setText(String.valueOf(performance.getAgility()));
        performance_nbr_goals.setText(String.valueOf(performance.getNbr_goals()));
        performance_assists.setText(String.valueOf(performance.getAssists()));
        performance_nbr_fouls.setText(String.valueOf(performance.getNbr_fouls()));
        performance_date_recorded.setValue(performance.getDate_recorded().toLocalDate());
    }

    @FXML
    private void updatePerformance() {
        if (!validateInputs()) {
            showAlert("Error", "Please correct the highlighted fields.");
            return;
        }
        selectedPerformance.setSpeed(Float.parseFloat(performance_speed.getText()));
        selectedPerformance.setAgility(Float.parseFloat(performance_agility.getText()));
        selectedPerformance.setNbr_goals(Integer.parseInt(performance_nbr_goals.getText()));
        selectedPerformance.setAssists(Integer.parseInt(performance_assists.getText()));
        selectedPerformance.setNbr_fouls(Integer.parseInt(performance_nbr_fouls.getText()));
        selectedPerformance.setDate_recorded(java.sql.Date.valueOf(performance_date_recorded.getValue()));

        try {
            // Update the performance in the database
            performanceService.update(selectedPerformance);
            System.out.println("Performance updated successfully!");

            // Refresh the TableView in the AddPerformanceData controller
            AddPerformanceData controller = (AddPerformanceData) updateButton.getScene().getWindow().getUserData();
            controller.updatePerformanceList();

            // Close the update window
            updateButton.getScene().getWindow().hide();
        } catch (SQLException e) {
            System.err.println("Failed to update performance: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(AlertType.ERROR);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }
    private boolean validateInputs() {
        boolean valid = true;
        // Validate speed
        try {
            Float.parseFloat(performance_speed.getText());
            performance_speed.setBorder(Border.EMPTY);
        } catch (NumberFormatException e) {
            performance_speed.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        }
        // Validate agility
        try {
            Float.parseFloat(performance_agility.getText());
            performance_agility.setBorder(Border.EMPTY);
        } catch (NumberFormatException e) {
            performance_agility.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        }
        // Validate goals
        try {
            Integer.parseInt(performance_nbr_goals.getText());
            performance_nbr_goals.setBorder(Border.EMPTY);
        } catch (NumberFormatException e) {
            performance_nbr_goals.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        }
        // Validate assists
        try {
            Integer.parseInt(performance_assists.getText());
            performance_assists.setBorder(Border.EMPTY);
        } catch (NumberFormatException e) {
            performance_assists.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        }
        // Validate fouls
        try {
            Integer.parseInt(performance_nbr_fouls.getText());
            performance_nbr_fouls.setBorder(Border.EMPTY);
        } catch (NumberFormatException e) {
            performance_nbr_fouls.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        }
        // Validate date
        if (performance_date_recorded.getValue() == null) {
            performance_date_recorded.setBorder(new Border(new BorderStroke(Color.RED, BorderStrokeStyle.SOLID, CornerRadii.EMPTY, BorderWidths.DEFAULT)));
            valid = false;
        } else {
            performance_date_recorded.setBorder(Border.EMPTY);
        }
        return valid;
    }
}