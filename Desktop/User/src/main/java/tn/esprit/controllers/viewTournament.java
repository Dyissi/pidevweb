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
import tn.esprit.entities.tournament;
import tn.esprit.services.TournamentService;

import java.io.IOException;
import java.sql.Date;
import java.sql.SQLException;
import java.util.List;

public class viewTournament {

    @FXML
    private TableView<tournament> tabTourn;
    @FXML
    private TableColumn<tournament, Integer> tabTournid;
    @FXML
    private TableColumn<tournament, String> tabTournName;
    @FXML
    private TableColumn<tournament, String> tabTournLoc;
    @FXML
    private TableColumn<tournament, Date> tabTournSdate;
    @FXML
    private TableColumn<tournament, Date> tabTournEdate;
    @FXML
    private TableColumn<tournament, String> tabTournSport;
    @FXML
    private TableColumn<tournament, Integer> tabTournTeams;
    @FXML
    private TableColumn<tournament, Void> tabTournActions;
    @FXML
    private Label backBtn;
    @FXML
    private Label addTournLabelC;

    private final TournamentService tournamentService = new TournamentService();
    private final ObservableList<tournament> tournamentList = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        backBtn.setOnMouseClicked(event -> switchBackToCoachFront());
        addTournLabelC.setOnMouseClicked(event -> switchScreenAddTournament());

        tabTournid.setCellValueFactory(new PropertyValueFactory<>("tournamentId"));
        tabTournName.setCellValueFactory(new PropertyValueFactory<>("tournamentName"));
        tabTournLoc.setCellValueFactory(new PropertyValueFactory<>("tournamentLocation"));
        tabTournSdate.setCellValueFactory(new PropertyValueFactory<>("tournamentStartDate"));
        tabTournEdate.setCellValueFactory(new PropertyValueFactory<>("tournamentEndDate"));
        tabTournSport.setCellValueFactory(new PropertyValueFactory<>("tournamentTOS"));
        tabTournTeams.setCellValueFactory(new PropertyValueFactory<>("tournamentNbteams"));
        tabTourn.setItems(tournamentList);

        updatetournamentList();
        addActionButtonsToTable();
    }

    void updatetournamentList() {
        tournamentList.clear();
        try {
            List<tournament> tournaments = tournamentService.returnList();
            tournamentList.addAll(tournaments);
            tabTourn.refresh();
        } catch (SQLException e) {
            showAlert("Error", "Failed to load tournament data: " + e.getMessage());
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
            showAlert("Error", "Failed to open Coach screen: " + e.getMessage());
        }
    }
    private void switchScreenAddTournament() {
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
            Stage currentStage = (Stage) addTournLabelC.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open add tournament screen: " + e.getMessage());
        }
    }
    private void openUpdateTournamentScreen(tournament tournament) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/updateTournament.fxml"));
            Parent root = loader.load();

            updateTournament controller = loader.getController();
            controller.initData(tournament);

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Update Tournament");

            // Listen for window close and refresh the table
            stage.setOnHidden(event -> updatetournamentList());

            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to open update screen: " + e.getMessage());
        }
    }

    private void addActionButtonsToTable() {
        tabTournActions.setCellFactory(param -> new TableCell<>() {
            private final Button updateButton = new Button("Update");
            private final Button deleteButton = new Button("Delete");
            private final HBox pane = new HBox(updateButton, deleteButton);

            {
                updateButton.setStyle("-fx-background-color: #BCCCE0; -fx-text-fill: white;");
                deleteButton.setStyle("-fx-background-color: #D68C45; -fx-text-fill: white;");
                pane.setSpacing(5);

                // Update Button Action
                updateButton.setOnAction(event -> {
                    tournament tournament = (tournament) getTableView().getItems().get(getIndex());
                    openUpdateTournamentScreen(tournament);
                });

                // Delete Button Action
                deleteButton.setOnAction(event -> {
                    tournament tournament = (tournament) getTableView().getItems().get(getIndex());
                    deleteTournament(tournament);
                    System.out.println("Delete button clicked for team: " + tournament.getTournamentId());
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

    private void deleteTournament(tournament tournament) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION, "Are you sure you want to delete this record?", ButtonType.OK, ButtonType.CANCEL);
        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                try {
                    tournamentService.delete(tournament.getTournamentId());
                    tournamentList.remove(tournament);
                    showAlert("Success", "Tournament deleted successfully!");
                } catch (SQLException e) {
                    showAlert("Error", "Failed to delete tournament: " + e.getMessage());
                }
            }
        });
    }
    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
