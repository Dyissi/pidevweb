package tn.esprit.main;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

import java.io.IOException;


public class MainFX extends Application {


    @Override
    public void start(Stage primaryStage) throws IOException {

        Parent root= FXMLLoader.load(getClass().getResource("/Medicalfront.fxml"));
        //Parent root= FXMLLoader.load(getClass().getResource("/AdminBack.fxml"));
        //Parent root= FXMLLoader.load(getClass().getResource("/AddClaimAthlete.fxml"));
        Scene scene=new Scene(root);
        //primaryStage.setTitle("first Scene");
        primaryStage.initStyle(StageStyle.DECORATED);
        primaryStage.setScene(scene);
        primaryStage.show();



    }
    public static void main(String[] args) {
        launch(args);
    }
}