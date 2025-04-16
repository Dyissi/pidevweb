package tn.esprit.services;

import tn.esprit.entities.Claim;
import tn.esprit.entities.ClaimAction;
import tn.esprit.entities.ClaimCategory;
import tn.esprit.entities.ClaimStatus;
import tn.esprit.utils.MyDatabase;

import java.sql.*;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class ClaimServices implements Iservice4<Claim> {

    private Connection con;

    public ClaimServices(){
        con = MyDatabase.getInstance().getCon();
    }

    @Override
    public void add(Claim claim) throws SQLException {
        String query = "INSERT INTO `claim`(`claimDescription`, `claimStatus`, `claimDate`, `claimCategory`) VALUES ('"+claim.getClaimDescription()+"','"+claim.getClaimStatus()+"','"+claim.getClaimDate()+"','"+claim.getClaimCategory()+"')";
        Statement stm = con.createStatement();
        stm.executeUpdate(query);
        System.out.println("Claim added!");
    }

    @Override
    public void addP(Claim claim) throws SQLException {
        String query = "INSERT INTO `claim`(`claimDescription`, `claimStatus`, `claimDate`, `claimCategory`) VALUES (?,?,?,?)";
        PreparedStatement ps =con.prepareStatement(query);
        ps.setString(1, claim.getClaimDescription());
        ps.setString(2, claim.getClaimStatus().toString());
        ps.setDate(3, java.sql.Date.valueOf(claim.getClaimDate()));
        ps.setString(4, claim.getClaimCategory().toString());
        ps.executeUpdate();
        System.out.println("Claim added with second method!");
    }

    @Override
    public List<Claim> returnList() throws SQLException {
        List<Claim> claims = new ArrayList<>();
        String query = "SELECT * FROM claim"; // Adjust the table name if necessary

        try (Statement stmt = con.createStatement();
             ResultSet rs = stmt.executeQuery(query)) {

            while (rs.next()) {
                int claimId = rs.getInt("claimId"); // Adjust this to your actual column name
                String claimDescription = rs.getString("claimDescription");
                ClaimStatus claimStatus = ClaimStatus.valueOf(rs.getString("claimStatus")); // Adjust if your DB uses different strings
                LocalDate claimDate = rs.getDate("claimDate").toLocalDate();
                ClaimCategory claimCategory = ClaimCategory.valueOf(rs.getString("claimCategory")); // Adjust as needed

                Claim claim = new Claim(claimId, claimDescription, claimStatus, claimDate, claimCategory);
                claims.add(claim);
            }

        } catch (SQLException e) {
            System.out.println("Error retrieving claims: " + e.getMessage());
            throw e; // Propagate the exception
        }

        return claims;
    }


    @Override
    public void delete(Claim claim) throws SQLException {
        String query = "DELETE FROM claim WHERE claimId = ?";

        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, claim.getClaimId());
            int rowsDeleted = pstmt.executeUpdate();
            if (rowsDeleted > 0) {
                System.out.println("Claim deleted successfully!");
            } else {
                System.out.println("No claim found with the given ID.");
            }
        } catch (SQLException e) {
            System.out.println("Error deleting claim: " + e.getMessage());
            throw e; // Propagate the exception
        }
    }


    @Override
    public void update(Claim claim) throws SQLException {
        String query = "UPDATE claim SET claimDescription = ?, claimStatus = ?, claimDate = ?, claimCategory = ? WHERE claimId = ?";

        PreparedStatement stmt = con.prepareStatement(query);
        stmt.setString(1, claim.getClaimDescription());
        stmt.setString(2, claim.getClaimStatus().toString());
        stmt.setDate(3, java.sql.Date.valueOf(claim.getClaimDate()));
        stmt.setString(4, claim.getClaimCategory().toString());
        stmt.setInt(5, claim.getClaimId());

        int rowsUpdated = stmt.executeUpdate();
        if (rowsUpdated > 0) {
            System.out.println("Claim updated successfully!");
        } else {
            System.out.println("No claim found with the given ID.");
        }
    }

    public Claim findById(int claimId) throws SQLException {
        String query = "SELECT * FROM claim WHERE claimId = ?";
        Claim claim = null;

        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setInt(1, claimId);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                String description = rs.getString("claimDescription");
                ClaimStatus status = ClaimStatus.valueOf(rs.getString("claimStatus"));
                LocalDate date = rs.getDate("claimDate").toLocalDate();
                ClaimCategory category = ClaimCategory.valueOf(rs.getString("claimCategory"));

                claim = new Claim(claimId, description, status, date, category);
            }
        }

        return claim;
    }

}
