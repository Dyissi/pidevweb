package tn.esprit.controllers;

import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.event.ActionEvent;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import javafx.stage.Stage;
import tn.esprit.services.PerformanceServices;
import tn.esprit.entities.Performance;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.sql.Date;
import java.util.List;

public class AddPerformanceData {
    @FXML
    private TextField performance_speed;

    @FXML
    private TextField performance_agility;

    @FXML
    private TextField performance_nbr_goals;

    @FXML
    private TextField performance_assists;

    @FXML
    private DatePicker performance_date_recorded;

    @FXML
    private TextField performance_nbr_fouls;

    @FXML
    private Button submitButton;

    @FXML
    private TableView<Performance> performanceTable;

    @FXML
    private TableColumn<Performance, Integer> colId;

    @FXML
    private TableColumn<Performance, Float> colSpeed;

    @FXML
    private TableColumn<Performance, Float> colAgility;

    @FXML
    private TableColumn<Performance, Integer> colGoals;

    @FXML
    private TableColumn<Performance, Integer> colAssists;

    @FXML
    private TableColumn<Performance, Date> colDate;

    @FXML
    private TableColumn<Performance, Integer> colFouls;

    @FXML
    private TableColumn<Performance, Void> colActions;
    @FXML
    private Label backBtn;

    private final PerformanceServices performanceService = new PerformanceServices();
    private final ObservableList<Performance> performanceList = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        colId.setCellValueFactory(new PropertyValueFactory<>("performance_id"));
        colSpeed.setCellValueFactory(new PropertyValueFactory<>("speed"));
        colAgility.setCellValueFactory(new PropertyValueFactory<>("agility"));
        colGoals.setCellValueFactory(new PropertyValueFactory<>("nbr_goals"));
        colAssists.setCellValueFactory(new PropertyValueFactory<>("assists"));
        colDate.setCellValueFactory(new PropertyValueFactory<>("date_recorded"));
        colFouls.setCellValueFactory(new PropertyValueFactory<>("nbr_fouls"));

        backBtn.setOnMouseClicked(event -> switchBackToCoachFront());
        performanceTable.setItems(performanceList);
        updatePerformanceList();
        addActionButtonsToTable();
    }

    @FXML
    void addPerformanceData(ActionEvent event) {
        if (!validateInputs()) {
            showAlert("Error", "Please correct the highlighted fields.");
            return;
        }

        try {
            float speed = Float.parseFloat(performance_speed.getText());
            float agility = Float.parseFloat(performance_agility.getText());
            int goals = Integer.parseInt(performance_nbr_goals.getText());
            int assists = Integer.parseInt(performance_assists.getText());
            int fouls = Integer.parseInt(performance_nbr_fouls.getText());

            LocalDate localDate = performance_date_recorded.getValue();
            Date date = Date.valueOf(localDate);

            Performance performance = new Performance(speed, agility, goals, assists, date, fouls);

            performanceService.addP(performance);

            showAlert("Success", "Performance Data Added Successfully!");

            clearFields();

            updatePerformanceList();

        } catch (NumberFormatException e) {
            showAlert("Error", "Please enter valid numbers for speed, agility, goals, assists, and fouls.");
        } catch (SQLException e) {
            showAlert("Error", "Database error: " + e.getMessage());
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
            stage.setTitle("Welcome Coach");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) backBtn.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open coach front screen: " + e.getMessage());
        }
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

    private void clearFields() {
        performance_speed.clear();
        performance_agility.clear();
        performance_nbr_goals.clear();
        performance_assists.clear();
        performance_nbr_fouls.clear();
        performance_date_recorded.setValue(null);
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }
    void updatePerformanceList() {
        performanceList.clear();
        try {
            List<Performance> performances = performanceService.returnList();
            performanceList.addAll(performances);
            performanceTable.refresh();
        } catch (SQLException e) {
            showAlert("Error", "Failed to load performance data: " + e.getMessage());
        }
    }

    private void addActionButtonsToTable() {
        colActions.setCellFactory(param -> new TableCell<>() {
            private final Button updateButton = new Button("Update");
            private final Button deleteButton = new Button("Delete");
            private final HBox pane = new HBox(updateButton, deleteButton);

            {
                updateButton.setStyle("-fx-background-color: #BCCCE0; -fx-text-fill: white;");
                deleteButton.setStyle("-fx-background-color: #D68C45; -fx-text-fill: white;");
                pane.setSpacing(5);

                // Update Button Action
                updateButton.setOnAction(event -> {
                    Performance performance = (Performance) getTableView().getItems().get(getIndex());
                    openUpdatePerformanceScreen(performance);
                });

                // Delete Button Action
                deleteButton.setOnAction(event -> {
                    Performance performance = (Performance) getTableView().getItems().get(getIndex());
                    deletePerformance(performance);
                });
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    setGraphic(pane);
                }
            }
        });
    }

    private void openUpdatePerformanceScreen(Performance performance) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/updatePerformance.fxml"));
            Parent root = loader.load();

            UpdatePerformance controller = loader.getController();
            controller.initData(performance);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Update Performance");
            stage.setUserData(this);
            stage.show();
            updatePerformanceList();
        } catch (IOException e) {
            showAlert("Error", "Failed to open update screen: " + e.getMessage());
        }
    }

    private void deletePerformance(Performance performance) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Delete Confirmation");
        alert.setHeaderText("Are you sure you want to delete this record?");
        alert.setContentText("This action cannot be undone.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    performanceService.delete(performance);
                    performanceList.remove(performance);
                    showAlert("Success", "Performance record deleted successfully!");
                } catch (SQLException e) {
                    showAlert("Error", "Failed to delete performance: " + e.getMessage());
                }
            }
        });
    }
}
