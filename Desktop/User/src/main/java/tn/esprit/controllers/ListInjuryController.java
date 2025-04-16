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
import tn.esprit.entities.Injury;
import tn.esprit.services.InjuryServices;
import java.util.Comparator;
import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.Comparator;
import java.util.List;
import java.util.stream.Collectors;
import java.util.stream.Collectors;

public class ListInjuryController {

    @FXML
    public Button UpdateInjuryButton;
    @FXML
    public Button BackButton;
    @FXML
    private TableColumn<Injury, Injury> Injuryid;
    @FXML
    private TableColumn<Injury, String> user_fname;
    @FXML
    private TableColumn<Injury, String> user_lname;
    @FXML
    private TableColumn<Injury, String> InjuryType;
    @FXML
    private TableColumn<Injury, String> Severity;
    @FXML
    private TableColumn<Injury, LocalDate> InjuryDate;
    @FXML
    private TableColumn<Injury, String> Description;
    @FXML
    private TableColumn<Injury, Void> action_id;
    @FXML
    private Button AddInjuryButton;
    @FXML
    private TableView<Injury> tableView_id;
    @FXML
    private TextField searchField;
    @FXML
    private Button sortBySeverityButton;

    private final InjuryServices injuryService = new InjuryServices();
    private final ObservableList<Injury> injuryList = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        // Initialize table columns
        Injuryid.setCellValueFactory(new PropertyValueFactory<>("injury_id"));
        user_fname.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getUser().getUser_fname()));
        user_lname.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getUser().getUser_lname()));
        InjuryType.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getInjuryType().toString()));
        Severity.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getInjury_severity().toString()));
        InjuryDate.setCellValueFactory(new PropertyValueFactory<>("injuryDate"));
        Description.setCellValueFactory(new PropertyValueFactory<>("injury_description"));

        tableView_id.setItems(injuryList);
        updateInjuryList();
        addActionButtonsToTable();
        AddInjuryButton.setOnMouseClicked(event -> switchScreenToAddInjury());
        UpdateInjuryButton.setOnMouseClicked(event -> switchScreenToUpdateInjury());
        BackButton.setOnMouseClicked(event -> switchScreenToBack());

        // Set up search functionality
        searchField.textProperty().addListener((observable, oldValue, newValue) -> filterInjuries(newValue));

        // Set up sorting functionality
        sortBySeverityButton.setOnMouseClicked(event -> sortInjuriesBySeverity());
    }

    private void filterInjuries(String keyword) {
        if (keyword == null || keyword.trim().isEmpty()) {
            tableView_id.setItems(injuryList);
        } else {
            ObservableList<Injury> filteredList = FXCollections.observableArrayList(
                    injuryList.stream()
                            .filter(injury -> injury.getUser().getUser_fname().toLowerCase().contains(keyword.toLowerCase())
                                    || injury.getUser().getUser_lname().toLowerCase().contains(keyword.toLowerCase())
                                    || injury.getInjuryType().toString().toLowerCase().contains(keyword.toLowerCase())
                                    || injury.getInjury_severity().toString().toLowerCase().contains(keyword.toLowerCase()))
                            .collect(Collectors.toList())
            );
            tableView_id.setItems(filteredList);
        }
    }

    private boolean ascendingOrder = true; // Toggle variable

    private void sortInjuriesBySeverity() {
        Comparator<Injury> comparator = Comparator.comparing(injury -> injury.getInjury_severity().ordinal());

        // Reverse order if the toggle is false
        if (!ascendingOrder) {
            comparator = comparator.reversed();
        }

        ObservableList<Injury> sortedList = FXCollections.observableArrayList(
                injuryList.stream().sorted(comparator).collect(Collectors.toList())
        );

        tableView_id.setItems(sortedList);

        // Toggle order for next click
        ascendingOrder = !ascendingOrder;
    }


    private void updateInjuryList() {
        injuryList.clear();
        try {
            List<Injury> injuries = injuryService.returnList();
            injuryList.addAll(injuries);
            tableView_id.refresh();
        } catch (Exception e) {
            showAlert("Error", "Failed to load injury data: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // Open the update injury screen
    private void openUpdateInjuryScreen(Injury injury) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/UpdateAthleteInjury.fxml"));
            Parent root = loader.load();

            UpdateAthleteInjuryController controller = loader.getController();
            controller.initData(injury);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Update Injury");
            stage.setOnHidden(event -> updateInjuryList());
            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to open update screen: " + e.getMessage());
        }
    }

    // Add action buttons (Update, Delete) to the table
    private void addActionButtonsToTable() {
        action_id.setCellFactory(param -> new TableCell<>() {
            private final Button updateButton = new Button("Update");
            private final Button deleteButton = new Button("Delete");
            private final HBox pane = new HBox(updateButton, deleteButton);

            {
                updateButton.setStyle("-fx-background-color: #4CAF50; -fx-text-fill: white;");
                deleteButton.setStyle("-fx-background-color: #F44336; -fx-text-fill: white;");
                pane.setSpacing(5);

                updateButton.setOnAction(event -> {
                    Injury injury = getTableView().getItems().get(getIndex());
                    openUpdateInjuryScreen(injury);
                });

                deleteButton.setOnAction(event -> {
                    Injury injury = getTableView().getItems().get(getIndex());
                    deleteInjury(injury);
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

    // Delete an injury from the list
    private void deleteInjury(Injury injury) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Delete Confirmation");
        alert.setHeaderText("Are you sure you want to delete this injury?");
        alert.setContentText("This action cannot be undone.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    injuryService.delete(injury.getInjury_id());
                } catch (SQLException e) {
                    throw new RuntimeException(e);
                }
                injuryList.remove(injury);
                showAlert("Success", "Injury deleted successfully!");
            }
        });
    }

    // Switch to the screen for adding a new injury
    private void switchScreenToAddInjury() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/AddAthleteInjury.fxml"));
            Parent root = loader.load();

            AddAthleteInjuryController controller = loader.getController();
            controller.initialize();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Add New Injury");
            stage.setUserData(this);
            stage.show();

            Stage currentStage = (Stage) AddInjuryButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open add injury screen: " + e.getMessage());
        }
    }

    // Switch to the screen for updating injury
    private void switchScreenToUpdateInjury() {
        Injury selectedInjury = tableView_id.getSelectionModel().getSelectedItem();
        if (selectedInjury != null) {
            try {
                FXMLLoader loader = new FXMLLoader(getClass().getResource("/UpdateAthleteInjury.fxml"));
                Parent root = loader.load();

                UpdateAthleteInjuryController controller = loader.getController();
                controller.initData(selectedInjury);  // Pass the selected injury to the controller

                Stage stage = new Stage();
                stage.setScene(new Scene(root));
                stage.setTitle("Update Injury");
                stage.show();

                // Close the current window
                Stage currentStage = (Stage) UpdateInjuryButton.getScene().getWindow();
                currentStage.close();
            } catch (IOException e) {
                showAlert("Error", "Failed to open update injury screen: " + e.getMessage());
            }
        } else {
            showAlert("Error", "Please select an injury to update.");
        }
    }

    private void switchScreenToBack() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/Medicalfront.fxml"));
            Parent root = loader.load();

            Medicalfront controller = loader.getController();
            controller.initialize();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Back");
            stage.setUserData(this);
            stage.show();

            Stage currentStage = (Stage) BackButton.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open add injury screen: " + e.getMessage());
        }
    }

}
