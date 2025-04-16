package tn.esprit.services;

import tn.esprit.entities.Performance;
import tn.esprit.services.IService2;
import tn.esprit.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;


public class PerformanceServices implements IService3<Performance> {

    private Connection con;

    public PerformanceServices() {
        con = MyDatabase.getInstance().getCon();
    }

//     @Override
//    public void add(Performance performance) throws SQLException {
//        String query = "INSERT INTO `performance_data` (`performance_speed`, `performance_agility`, `performance_nbr_goals`, `performance_assists`, `performance_date_recorded`, `performance_nbr_fouls`) VALUES ("
//                + performance.getSpeed() + ", "
//                + performance.getAgility() + ", "
//                + performance.getNbr_goals() + ", "
//                + performance.getAssists() + ", '"
//                + performance.getDate_recorded() + "', "
//                + performance.getNbr_fouls() + ")";
//        Statement stm =con.createStatement();
//        stm.executeUpdate(query);
//        System.out.println("Performance added Successfully !! :>");
//    }

    @Override
    public void addP(Performance performance) throws SQLException {
        String query = "INSERT INTO `performance_data` (`performance_speed`, `performance_agility`, `performance_nbr_goals`, `performance_assists`, `performance_date_recorded`, `performance_nbr_fouls`) VALUES (?,?,?,?,?,?)";
        PreparedStatement pstm =con.prepareStatement(query);
        pstm.setFloat(1,performance.getSpeed());
        pstm.setFloat(2,performance.getAgility());
        pstm.setInt(3,performance.getNbr_goals());
        pstm.setInt(4,performance.getAssists());
        pstm.setDate(5, performance.getDate_recorded());
        pstm.setInt(6,performance.getNbr_fouls());
        pstm.executeUpdate();
        System.out.println("Performance added Successfully using the PreparedStatement !! :>");
    }

    @Override
    public List<Performance> returnList() throws SQLException {
        String query = "SELECT * FROM `performance_data`";
        Statement stm = con.createStatement();
        List<Performance> performances = new ArrayList<>();
        ResultSet rs = stm.executeQuery(query);
        while (rs.next()) {
            Date dateRecorded = rs.getDate("performance_date_recorded");

            Performance pr = new Performance(
                    rs.getInt("performance_id"),
                    rs.getFloat("performance_speed"),
                    rs.getFloat("performance_agility"),
                    rs.getInt("performance_nbr_goals"),
                    rs.getInt("performance_assists"),
                    dateRecorded,
                    rs.getInt("performance_nbr_fouls")
            );
            performances.add(pr);
        }
        return performances;
    }
    @Override
    public void delete(Performance performance) throws SQLException {
        String query = "DELETE FROM `performance_data` WHERE `performance_id` = ?";
        try(PreparedStatement pstmt =con.prepareStatement(query)){
            pstmt.setInt(1,performance.getPerformance_id());
            int rowsDeleted = pstmt.executeUpdate();
            if(rowsDeleted>0){
                System.out.println("Performance deleted Successfully !! :>");
            }else {
                System.out.println("Oops ! No performance found with the given ID :<");
            }
        }


    }

    @Override
    public void update(Performance performance) throws SQLException {
        // Check if the performance with the given ID exists
        String checkQuery = "SELECT * FROM performance_data WHERE performance_id = ?;";
        try (PreparedStatement checkStmt = con.prepareStatement(checkQuery)) {
            checkStmt.setInt(1, performance.getPerformance_id());
            ResultSet rs = checkStmt.executeQuery();
            if (!rs.next()) {
                System.out.println("No performance found with the given ID: :< " + performance.getPerformance_id());
                return;
            }
        }

        String query = "UPDATE `performance_data` SET `performance_speed` = ?, `performance_agility` = ?, `performance_nbr_goals` = ?, `performance_assists` = ?, `performance_date_recorded` = ?, `performance_nbr_fouls` = ? WHERE `performance_id` = ?";
        try (PreparedStatement pstmt = con.prepareStatement(query)) {
            pstmt.setFloat(1, performance.getSpeed());
            pstmt.setFloat(2, performance.getAgility());
            pstmt.setInt(3, performance.getNbr_goals());
            pstmt.setInt(4, performance.getAssists());
            pstmt.setDate(5, performance.getDate_recorded());
            pstmt.setInt(6, performance.getNbr_fouls());
            pstmt.setInt(7, performance.getPerformance_id());

            int rowsUpdated = pstmt.executeUpdate();
            if (rowsUpdated > 0) {
                System.out.println("Performance updated successfully :>!");
            }else {
                System.out.println("Oops ! Failed to update :<");
            }
        }
    }

}
