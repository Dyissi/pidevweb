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
import tn.esprit.entities.Coach;
import tn.esprit.services.UserServices;

import java.io.IOException;
import java.sql.SQLException;
import java.util.List;

public class ListCoach {
    @FXML
    private Label addcoach_id;
    @FXML
    private TableView<Coach> tableView_id;
    @FXML
    private TableColumn<Coach, Integer> id;
    @FXML
    private TableColumn<Coach, String> fname_id;
    @FXML
    private TableColumn<Coach, String> lname_id;
    @FXML
    private TableColumn<Coach, String> email_id;
    @FXML
    private TableColumn<Coach, String> phonenb_id;
    @FXML
    private TableColumn<Coach, Integer> nbteams_id;
    @FXML
    private TableColumn<Coach, String> pwd_id;
    @FXML
    private TableColumn<Coach, Void> action_id;

    private final UserServices userService = new UserServices();
    private final ObservableList<Coach> coachList = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        id.setCellValueFactory(new PropertyValueFactory<>("user_id"));
        fname_id.setCellValueFactory(new PropertyValueFactory<>("user_fname"));
        lname_id.setCellValueFactory(new PropertyValueFactory<>("user_lname"));
        email_id.setCellValueFactory(new PropertyValueFactory<>("user_email"));
        phonenb_id.setCellValueFactory(new PropertyValueFactory<>("user_nbr"));
        nbteams_id.setCellValueFactory(new PropertyValueFactory<>("nb_teams"));
        pwd_id.setCellValueFactory(new PropertyValueFactory<>("user_pwd"));

        tableView_id.setItems(coachList);
        updateCoachList();
        addActionButtonsToTable();

        addcoach_id.setOnMouseClicked(event -> switchScreenConsult());

    }

    void updateCoachList() {
        coachList.clear();
        try {
            List<Coach> coaches = userService.getAll().stream()
                    .filter(user -> user instanceof Coach)
                    .map(user -> (Coach) user)
                    .toList();
            coachList.addAll(coaches);
            tableView_id.refresh();
        } catch (Exception e) {
            showAlert("Error", "Failed to load coach data: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void openUpdateCoachScreen(Coach coach) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/updateCoach.fxml"));
            Parent root = loader.load();

            updateCoach controller = loader.getController();
            controller.initData(coach);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Update Coach");
            stage.setOnHidden(event -> updateCoachList());
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
                    Coach coach = getTableView().getItems().get(getIndex());
                    openUpdateCoachScreen(coach);
                });

                deleteButton.setOnAction(event -> {
                    Coach coach = getTableView().getItems().get(getIndex());
                    deleteCoach(coach);
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

    private void deleteCoach(Coach coach) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Delete Confirmation");
        alert.setHeaderText("Are you sure you want to delete this coach?");
        alert.setContentText("This action cannot be undone.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                userService.delete(coach.getUser_id());
                coachList.remove(coach);
                showAlert("Success", "Coach deleted successfully!");
            }
        });
    }
    private void switchScreenConsult() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addUser.fxml"));
            Parent root = loader.load();

            addUser controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Consult List of Coaches");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) addcoach_id.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
}
}
}
