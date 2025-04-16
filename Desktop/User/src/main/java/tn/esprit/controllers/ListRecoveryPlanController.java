package tn.esprit.controllers;

import javafx.beans.property.SimpleStringProperty;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.HBox;
import javafx.stage.Stage;
import tn.esprit.entities.RecoveryPlan;
import tn.esprit.services.RecoveryPlanServices;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.Comparator;
import java.util.List;
import java.util.stream.Collectors;

public class ListRecoveryPlanController {

    @FXML
    public Button UpdateRecoveryPlanButton;
    @FXML
    public Button BackButton;
    @FXML
    private TableColumn<RecoveryPlan, Integer> recovery_id;
    @FXML
    private TableColumn<RecoveryPlan, String> injuryType;
    @FXML
    private TableColumn<RecoveryPlan, String> user_fname;
    @FXML
    private TableColumn<RecoveryPlan, String> user_lname;
    @FXML
    private TableColumn<RecoveryPlan, String> recovery_Goal;
    @FXML
    private TableColumn<RecoveryPlan, String> recovery_Description;
    @FXML
    private TableColumn<RecoveryPlan, LocalDate> recovery_StartDate;
    @FXML
    private TableColumn<RecoveryPlan, LocalDate> recovery_EndDate;
    @FXML
    private TableColumn<RecoveryPlan, String> recovery_Status;
    @FXML
    private TableColumn<RecoveryPlan, Void> Action;

    @FXML
    private Button AddRecoveryPlanButton;
    @FXML
    private TextField searchField;
    @FXML
    private Button sortByStatusButton;

    @FXML
    private TableView<RecoveryPlan> tableView_id;

    private final RecoveryPlanServices recoveryPlanService = new RecoveryPlanServices();
    private final ObservableList<RecoveryPlan> recoveryPlanList = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        // Initialize table columns
        recovery_id.setCellValueFactory(new PropertyValueFactory<>("recovery_id"));
        injuryType.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getInjury().getInjuryType().toString()));
        user_fname.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getUser().getUser_fname()));
        user_lname.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getUser().getUser_lname()));
        recovery_Goal.setCellValueFactory(new PropertyValueFactory<>("recovery_Goal"));
        recovery_Description.setCellValueFactory(new PropertyValueFactory<>("recovery_Description"));
        recovery_StartDate.setCellValueFactory(new PropertyValueFactory<>("recovery_StartDate"));
        recovery_EndDate.setCellValueFactory(new PropertyValueFactory<>("recovery_EndDate"));
        recovery_Status.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getRecovery_Status().toString()));

        tableView_id.setItems(recoveryPlanList);
        updateRecoveryPlanList();
        addActionButtonsToTable();
        AddRecoveryPlanButton.setOnMouseClicked(event -> switchScreen("/AddRecoveryPlan.fxml", "Add New Recovery Plan"));
        UpdateRecoveryPlanButton.setOnMouseClicked(event -> switchScreenToUpdateRecoveryPlan());
        BackButton.setOnMouseClicked(event -> switchScreen("/Medicalfront.fxml", "Back"));

        // Adding filter functionality
        searchField.textProperty().addListener((observable, oldValue, newValue) -> filterRecoveryPlans(newValue));

        // Adding sort functionality
        sortByStatusButton.setOnAction(event -> sortRecoveryPlansByStatus());
    }

    private void filterRecoveryPlans(String keyword) {
        if (keyword == null || keyword.trim().isEmpty()) {
            tableView_id.setItems(recoveryPlanList);
        } else {
            ObservableList<RecoveryPlan> filteredList = FXCollections.observableArrayList(
                    recoveryPlanList.stream()
                            .filter(recoveryPlan -> recoveryPlan.getUser().getUser_fname().toLowerCase().contains(keyword.toLowerCase())
                                    || recoveryPlan.getUser().getUser_lname().toLowerCase().contains(keyword.toLowerCase())
                                    || recoveryPlan.getRecovery_Goal().toString().toLowerCase().contains(keyword.toLowerCase())
                                    || recoveryPlan.getRecovery_Description().toLowerCase().contains(keyword.toLowerCase())
                                    || recoveryPlan.getRecovery_Status().toString().toLowerCase().contains(keyword.toLowerCase())
                                    || recoveryPlan.getInjury().getInjuryType().toString().toLowerCase().contains(keyword.toLowerCase()))
                            .collect(Collectors.toList())
            );
            tableView_id.setItems(filteredList);
        }
    }

    private boolean ascendingOrderStatus = true; // Toggle variable for sorting status

    private void sortRecoveryPlansByStatus() {
        Comparator<RecoveryPlan> comparator = Comparator.comparing(recoveryPlan -> recoveryPlan.getRecovery_Status().ordinal());

        // Reverse order if the toggle is false
        if (!ascendingOrderStatus) {
            comparator = comparator.reversed();
        }

        ObservableList<RecoveryPlan> sortedList = FXCollections.observableArrayList(
                recoveryPlanList.stream().sorted(comparator).collect(Collectors.toList())
        );

        tableView_id.setItems(sortedList);

        // Toggle order for next click
        ascendingOrderStatus = !ascendingOrderStatus;
    }



    // Update the recovery plan list
    private void updateRecoveryPlanList() {
        recoveryPlanList.clear();
        try {
            List<RecoveryPlan> recoveryPlans = recoveryPlanService.returnList();
            recoveryPlanList.addAll(recoveryPlans);
            tableView_id.refresh();
        } catch (Exception e) {
            showAlert("Error", "Failed to load recovery plan data: " + e.getMessage());
        }
    }

    // Show an alert message
    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // Open the update recovery plan screen
    private void openUpdateRecoveryPlanScreen(RecoveryPlan recoveryPlan) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/UpdateRecoveryPlan.fxml"));
            Parent root = loader.load();

            UpdateRecoveryPlanController controller = loader.getController();
            controller.initData(recoveryPlan);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Update Recovery Plan");
            stage.setOnHidden(event -> updateRecoveryPlanList());
            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to open update screen: " + e.getMessage());
        }
    }

    // Add action buttons (Update, Delete) to the table
    private void addActionButtonsToTable() {
        Action.setCellFactory(param -> new TableCell<>() {
            private final Button updateButton = new Button("Update");
            private final Button deleteButton = new Button("Delete");
            private final HBox pane = new HBox(updateButton, deleteButton);

            {
                updateButton.setStyle("-fx-background-color: #4CAF50; -fx-text-fill: white;");
                deleteButton.setStyle("-fx-background-color: #F44336; -fx-text-fill: white;");
                pane.setSpacing(5);

                updateButton.setOnAction(event -> {
                    RecoveryPlan recoveryPlan = getTableView().getItems().get(getIndex());
                    openUpdateRecoveryPlanScreen(recoveryPlan);
                });

                deleteButton.setOnAction(event -> {
                    RecoveryPlan recoveryPlan = getTableView().getItems().get(getIndex());
                    deleteRecoveryPlan(recoveryPlan);
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

    // Delete a recovery plan from the list
    private void deleteRecoveryPlan(RecoveryPlan recoveryPlan) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Delete Confirmation");
        alert.setHeaderText("Are you sure you want to delete this recovery plan?");
        alert.setContentText("This action cannot be undone.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    recoveryPlanService.delete(recoveryPlan.getRecovery_id());
                } catch (SQLException e) {
                    throw new RuntimeException(e);
                }
                recoveryPlanList.remove(recoveryPlan);
                showAlert("Success", "Recovery plan deleted successfully!");
            }
        });
    }

    // Switch to a new screen using a general method
    private void switchScreen(String fxmlPath, String title) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle(title);
            stage.setUserData(this);
            stage.show();

            Stage currentStage = (Stage) AddRecoveryPlanButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open screen: " + e.getMessage());
        }
    }

    // Switch to the screen for updating recovery plan
    private void switchScreenToUpdateRecoveryPlan() {
        RecoveryPlan selectedRecoveryPlan = tableView_id.getSelectionModel().getSelectedItem();
        if (selectedRecoveryPlan != null) {
            openUpdateRecoveryPlanScreen(selectedRecoveryPlan);
        } else {
            showAlert("Error", "Please select a recovery plan to update.");
        }
    }
}
