package tn.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;
import tn.esprit.entities.team;
import tn.esprit.entities.tournament;
import tn.esprit.services.TournamentService;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.regex.Pattern;

public class updateTournament {

    @FXML
    private Label addTeamLabel;

    @FXML
    private Label addTournLabel;

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
    private Button updateTournbtn;

    @FXML
    private Label viewTournLabel;


    private tournament selectedTournament;
    private final TournamentService tournamentService = new TournamentService();

    public void initData(tournament tournament) {
        updateTournbtn.setOnAction(event -> updateTournamentAction());
        this.selectedTournament = tournament;
        tournName.setText(tournament.getTournamentName());
        tournStartDate.setValue(tournament.getTournamentStartDate().toLocalDate());
        tournEndDate.setValue(tournament.getTournamentEndDate().toLocalDate());
        tournLoc.setText(tournament.getTournamentLocation());
        tournTOS.setValue(tournament.getTournamentTOS());


    }
    @FXML
    private void updateTournamentAction() {
        if (!validateInputs()) {
            return;
        }
        selectedTournament.setTournamentName(tournName.getText());
        selectedTournament.setTournamentStartDate(java.sql.Date.valueOf(tournStartDate.getValue()));
        selectedTournament.setTournamentEndDate(java.sql.Date.valueOf(tournEndDate.getValue()));
        selectedTournament.setTournamentLocation(tournLoc.getText());
        selectedTournament.setTournamentTOS(tournTOS.getValue());
        selectedTournament.setTournamentNbteams(selectedTournament.getTournamentNbteams());
        try {
            tournamentService.update(selectedTournament.getTournamentId(), selectedTournament);
            System.out.println("Tournament updated successfully!");

            // Close the update window
            updateTournbtn.getScene().getWindow().hide();

        } catch (SQLException e) {
            System.err.println("Failed to update tournament: " + e.getMessage());
        }
    }



    private void showAlert(String title, String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(title);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void switchScreenAdd() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/addTournament.fxml"));
            Parent root = loader.load();

            addTeam controller = loader.getController();
            controller.initialize();

            Stage stage = new Stage();
            stage.setScene(new Scene(root));
            stage.setTitle("Add Tournament");
            stage.setUserData(this);
            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to open add screen: " + e.getMessage());
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

}
