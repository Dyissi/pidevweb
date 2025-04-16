package tn.esprit.services;

import tn.esprit.entities.team;
import tn.esprit.entities.tournament;
import tn.esprit.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class TournamentService implements IService2<tournament> {
    private Connection connection;

    public TournamentService() {
        connection = MyDatabase.getInstance().getCon();
    }

    @Override
    public void add(tournament tournament) throws SQLException {
        String sql = "INSERT INTO tournament (tournamentName, tournamentStartDate, tournamentEndDate, tournamentLocation, tournamentTOS, tournamentNbteams) " +
                "VALUES (?, ?, ?, ?, ?, ?)";
        PreparedStatement preparedStatement = connection.prepareStatement(sql);
        preparedStatement.setString(1, tournament.getTournamentName());
        preparedStatement.setDate(2, new java.sql.Date(tournament.getTournamentStartDate().getTime()));
        preparedStatement.setDate(3, new java.sql.Date(tournament.getTournamentEndDate().getTime()));
        preparedStatement.setString(4, tournament.getTournamentLocation());
        preparedStatement.setString(5, tournament.getTournamentTOS());
        preparedStatement.setInt(6, tournament.getTournamentNbteams());
        preparedStatement.executeUpdate();
    }

    @Override
    public void addP(tournament tournament) throws SQLException {
        String sql = "INSERT INTO tournament (tournamentName, tournamentStartDate, tournamentEndDate, tournamentLocation, tournamentTOS, tournamentNbteams) " +
                "VALUES (?, ?, ?, ?, ?, ?)";
        PreparedStatement preparedStatement = connection.prepareStatement(sql);
        preparedStatement.setString(1, tournament.getTournamentName());
        preparedStatement.setDate(2, new java.sql.Date(tournament.getTournamentStartDate().getTime()));
        preparedStatement.setDate(3, new java.sql.Date(tournament.getTournamentEndDate().getTime()));
        preparedStatement.setString(4, tournament.getTournamentLocation());
        preparedStatement.setString(5, tournament.getTournamentTOS());
        preparedStatement.setInt(6, tournament.getTournamentNbteams());
        preparedStatement.executeUpdate();
        System.out.println("Tournament added successfully");
    }

    @Override
    public void delete(int id) throws SQLException {
        String sql = "DELETE FROM tournament WHERE tournamentId = ?";
        PreparedStatement preparedStatement = connection.prepareStatement(sql);
        preparedStatement.setInt(1, id);
        int test = preparedStatement.executeUpdate();
        if (test == 0)
            System.out.println("tournament not found");
        else
            System.out.println("tournament deleted");
    }

    @Override
    public void update(int id, tournament tournament) throws SQLException {
        String sql = "UPDATE tournament SET tournamentName = ?, tournamentStartDate = ?, tournamentEndDate = ?, " +
                "tournamentLocation = ?, tournamentTOS = ?, tournamentNbteams = ? WHERE tournamentId = ?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setString(1, tournament.getTournamentName());
        ps.setDate(2, new java.sql.Date(tournament.getTournamentStartDate().getTime()));
        ps.setDate(3, new java.sql.Date(tournament.getTournamentEndDate().getTime()));
        ps.setString(4, tournament.getTournamentLocation());
        ps.setString(5, tournament.getTournamentTOS());
        ps.setInt(6, tournament.getTournamentNbteams());
        ps.setInt(7, id);
        int test=ps.executeUpdate();
        if (test==0){
            System.out.println("not found");
        }else
            System.out.println("updated successfully");
    }

    @Override
    public List<tournament> returnList() throws SQLException {
        String query = "SELECT * FROM `tournament`";
        Statement stM = connection.createStatement();
        List<tournament> tournaments = new ArrayList<>();
        ResultSet rs = stM.executeQuery(query);
        while (rs.next()) {
            tournament t = new tournament(rs.getInt(1),rs.getString(2),rs.getDate(3),rs.getDate(4), rs.getString(5), rs.getString(6), rs.getInt(7));//index starts at 1 unlike add which starts at 0

            tournaments.add(t);
        }

        return tournaments;
    }
}

