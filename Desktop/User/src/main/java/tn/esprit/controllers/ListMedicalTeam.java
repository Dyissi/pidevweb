package tn.esprit.controllers;

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
import tn.esprit.entities.Medical_staff;
import tn.esprit.services.UserServices;

import java.io.IOException;
import java.util.List;

public class ListMedicalTeam {
    @FXML
    private TableColumn<Medical_staff, Void> action_id;

    @FXML
    private TableColumn<Medical_staff, String> email_id;

    @FXML
    private TableColumn<Medical_staff, String> fname_id;

    @FXML
    private TableColumn<Medical_staff, Integer> id;

    @FXML
    private TableColumn<Medical_staff, String> lname_id;

    @FXML
    private TableColumn<Medical_staff, String> phonenb_id;

    @FXML
    private TableColumn<Medical_staff, String> pwd_id;

    @FXML
    private TableColumn<Medical_staff, String> specialty_id;

    @FXML
    private TableView<Medical_staff> tableView_id;

    @FXML
    private Label addcoach_id;


    private final UserServices userService = new UserServices();
    private final ObservableList<Medical_staff> medical_staffList = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        id.setCellValueFactory(new PropertyValueFactory<>("user_id"));
        fname_id.setCellValueFactory(new PropertyValueFactory<>("user_fname"));
        lname_id.setCellValueFactory(new PropertyValueFactory<>("user_lname"));
        email_id.setCellValueFactory(new PropertyValueFactory<>("user_email"));
        phonenb_id.setCellValueFactory(new PropertyValueFactory<>("user_nbr"));
        pwd_id.setCellValueFactory(new PropertyValueFactory<>("user_pwd"));
        specialty_id.setCellValueFactory(new PropertyValueFactory<>("speciality"));


        tableView_id.setItems(medical_staffList);
        updateMedicalteamList();
        addActionButtonsToTable();
        addcoach_id.setOnMouseClicked(event -> switchScreenToaddMed());



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
            Stage currentStage = (Stage) addcoach_id.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
        }
    }
    void updateMedicalteamList() {
        medical_staffList.clear();
        try {
            List<Medical_staff> staff = userService.getAll().stream()
                    .filter(user -> user instanceof Medical_staff)
                    .map(user -> (Medical_staff) user)
                    .toList();
            System.out.println("Fetched medical staff: " + staff.size()); // Debug print

            medical_staffList.addAll(staff);
            tableView_id.refresh();
        } catch (Exception e) {
            showAlert("Error", "Failed to load Medical Staff  data: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void openUpdateStaffScreen(Medical_staff staff) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/updateMedicalteam.fxml"));
            Parent root = loader.load();

            updateMedicalteam controller = loader.getController();
            controller.initData(staff);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Update Medical Staff");
            stage.setOnHidden(event -> updateMedicalteamList());
            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to open update screen: " + e.getMessage());
        }
    }

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
                    Medical_staff staff = getTableView().getItems().get(getIndex());
                    openUpdateStaffScreen(staff);
                });

                deleteButton.setOnAction(event -> {
                    Medical_staff staff = getTableView().getItems().get(getIndex());
                    deleteStaff(staff);
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

    private void deleteStaff(Medical_staff staff) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Delete Confirmation");
        alert.setHeaderText("Are you sure you want to delete this Medical Staff?");
        alert.setContentText("This action cannot be undone.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                userService.delete(staff.getUser_id());
                medical_staffList.remove(staff);
                    showAlert("Success", "Medical Staff deleted successfully!");
            }
        });
    }
    /*private void switchScreenConsult() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addMedicalteam.fxml"));
            Parent root = loader.load();

            addUser controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Consult List of Coaches");
            stage.setUserData(this);
            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
        }
    }*/
}
