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
import tn.esprit.entities.team;
import tn.esprit.services.teamServices;

import java.io.IOException;
import java.sql.SQLException;
import java.util.List;

public class consultTeam {

    @FXML
    private TableView<team> tabTeam;

    @FXML
    private TableColumn<team, Void> tabTeamActions;

    @FXML
    private TableColumn<team, Integer> tabTeamId;

    @FXML
    private TableColumn<team, Integer> tabTeamLosses;

    @FXML
    private TableColumn<team, String> tabTeamName;

    @FXML
    private TableColumn<team, Integer> tabTeamNath;

    @FXML
    private TableColumn<team, String> tabTeamSport;
    @FXML
    private Label backBtn;
    @FXML
    private Label addTeamLabelC;


    @FXML
    private TableColumn<team, Integer> tabTeamWins;
    private final teamServices teamService = new teamServices();
    private final ObservableList<team> teamList = FXCollections.observableArrayList();
    @FXML
    public void initialize() {
        addTeamLabelC.setOnMouseClicked(event -> switchScreenAddTeam());
        backBtn.setOnMouseClicked(event -> switchBackToCoachFront());
        tabTeamId.setCellValueFactory(new PropertyValueFactory<>("teamId"));
        tabTeamName.setCellValueFactory(new PropertyValueFactory<>("teamName"));
        tabTeamNath.setCellValueFactory(new PropertyValueFactory<>("teamNath"));
        tabTeamSport.setCellValueFactory(new PropertyValueFactory<>("teamTOS"));
        tabTeamWins.setCellValueFactory(new PropertyValueFactory<>("teamW"));
        tabTeamLosses.setCellValueFactory(new PropertyValueFactory<>("teamL"));
        tabTeam.setItems(teamList);
        updateteamList();
        addActionButtonsToTable();


    }
    private void switchBackToCoachFront() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/Coachfront.fxml"));
            Parent root = loader.load();

            Coachfront controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Consult Team");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) backBtn.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
        }
    }
    void updateteamList() {
        teamList.clear();
        try {
            List<team> teams = teamService.returnList();
            teamList.addAll(teams);
            tabTeam.refresh();
        } catch (SQLException e) {
            showAlert("Error", "Failed to load team data: " + e.getMessage());
        }
    }

    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void switchScreenAddTeam() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addTeam.fxml"));
            Parent root = loader.load();

            addTeam controller = loader.getController();
            controller.initialize();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Consult Team");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) addTeamLabelC.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
        }
    }

    private void openUpdateTeamScreen(team team) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/updateTeam.fxml"));
            Parent root = loader.load();

            updateTeam controller = loader.getController();
            controller.initData(team);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Update Team");

            // Listen for window close and refresh the table
            stage.setOnHidden(event -> updateteamList());

            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to open update screen: " + e.getMessage());
        }
    }

    private void addActionButtonsToTable() {
        tabTeamActions.setCellFactory(param -> new TableCell<>() {
            private final Button updateButton = new Button("Update");
            private final Button deleteButton = new Button("Delete");
            private final HBox pane = new HBox(updateButton, deleteButton);

            {
                updateButton.setStyle("-fx-background-color: #BCCCE0; -fx-text-fill: white;");
                deleteButton.setStyle("-fx-background-color: #D68C45; -fx-text-fill: white;");
                pane.setSpacing(5);

                // Update Button Action
                updateButton.setOnAction(event -> {
                    team team = (team) getTableView().getItems().get(getIndex());
                    openUpdateTeamScreen(team);
                });

                // Delete Button Action
                deleteButton.setOnAction(event -> {
                    team team = (team) getTableView().getItems().get(getIndex());
                    deleteTeam(team);
                    System.out.println("Delete button clicked for team: " + team.getTeamName());
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
    private void deleteTeam(team team) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Delete Confirmation");
        alert.setHeaderText("Are you sure you want to delete this record?");
        alert.setContentText("This action cannot be undone.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    teamService.delete(team.getTeamId());
                    teamList.remove(team);
                    showAlert("Success", "Performance record deleted successfully!");
                } catch (SQLException e) {
                    showAlert("Error", "Failed to delete performance: " + e.getMessage());
                }
            }
        });
    }
}