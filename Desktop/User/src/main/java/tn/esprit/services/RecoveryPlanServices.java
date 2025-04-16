package tn.esprit.services;

import tn.esprit.entities.*;
import tn.esprit.entities.user;
import tn.esprit.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class RecoveryPlanServices implements IService2<RecoveryPlan> {
    private Connection connection;

    public RecoveryPlanServices() {
        connection = MyDatabase.getInstance().getCon();
    }

    @Override
    public void add(RecoveryPlan recoveryPlan) throws Exception {
        try {
            if (recoveryPlan.getRecovery_Status() == null) {
                throw new Exception("Recovery status cannot be null");
            }

            String sql = "INSERT INTO recoveryplan (injury_id, user_id, recovery_Goal, recovery_Description, recovery_StartDate, recovery_EndDate, recovery_Status) " +
                    "VALUES (?, ?, ?, ?, ?, ?, ?)";
            PreparedStatement preparedStatement = connection.prepareStatement(sql);
            preparedStatement.setInt(1, recoveryPlan.getInjury().getInjury_id());
            preparedStatement.setInt(2, recoveryPlan.getUser().getUser_id());
            preparedStatement.setString(3, recoveryPlan.getRecovery_Goal().name());
            preparedStatement.setString(4, recoveryPlan.getRecovery_Description());
            preparedStatement.setDate(5, Date.valueOf(recoveryPlan.getRecovery_StartDate()));
            preparedStatement.setDate(6, Date.valueOf(recoveryPlan.getRecovery_EndDate()));
            preparedStatement.setString(7, recoveryPlan.getRecovery_Status().name());
            preparedStatement.executeUpdate();

        } catch (SQLException e) {
            throw new SQLException("Error while adding recovery plan: " + e.getMessage());
        } catch (Exception e) {
            throw new Exception("Failed to add recovery plan: " + e.getMessage());
        }
    }

    @Override
    public void addP(RecoveryPlan recoveryPlan) throws Exception {
        try {
            add(recoveryPlan);
            System.out.println("Recovery plan added successfully");
        } catch (Exception e) {
            throw e;
        }
    }

    @Override
    public void delete(int recovery_id) throws SQLException {
        String sql = "DELETE FROM recoveryplan WHERE recovery_id = ?";
        PreparedStatement preparedStatement = connection.prepareStatement(sql);
        preparedStatement.setInt(1, recovery_id);
        int result = preparedStatement.executeUpdate();
        if (result == 0)
            System.out.println("Recovery plan not found");
        else
            System.out.println("Recovery plan deleted");
    }

    @Override
    public void update(int recovery_id, RecoveryPlan recoveryPlan) throws SQLException {
        String sql = "UPDATE recoveryplan SET injury_id = ?, user_id = ?, recovery_Goal = ?, recovery_Description = ?, " +
                "recovery_StartDate = ?, recovery_EndDate = ?, recovery_Status = ? WHERE recovery_id = ?";
        PreparedStatement ps = connection.prepareStatement(sql);
        ps.setInt(1, recoveryPlan.getInjury().getInjury_id());
        ps.setInt(2, recoveryPlan.getUser().getUser_id());
        ps.setString(3, recoveryPlan.getRecovery_Goal().name());
        ps.setString(4, recoveryPlan.getRecovery_Description());
        ps.setDate(5, Date.valueOf(recoveryPlan.getRecovery_StartDate()));
        ps.setDate(6, Date.valueOf(recoveryPlan.getRecovery_EndDate()));
        ps.setString(7, recoveryPlan.getRecovery_Status().name());
        ps.setInt(8, recovery_id);
        int result = ps.executeUpdate();
        if (result == 0)
            System.out.println("Recovery plan not found");
        else
            System.out.println("Recovery plan updated successfully");
    }

    @Override
    public List<RecoveryPlan> returnList() throws SQLException {
        String query = "SELECT r.*, i.injuryType, i.injuryDate, u.user_fname, u.user_lname " +
                "FROM recoveryplan r " +
                "JOIN injury i ON r.injury_id = i.injury_id " +
                "JOIN user u ON r.user_id = u.user_id";
        Statement statement = connection.createStatement();
        List<RecoveryPlan> recoveryPlans = new ArrayList<>();
        ResultSet rs = statement.executeQuery(query);
        while (rs.next()) {
            Injury injury = new Injury();
            injury.setInjury_id(rs.getInt("injury_id"));
            injury.setInjuryType(tn.esprit.entities.InjuryType.valueOf(rs.getString("injuryType")));
            injury.setInjuryDate(rs.getDate("injuryDate").toLocalDate());

            user user = new user();
            user.setUser_id(rs.getInt("user_id"));
            user.setUser_fname(rs.getString("user_fname"));
            user.setUser_lname(rs.getString("user_lname"));

            RecoveryPlan recoveryPlan = new RecoveryPlan(
                    rs.getInt("recovery_id"),
                    injury,
                    user,
                    RecoveryGoal.valueOf(rs.getString("recovery_Goal")),
                    rs.getString("recovery_Description"),
                    rs.getDate("recovery_StartDate").toLocalDate(),
                    rs.getDate("recovery_EndDate").toLocalDate(),
                    RecoveryStatus.valueOf(rs.getString("recovery_Status"))
            );
            recoveryPlans.add(recoveryPlan);
        }
        return recoveryPlans;
    }
}
