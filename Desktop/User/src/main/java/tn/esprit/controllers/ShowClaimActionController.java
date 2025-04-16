package tn.esprit.controllers;

import javafx.beans.property.SimpleIntegerProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.HBox;
import javafx.stage.Stage;
import tn.esprit.entities.ClaimAction;
import tn.esprit.services.ClaimActionServices;

import javafx.util.Callback;
import java.io.IOException;
import java.sql.SQLException;

public class ShowClaimActionController {

    @FXML
    private TableView<ClaimAction> claimActionsTable;

    @FXML
    private TableColumn<ClaimAction, Integer> claimActionIdColumn;

    @FXML
    private TableColumn<ClaimAction, Integer> claimIdColumn;

    @FXML
    private TableColumn<ClaimAction, String> claimActionTypeColumn;

    @FXML
    private TableColumn<ClaimAction, String> claimActionStartDateColumn;

    @FXML
    private TableColumn<ClaimAction, String> claimActionEndDateColumn;

    @FXML
    private TableColumn<ClaimAction, String> claimActionNotesColumn;

    @FXML
    private TableColumn<ClaimAction, Void> actionsColumn;

    private ClaimActionServices claimActionServices;
    private ObservableList<ClaimAction> claimActionList;

    @FXML
    private Label claimsButton;
    @FXML
    private Label claimActionsButton;

    @FXML
    public void initialize() {
        // Ensure actionsColumn is not null
        assert actionsColumn != null : "fx:id=\"actionsColumn\" was not injected: check your FXML file 'ManageClaimActionsInterface.fxml'.";

        claimActionServices = new ClaimActionServices();
        claimActionList = FXCollections.observableArrayList();

        // Set up the table columns
        claimActionIdColumn.setCellValueFactory(cellData -> new SimpleIntegerProperty(cellData.getValue().getClaimActionId()).asObject());
        claimIdColumn.setCellValueFactory(cellData -> new SimpleIntegerProperty(cellData.getValue().getClaim().getClaimId()).asObject());
        claimActionTypeColumn.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getClaimActionType().toString()));
        claimActionStartDateColumn.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getClaimActionStartDate().toString()));
        claimActionEndDateColumn.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getClaimActionEndDate().toString()));
        claimActionNotesColumn.setCellValueFactory(cellData -> new SimpleStringProperty(cellData.getValue().getClaimActionNotes()));

        claimsButton.setOnMouseClicked((MouseEvent event) -> {
            switchToClaimsScreen(event);
        });
        claimActionsButton.setOnMouseClicked((MouseEvent event) -> {
            switchToClaimActionsScreen(event);
        });
        // Initialize actions column
        actionsColumn.setCellFactory(new Callback<TableColumn<ClaimAction, Void>, TableCell<ClaimAction, Void>>() {
            @Override
            public TableCell<ClaimAction, Void> call(TableColumn<ClaimAction, Void> param) {
                return new TableCell<ClaimAction, Void>() {
                    private final Button updateButton = new Button("Update");
                    private final Button deleteButton = new Button("Delete");

                    {
                        updateButton.setOnAction(event -> {
                            ClaimAction selectedClaimAction = getTableView().getItems().get(getIndex());
                            updateClaimAction(selectedClaimAction);
                        });

                        deleteButton.setOnAction(event -> {
                            ClaimAction selectedClaimAction = getTableView().getItems().get(getIndex());
                            deleteClaimAction(selectedClaimAction);
                        });

                        HBox hBox = new HBox(updateButton, deleteButton);
                        hBox.setSpacing(10);
                        setGraphic(hBox);
                    }

                    @Override
                    protected void updateItem(Void item, boolean empty) {
                        super.updateItem(item, empty);
                        if (empty) {
                            setGraphic(null);
                        } else {
                            setGraphic(getGraphic());
                        }
                    }
                };
            }
        });

        // Load claim actions from the database
        loadClaimActions();
    }

    private void loadClaimActions() {
        try {
            claimActionList.clear();
            claimActionList.addAll(claimActionServices.returnList());
            claimActionsTable.setItems(claimActionList);
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to load claim actions.");
        }
    }

    private void updateClaimAction(ClaimAction selectedClaimAction) {
        if (selectedClaimAction != null) {
            openUpdateClaimActionInterface(selectedClaimAction);
        } else {
            showAlert(Alert.AlertType.WARNING, "Selection Error", "Please select a claim action to update.");
        }
    }

    private void openUpdateClaimActionInterface(ClaimAction selectedClaimAction) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/UpdateClaimActionInterface.fxml"));
            Parent updateClaimActionRoot = loader.load();

            UpdateClaimActionController updateClaimActionController = loader.getController();
            updateClaimActionController.setClaimActionData(selectedClaimAction);
            updateClaimActionController.setShowClaimActionController(this);

            Stage updateClaimActionStage = new Stage();
            updateClaimActionStage.setTitle("Update Claim Action");
            updateClaimActionStage.setScene(new Scene(updateClaimActionRoot));
            updateClaimActionStage.show();

        } catch (IOException e) {
            e.printStackTrace();
            showAlert(Alert.AlertType.ERROR, "Error", "Failed to open Update Claim Action interface.");
        }
    }

    private void deleteClaimAction(ClaimAction selectedClaimAction) {
        if (selectedClaimAction != null) {
            try {
                claimActionServices.delete(selectedClaimAction);
                loadClaimActions(); // Refresh the list after deletion
                claimActionsTable.refresh(); // Force the table to refresh its cells
            } catch (SQLException e) {
                showAlert(Alert.AlertType.ERROR, "Database Error", "Failed to delete claim action.");
            }
        } else {
            showAlert(Alert.AlertType.WARNING, "Selection Error", "Please select a claim action to delete.");
        }
    }

    private void showAlert(Alert.AlertType alertType, String title, String content) {
        Alert alert = new Alert(alertType);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }

    public void refreshTable() {
        loadClaimActions(); // Reload the claim actions from the database
        claimActionsTable.refresh(); // Refresh the table view
    }

    private void switchToClaimsScreen(MouseEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ManageClaimsInterface.fxml"));
            Parent root = loader.load();
            Scene scene = new Scene(root);
            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            stage.setScene(scene);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private void switchToClaimActionsScreen(MouseEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ManageClaimActionsInterface.fxml"));
            Parent root = loader.load();
            Scene scene = new Scene(root);
            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            stage.setScene(scene);
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    @FXML
    private void handleBackButtonAction(ActionEvent event) {
        try {
            // Load the AdminBack interface
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/AdminBack.fxml"));
            Parent adminBackRoot = loader.load();

            // Get the current stage and switch the scene
            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            stage.setScene(new Scene(adminBackRoot));
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}