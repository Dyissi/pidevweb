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
import tn.esprit.services.teamServices;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.List;
import java.sql.Date;

public class addTournament {

    @FXML
    private Label addTeamLabel;

    @FXML
    private Label addTournLabel;

    @FXML
    private Button addTournbtn;

    @FXML
    private Label consultTeamLabel;

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
    private TableColumn<team, Integer> tabTeamWins;

    @FXML
    private DatePicker tournEndDate;

    @FXML
    private TextField tournLoc;

    @FXML
    private TextField tournName;

    @FXML
    private DatePicker tournStartDate;

    @FXML
    private ComboBox<String> tournTOS;

    @FXML
    private Label viewTournLabel;
    @FXML
    private Label backBtn;

    private final TournamentService tournamentService = new TournamentService();
    private final teamServices teamService = new teamServices();
    private final ObservableList<team> teamList = FXCollections.observableArrayList();
    private int selectedTeamsCount = 0; // Counter for added teams

    @FXML
    public void initialize() {
        addTournbtn.setOnAction(event -> addTournAction());
        viewTournLabel.setOnMouseClicked(event -> switchScreenConsult());
        backBtn.setOnMouseClicked(event -> switchBackToCoachFront());
        // Update event trigger: Filtering when ComboBox is clicked
        tournTOS.setOnAction(event -> filterTeamsBySport());


        // Set up table columns
        tabTeamId.setCellValueFactory(new PropertyValueFactory<>("teamId"));
        tabTeamName.setCellValueFactory(new PropertyValueFactory<>("teamName"));
        tabTeamNath.setCellValueFactory(new PropertyValueFactory<>("teamNath"));
        tabTeamWins.setCellValueFactory(new PropertyValueFactory<>("teamW"));
        tabTeamLosses.setCellValueFactory(new PropertyValueFactory<>("teamL"));
        tabTeamSport.setCellValueFactory(new PropertyValueFactory<>("teamTOS"));
        tabTeam.setItems(teamList);

        addActionButtonsToTable();
        viewTournLabel.setOnMouseClicked(event -> switchScreenConsult());

    }


    private void addTournAction() {
        if (!validateInputs()) return;
        try {
            String name = tournName.getText();
            String location = tournLoc.getText();
            LocalDate startDate = tournStartDate.getValue();
            LocalDate endDate = tournEndDate.getValue();
            String sport = tournTOS.getValue();

            tournament currentTournament = new tournament(name, Date.valueOf(startDate), Date.valueOf(endDate), location, sport, selectedTeamsCount);
            tournamentService.add(currentTournament);
            showAlert("Success", "Tournament added successfully!");

        } catch (SQLException e) {
            showAlert("Error", "Failed to add tournament: " + e.getMessage());
        }
    }

    private void filterTeamsBySport() {
        teamList.clear();
        String selectedSport = tournTOS.getValue();

        if (selectedSport != null) {
            try {
                List<team> teams = teamService.returnListBySport(selectedSport);
                teamList.setAll(teams);  // Ensures all teams are added at once
                tabTeam.setItems(teamList); // Explicitly setting items again
                tabTeam.refresh();
            } catch (SQLException e) {
                showAlert("Error", "Failed to load teams: " + e.getMessage());
            }
        }
    }


    private void addActionButtonsToTable() {
        tabTeamActions.setCellFactory(param -> new TableCell<>() {
            private final Button addButton = new Button("Add");
            private final HBox pane = new HBox(addButton);

            {
                addButton.setStyle("-fx-background-color: #4CAF50; -fx-text-fill: white;");
                addButton.setOnAction(event -> {
                    team selectedTeam = getTableView().getItems().get(getIndex());
                    addTeamToTournament(selectedTeam);
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

    private void addTeamToTournament(team selectedTeam) {
        selectedTeamsCount++; // Increment the counter instead of calling DB update
        showAlert("Success", selectedTeam.getTeamName() + " team added to tournament!");
    }

    private void switchScreenConsult() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/viewTournament.fxml"));
            Parent root = loader.load();

            viewTournament controller = loader.getController();
            controller.initialize();
            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("viewing tournaments");
            stage.setUserData(this);
            stage.show();
            Stage currentStage = (Stage) viewTournLabel.getScene().getWindow();
            currentStage.close();
        } catch (IOException e) {
            showAlert("Error", "Failed to open view tournament screen: " + e.getMessage());
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
            showAlert("Error", "Failed to open consult screen: " + e.getMessage());
        }
    }
    private boolean validateInputs() {
        String name = tournName.getText();
        String location = tournLoc.getText();
        LocalDate startDate = tournStartDate.getValue();
        LocalDate endDate = tournEndDate.getValue();
        String sport = tournTOS.getValue();

        // Check if tournament name contains only letters and spaces
        if (name == null || !name.matches("^[A-Za-z ]+$")) {
            showAlert("Validation Error", "Tournament name should only contain letters and spaces.");
            return false;
        }

        // Check if location is not null or empty
        if (location == null || location.trim().isEmpty()) {
            showAlert("Validation Error", "Tournament location cannot be empty.");
            return false;
        }

        // Check if start date is not before today
        LocalDate today = LocalDate.now();
        if (startDate == null || startDate.isBefore(today)) {
            showAlert("Validation Error", "Tournament start date cannot be before today's date.");
            return false;
        }

        // Check if end date is not before start date
        if (endDate == null || endDate.isBefore(startDate)) {
            showAlert("Validation Error", "Tournament end date cannot be before the start date.");
            return false;
        }

        // Check if type of sport is selected
        if (sport == null || sport.trim().isEmpty()) {
            showAlert("Validation Error", "Please select a type of sport.");
            return false;
        }

        return true;
    }


    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
