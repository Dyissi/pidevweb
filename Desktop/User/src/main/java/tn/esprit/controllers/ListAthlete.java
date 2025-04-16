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
import tn.esprit.entities.Athlete;
import tn.esprit.services.UserServices;

import java.io.IOException;
import java.util.Date;
import java.util.List;

public class ListAthlete {
    @FXML
    private TableColumn<Athlete, Date> DoB_id;

    @FXML
    private TableColumn<Athlete, Void> action_id;

    @FXML
    private Label addcoach_id;

    @FXML
    private TableColumn<Athlete, String> address_id;

    @FXML
    private TableColumn<Athlete, String> email_id;

    @FXML
    private TableColumn<Athlete, String> fname_id;

    @FXML
    private TableColumn<Athlete, String> gender_id;

    @FXML
    private TableColumn<Athlete, Float> height_id;

    @FXML
    private TableColumn<Athlete, Integer> id;

    @FXML
    private TableColumn<Athlete, Integer> injury_id;

    @FXML
    private TableColumn<Athlete, String> lname_id;

    @FXML
    private TableColumn<Athlete, String> phonenb_id;

    @FXML
    private TableColumn<Athlete, String> pwd_id;

    @FXML
    private TableView<Athlete> tableView_id;

    @FXML
    private TableColumn<Athlete, Float> weight_id;

    private final UserServices userService = new UserServices();
    private final ObservableList<Athlete> athleteList = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        id.setCellValueFactory(new PropertyValueFactory<>("user_id"));
        fname_id.setCellValueFactory(new PropertyValueFactory<>("user_fname"));
        lname_id.setCellValueFactory(new PropertyValueFactory<>("user_lname"));
        email_id.setCellValueFactory(new PropertyValueFactory<>("user_email"));

        pwd_id.setCellValueFactory(new PropertyValueFactory<>("user_pwd"));
        phonenb_id.setCellValueFactory(new PropertyValueFactory<>("user_nbr"));
        address_id.setCellValueFactory(new PropertyValueFactory<>("athlete_address"));
        DoB_id.setCellValueFactory(new PropertyValueFactory<>("athlete_DoB"));
        gender_id.setCellValueFactory(new PropertyValueFactory<>("athlete_gender"));
        height_id.setCellValueFactory(new PropertyValueFactory<>("athlete_height"));
        weight_id.setCellValueFactory(new PropertyValueFactory<>("athlete_weight"));
        injury_id.setCellValueFactory(new PropertyValueFactory<>("isInjured"));

        tableView_id.setItems(athleteList);
        updateAthleteList();
        addActionButtonsToTable();
        addcoach_id.setOnMouseClicked(event -> switchScreenToAddAthlete());
    }

    void updateAthleteList() {
        athleteList.clear();
        try {
            List<Athlete> athletes = userService.getAll().stream()
                    .filter(user -> user instanceof Athlete)
                    .map(user -> (Athlete) user)
                    .toList();
            athleteList.addAll(athletes);
            tableView_id.refresh();
        } catch (Exception e) {
            showAlert("Error", "Failed to load athlete data: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void openUpdateAthleteScreen(Athlete athlete) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/updateAthlete.fxml"));
            Parent root = loader.load();

            updateAthlete controller = loader.getController();
            controller.initData(athlete);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Update Athlete");
            stage.setOnHidden(event -> updateAthleteList());
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
                    Athlete athlete = getTableView().getItems().get(getIndex());
                    openUpdateAthleteScreen(athlete);
                });

                deleteButton.setOnAction(event -> {
                    Athlete athlete = getTableView().getItems().get(getIndex());
                    deleteAthlete(athlete);
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

    private void deleteAthlete(Athlete athlete) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Delete Confirmation");
        alert.setHeaderText("Are you sure you want to delete this athlete?");
        alert.setContentText("This action cannot be undone.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                userService.delete(athlete.getUser_id());
                athleteList.remove(athlete);
                showAlert("Success", "athlete deleted successfully!");
            }
        });
    }
    private void switchScreenToAddAthlete() {


        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/AddAthlete.fxml"));
            Parent root = loader.load();

            addAthlete controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Adding Tournament");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) addcoach_id.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open tournament screen: " + e.getMessage());
        }
    }
}