package tn.esprit.services;

import tn.esprit.entities.ClaimAction;
import tn.esprit.entities.Claim;
import tn.esprit.entities.ClaimActionType;
import tn.esprit.utils.MyDatabase;

import java.sql.*;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class ClaimActionServices implements Iservice4<ClaimAction> {

    private Connection con;

    public ClaimActionServices() {
        con = MyDatabase.getInstance().getCon();
    }

    @Override
    public void add(ClaimAction claimAction) throws SQLException {
        String query = "INSERT INTO `claimaction`(`claimId`, `claimActionType`, `claimActionStartDate`, `claimActionEndDate`, `claimActionNotes`) " +
                "VALUES (?, ?, ?, ?, ?)";

        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, claimAction.getClaim().getClaimId());
            pstmt.setString(2, claimAction.getClaimActionType().toString());
            pstmt.setDate(3, java.sql.Date.valueOf(claimAction.getClaimActionStartDate()));
            pstmt.setDate(4, java.sql.Date.valueOf(claimAction.getClaimActionEndDate()));
            pstmt.setString(5, claimAction.getClaimActionNotes());
            pstmt.executeUpdate();
            System.out.println("Claim Action added!");
        }
    }

    @Override
    public void addP(ClaimAction claimAction) throws SQLException {
        String query = "INSERT INTO `claimaction`(`claimId`, `claimActionType`, `claimActionStartDate`, `claimActionEndDate`, `claimActionNotes`) " +
                "VALUES (?, ?, ?, ?, ?)";

        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, claimAction.getClaim().getClaimId());
            pstmt.setString(2, claimAction.getClaimActionType().toString());
            pstmt.setDate(3, java.sql.Date.valueOf(claimAction.getClaimActionStartDate()));
            pstmt.setDate(4, java.sql.Date.valueOf(claimAction.getClaimActionEndDate()));
            pstmt.setString(5, claimAction.getClaimActionNotes());
            pstmt.executeUpdate();
            System.out.println("Claim Action added with second method!");
        }
    }

    @Override
    public List<ClaimAction> returnList() throws SQLException {
        List<ClaimAction> claimActions = new ArrayList<>();
        String query = "SELECT * FROM claimaction";

        try (Statement stmt = con.createStatement();
             ResultSet rs = stmt.executeQuery(query)) {

            while (rs.next()) {
                int claimActionId = rs.getInt("claimActionId");
                int claimId = rs.getInt("claimId");
                ClaimActionType claimActionType = ClaimActionType.valueOf(rs.getString("claimActionType"));
                LocalDate claimActionStartDate = rs.getDate("claimActionStartDate").toLocalDate();
                LocalDate claimActionEndDate = rs.getDate("claimActionEndDate").toLocalDate();
                String claimActionNotes = rs.getString("claimActionNotes");

                Claim claim = new Claim(); // Assuming you have a way to retrieve the Claim object by claimId
                claim.setClaimId(claimId);

                ClaimAction claimAction = new ClaimAction(claimActionId, claim, claimActionType, claimActionStartDate, claimActionEndDate, claimActionNotes);
                claimActions.add(claimAction);
            }

        } catch (SQLException e) {
            System.out.println("Error retrieving claim actions: " + e.getMessage());
            throw e;
        }

        return claimActions;
    }

    @Override
    public void delete(ClaimAction claimAction) throws SQLException {
        String query = "DELETE FROM claimaction WHERE claimActionId = ?";

        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, claimAction.getClaimActionId());
            int rowsDeleted = pstmt.executeUpdate();
            if (rowsDeleted > 0) {
                System.out.println("Claim Action deleted successfully!");
            } else {
                System.out.println("No claim action found with the given ID.");
            }
        } catch (SQLException e) {
            System.out.println("Error deleting claim action: " + e.getMessage());
            throw e;
        }
    }

    @Override
    public void update(ClaimAction claimAction) throws SQLException {
        String query = "UPDATE claimaction SET claimId = ?, claimActionType = ?, claimActionStartDate = ?, claimActionEndDate = ?, claimActionNotes = ? WHERE claimActionId = ?";

        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, claimAction.getClaim().getClaimId());
            pstmt.setString(2, claimAction.getClaimActionType().toString());
            pstmt.setDate(3, java.sql.Date.valueOf(claimAction.getClaimActionStartDate()));
            pstmt.setDate(4, java.sql.Date.valueOf(claimAction.getClaimActionEndDate()));
            pstmt.setString(5, claimAction.getClaimActionNotes());
            pstmt.setInt(6, claimAction.getClaimActionId());

            int rowsUpdated = pstmt.executeUpdate();
            if (rowsUpdated > 0) {
                System.out.println("Claim Action updated successfully!");
            } else {
                System.out.println("No claim action found with the given ID.");
            }
        }
    }

    public ClaimAction findById(int claimActionId) throws SQLException {
        String query = "SELECT * FROM claimaction WHERE claimActionId = ?";
        ClaimAction claimAction = null;

        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, claimActionId);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                int claimId = rs.getInt("claimId");
                ClaimActionType claimActionType = ClaimActionType.valueOf(rs.getString("claimActionType"));
                LocalDate claimActionStartDate = rs.getDate("claimActionStartDate").toLocalDate();
                LocalDate claimActionEndDate = rs.getDate("claimActionEndDate").toLocalDate();
                String claimActionNotes = rs.getString("claimActionNotes");

                Claim claim = new Claim(); // Assuming you have a way to retrieve the Claim object by claimId
                claim.setClaimId(claimId);

                claimAction = new ClaimAction(claimActionId, claim, claimActionType, claimActionStartDate, claimActionEndDate, claimActionNotes);
            }
        }

        return claimAction;
    }

    public boolean hasClaimAction(int claimId) throws SQLException {
        String query = "SELECT COUNT(*) FROM claimaction WHERE claimId = ?";
        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, claimId);
            ResultSet rs = pstmt.executeQuery();
            if (rs.next()) {
                return rs.getInt(1) > 0;
            }
        }
        return false;
    }
}