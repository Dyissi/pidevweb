package tn.esprit.services;

import tn.esprit.entities.team;
import tn.esprit.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class teamServices implements IService2<team> {

    private Connection con;
    public teamServices() {
        con = MyDatabase.getInstance().getCon();
    }

    @Override
    public void add(team team) throws SQLException {
        String query = "INSERT INTO `team`(`teamName`, `teamNbAthletes`, `teamTypeOfSport`, `teamWins`, `teamLosses`) VALUES ('"+team.getTeamName()+"','"+team.getTeamNath()+"','"+team.getTeamTOS()+"','"+team.getTeamW()+"','"+team.getTeamL()+"')";
        Statement stM = con.createStatement();
        stM.executeUpdate(query);
        System.out.println("added");
    }

    @Override
    public void addP(team team) throws SQLException {
        String query = "INSERT INTO `team`(`teamName`, `teamNbAthletes`, `teamTypeOfSport`, `teamWins`, `teamLosses`) VALUES (?,?,?,?,?)";
        PreparedStatement ps = con.prepareStatement(query);
        ps.setString(1,team.getTeamName());
        ps.setInt(2,team.getTeamNath());
        ps.setString(3,team.getTeamTOS());
        ps.setInt(4,team.getTeamW());
        ps.setInt(5,team.getTeamL());
        ps.executeUpdate();
        System.out.println("Team added successfully");

    }

    @Override
    public void delete(int teamId) throws SQLException {
        String query = "DELETE FROM `team` WHERE `teamId`=?";
        PreparedStatement ps =  con.prepareStatement(query);
        ps.setInt(1,teamId);
        int test=ps.executeUpdate();
        if (test==0){
            System.out.println("not found");
        }else
            System.out.println("deleted");

    }

    @Override
    public void update(int teamId,team t)  throws SQLException {
        String query = "UPDATE `team` SET `teamName`=?,`teamNbAthletes`=?,`teamTypeOfSport`=?,`teamWins`=?,`teamLosses`=? WHERE `teamId`=?";
        PreparedStatement ps = con.prepareStatement(query);
        ps.setString(1,t.getTeamName());
        ps.setInt(2,t.getTeamNath());
        ps.setString(3,t.getTeamTOS());
        ps.setInt(4,t.getTeamW());
        ps.setInt(5,t.getTeamL());
        ps.setInt(6,teamId);
        int test=ps.executeUpdate();
        if (test==0){
            System.out.println("not found");
        }else
            System.out.println("updated successfully");

    }

    @Override
    public List<team> returnList() throws SQLException {
        String query = "SELECT * FROM `team`";
        Statement stM = con.createStatement();
        List<team> teams = new ArrayList<>();
        ResultSet rs = stM.executeQuery(query);
        while (rs.next()) {
            team t = new team(rs.getInt(1),rs.getString(2),rs.getInt(3),rs.getString(4), rs.getInt(5), rs.getInt(6) );//index starts at 1 unlike add which starts at 0

            teams.add(t);
        }

        return teams;
    }
    public List<team> returnListBySport(String sport) throws SQLException {
        String query = "SELECT * FROM `team` WHERE `teamTypeOfSport` = ?";
        PreparedStatement ps = con.prepareStatement(query);
        ps.setString(1, sport);

        List<team> teams = new ArrayList<>();
        ResultSet rs = ps.executeQuery();

        while (rs.next()) {
            int id = rs.getInt("teamId");  // Use column names for clarity
            String name = rs.getString("teamName");
            int athletes = rs.getInt("teamNbAthletes");
            String typeOfSport = rs.getString("teamTypeOfSport");
            int wins = rs.getInt("teamWins");
            int losses = rs.getInt("teamLosses");

            team t = new team(id, name, athletes, typeOfSport, wins, losses);
            teams.add(t);
        }

        return teams;
    }

}
