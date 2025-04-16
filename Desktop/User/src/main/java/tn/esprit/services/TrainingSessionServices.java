package tn.esprit.services;

import tn.esprit.entities.Duration;
import tn.esprit.entities.Focus;
import tn.esprit.entities.TrainingSession;
import tn.esprit.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class TrainingSessionServices implements IService3<TrainingSession> {
    private Connection con;

    public TrainingSessionServices() {
        con = MyDatabase.getInstance().getCon();
    }

//    @Override
//    public void add(TrainingSession trainingSession) throws SQLException {
//        // Convert ENUM to String otherwise we will have an issue in the db
//        String query = "INSERT INTO `training_session`(`session_focus`, `session_start_time`, `session_duration`, `session_location`, `session_notes`) VALUES ('"
//                + trainingSession.getFocus().name() + "', '"
//                + trainingSession.getStart_time() + "', '"
//                + trainingSession.getDuration().getMinutes() + "', '"
//                + trainingSession.getLocation() + "', '"
//                + trainingSession.getSession_notes() + "')";
//        Statement stm = con.createStatement();
//        stm.executeUpdate(query);
//        System.out.println("Training Session added Successfully! :>");
//
//    }

    @Override
    public void addP(TrainingSession trainingSession) {
        // Convert ENUM to String
        String query = "INSERT INTO `training_session`(`session_focus`, `session_start_time`, `session_duration`, `session_location`, `session_notes`) VALUES (?,?,?,?,?)";
        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setString(1, trainingSession.getFocus().name());
            pstmt.setTime(2, Time.valueOf(trainingSession.getStart_time()));
            pstmt.setString(3, trainingSession.getDuration().getMinutes());
            pstmt.setString(4, trainingSession.getLocation());
            pstmt.setString(5, trainingSession.getSession_notes());
            pstmt.executeUpdate();
            System.out.println("Training Session added Successfully using PreparedStatement! :>");
        } catch (SQLException e) {
            throw new RuntimeException(e);
        }
    }

    @Override
    public List<TrainingSession> returnList() throws SQLException {
        String query = "SELECT * FROM `training_session`";
        List<TrainingSession> sessions = new ArrayList<>();

        try (Statement stm = con.createStatement(); ResultSet rs = stm.executeQuery(query)) {
            while (rs.next()) {
                Focus focus = null;
                try {
                    focus = Focus.valueOf(rs.getString("session_focus").toUpperCase());
                } catch (IllegalArgumentException e) {
                    System.out.println("Invalid Focus value from DB: " + rs.getString("session_focus"));
                    continue; // skip this row and move to the next
                }

                TrainingSession session = new TrainingSession(
                        rs.getInt("trainingSession_id"),
                        focus,
                        rs.getTime("session_start_time").toLocalTime(),
                        Duration.fromString(rs.getString("session_duration")),
                        rs.getString("session_location"),
                        rs.getString("session_notes")
                );
                sessions.add(session);
            }
        }
        return sessions;

    }

    @Override
    public void delete(TrainingSession trainingSession) throws SQLException {
        String query = "DELETE FROM `training_session` WHERE `trainingSession_id` = ?";
        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, trainingSession.getTrainingSession_id());
            int rowsDeleted = pstmt.executeUpdate();

            if (rowsDeleted > 0) {
                System.out.println("Training session deleted successfully! :>");
            } else {
                System.out.println("Oops! No training session found with the given ID :<");
            }
        } catch (SQLException e) {
            throw new RuntimeException(e);
        }
    }

    @Override
    public void update(TrainingSession trainingSession) throws SQLException {
        String checkQuery = "SELECT * FROM training_session WHERE trainingSession_id = ?";
        try (PreparedStatement checkStmt = con.prepareStatement(checkQuery)) {
            checkStmt.setInt(1, trainingSession.getTrainingSession_id());
            ResultSet rs = checkStmt.executeQuery();
            if (!rs.next()) {
                System.out.println("No training session found with the given ID: :< " + trainingSession.getTrainingSession_id());
                return;
            }

            // Now we update
            String query = "UPDATE `training_session` SET `session_focus` = ?, `session_start_time` = ?, `session_duration` = ?, `session_location` = ?, `session_notes` = ? WHERE `trainingSession_id` = ?";
            try (PreparedStatement pstmt = con.prepareStatement(query)) {
                pstmt.setString(1, trainingSession.getFocus().name()); // using enum name directly
                pstmt.setTime(2, Time.valueOf(trainingSession.getStart_time())); // convert LocalTime to SQL Time
                pstmt.setString(3, trainingSession.getDuration().getMinutes());
                pstmt.setString(4, trainingSession.getLocation());
                pstmt.setString(5, trainingSession.getSession_notes());
                pstmt.setInt(6, trainingSession.getTrainingSession_id());

                int rowsUpdated = pstmt.executeUpdate();
                if (rowsUpdated > 0) {
                    System.out.println("Training session updated successfully! :>");
                } else {
                    System.out.println("Oops! Failed to update :<");
                }
            } catch (SQLException e) {
                throw new RuntimeException(e);
            }
        }
    }
}
