package tn.esprit.services;

import com.mysql.cj.xdevapi.Client;
import tn.esprit.entities.*;
import tn.esprit.utils.MyDatabase;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class UserServices implements IService<user> {
    private Connection con;

    public UserServices() {
        con = MyDatabase.getInstance().getCon();
    }

    @Override
    public void add(user user) throws SQLException {
        if (user instanceof Admin) {
            System.out.println("Admins must be added manually in the database.");
            return; // Don't allow adding admins through this method
        }
        String query = "INSERT INTO `user`(`user_fname`, `user_lname`, `user_email`, `user_pwd`, `user_nbr`, `user_role`, `nb_teams`,`med_specialty`,`athlete_DoB`, `athlete_gender`,`athlete_address`, `athlete_height`, `athlete_weight`,`isInjured` )" + " VALUES (?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?)";
        try (PreparedStatement ps = con.prepareStatement(query)) {
            ps.setString(1, user.getUser_fname());
            ps.setString(2, user.getUser_lname());
            ps.setString(3, user.getUser_email());
            ps.setString(4, user.getUser_pwd());
            ps.setString(5, user.getUser_nbr());
            ps.setString(6, user.getUser_role().name());
            if (user instanceof Coach) {
                Coach coach = (Coach) user;
                ps.setInt(7, coach.getNb_teams());
            } else {
                ps.setNull(7, java.sql.Types.INTEGER);
            }
            if (user instanceof Medical_staff) {
                Medical_staff medical_staff = (Medical_staff) user;
                ps.setString(8, medical_staff.getSpeciality());
            } else {
                ps.setNull(8, java.sql.Types.VARCHAR);
            }
            if (user instanceof Athlete) {
                Athlete athlete = (Athlete) user;
                ps.setFloat(12, athlete.getAthlete_height());
                ps.setFloat(13, athlete.getAthlete_weight());
                ps.setString(10, athlete.getAthlete_gender());
                ps.setString(11, athlete.getAthlete_address());
                ps.setInt(14, athlete.getIsInjured());
                ps.setDate(9, athlete.getAthlete_DoB());
            } else {
                ps.setNull(12, java.sql.Types.FLOAT);
                ps.setNull(13, java.sql.Types.FLOAT);
                ps.setNull(10, java.sql.Types.VARCHAR);
                ps.setNull(11, java.sql.Types.VARCHAR);
                ps.setNull(14, java.sql.Types.INTEGER);
                ps.setNull(9, java.sql.Types.DATE);
            }
            ps.executeUpdate();
            System.out.println("User added successfully.");
        } catch (SQLException e) {
            System.out.println("Error adding user: " + e.getMessage());
        }
    }

    public void delete(int user_id) {
        String query = "DELETE FROM user WHERE user_id = ?";

        try (PreparedStatement ps = con.prepareStatement(query)) {
            ps.setInt(1, user_id);
            int rowsAffected = ps.executeUpdate();

            if (rowsAffected > 0) {
                System.out.println("User with ID " + user_id + " deleted successfully.");
            } else {
                System.out.println("User with ID " + user_id + " not found.");
            }
        } catch (SQLException e) {
            System.out.println("Error deleting user: " + e.getMessage());
        }

    }

    public void update(int user_id,user updatedUser) {String query = "UPDATE user SET user_fname=?, user_lname=?, user_email=?, user_pwd=?, user_nbr=?, user_role=?, "
            + "nb_teams=?, med_specialty=?,athlete_DoB=?, athlete_gender=?,athlete_address=?, athlete_height=?, athlete_weight=?,isInjured=? WHERE user_id=?";

        try (PreparedStatement ps = con.prepareStatement(query)) {
            ps.setString(1, updatedUser.getUser_fname());
            ps.setString(2, updatedUser.getUser_lname());
            ps.setString(3, updatedUser.getUser_email());
            ps.setString(4, updatedUser.getUser_pwd());
            ps.setString(5, updatedUser.getUser_nbr());
            ps.setString(6, updatedUser.getUser_role().toString());

            if (updatedUser instanceof Coach coach) {
                // Set Client-specific fields
                ps.setInt(7, coach.getNb_teams());
                ps.setNull(8, java.sql.Types.VARCHAR);
                ps.setNull(9, java.sql.Types.DATE);
                ps.setNull(10, java.sql.Types.VARCHAR);
                ps.setNull(11, java.sql.Types.VARCHAR);
                ps.setNull(12, java.sql.Types.FLOAT);
                ps.setNull(13, java.sql.Types.FLOAT);
                ps.setNull(14, java.sql.Types.INTEGER);

            } else if  (updatedUser instanceof Medical_staff medical_staff) {
                // Set Medical_staff-specific fields
                ps.setString(8, medical_staff.getSpeciality());
                ps.setNull(7, java.sql.Types.INTEGER);
                ps.setNull(9, java.sql.Types.DATE);
                ps.setNull(10, java.sql.Types.VARCHAR);
                ps.setNull(11, java.sql.Types.VARCHAR);
                ps.setNull(12, java.sql.Types.FLOAT);
                ps.setNull(13, java.sql.Types.FLOAT);
                ps.setNull(14, java.sql.Types.INTEGER);
            } else if (updatedUser instanceof Athlete athlete) {
                // Set Athlete-specific fields
                ps.setDate(9, athlete.getAthlete_DoB());
                ps.setString(10, athlete.getAthlete_gender());
                ps.setString(11, athlete.getAthlete_address());
                ps.setFloat(12, athlete.getAthlete_height());
                ps.setFloat(13, athlete.getAthlete_weight());
                ps.setInt(14, athlete.getIsInjured());
                ps.setNull(7, java.sql.Types.INTEGER);
                ps.setNull(8, java.sql.Types.VARCHAR);
            } else if (updatedUser instanceof Admin admin) {
                // Set Admin-specific field
                ps.setNull(7, java.sql.Types.INTEGER);
                ps.setNull(8, java.sql.Types.VARCHAR);
                ps.setNull(9, java.sql.Types.DATE);
                ps.setNull(10, java.sql.Types.VARCHAR);
                ps.setNull(11, java.sql.Types.VARCHAR);
                ps.setNull(12, java.sql.Types.FLOAT);
                ps.setNull(13, java.sql.Types.FLOAT);
                ps.setNull(14, java.sql.Types.INTEGER);
            }

            ps.setInt(15, user_id); // WHERE condition
            ps.executeUpdate();
            System.out.println("Account updated successfully.");
        } catch (SQLException e) {
            System.out.println("Error updating account: " + e.getMessage());
        }
    }

    @Override
    public List<user> getAll() {
        List<user> users = new ArrayList<>();
        String query = "SELECT * FROM user";
        try (Statement stmt = con.createStatement();
             ResultSet rs = stmt.executeQuery(query)) {

            System.out.println("Fetching users from database...");

            while (rs.next()) {
                int user_id = rs.getInt("user_id");
                String user_fname = rs.getString("user_fname");
                String user_lname = rs.getString("user_lname");
                String user_email = rs.getString("user_email");
                String user_pwd = rs.getString("user_pwd");
                String user_nbr = rs.getString("user_nbr");
                String user_role = rs.getString("user_role");

                System.out.println("User found: " + user_id + " | Role: [" + user_role + "]");

                if ("coach".equalsIgnoreCase(user_role)) {
                    users.add(new Coach(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr,
                            rs.getInt("nb_teams")));
                }
                else if ("medical_staff".equalsIgnoreCase(user_role)) {
                    System.out.println("Adding medical staff: " + user_id);
                    users.add(new Medical_staff(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr,
                            rs.getString("med_specialty")));
                }
                else if ("athlete".equalsIgnoreCase(user_role)) {
                    users.add(new Athlete(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr,
                            rs.getDate("athlete_DoB"), rs.getString("athlete_gender"),
                            rs.getString("athlete_address"), rs.getFloat("athlete_height"),
                            rs.getFloat("athlete_weight"), rs.getInt("isInjured")));
                }
                else if ("admin".equalsIgnoreCase(user_role)) {
                    users.add(new Admin(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr));
                }
            }

            System.out.println("Total users fetched: " + users.size());
            System.out.println("Medical staff users in list: " + users.stream().filter(u -> u instanceof Medical_staff).count());

        } catch (SQLException e) {
            System.out.println("Error retrieving all users: " + e.getMessage());
        }
        return users;
    }

    public user getAthleteByFullName(String firstname, String lastname) {
        List<user> users = new ArrayList<>();
        String query = "SELECT * FROM user WHERE user_fname = ? AND user_lname = ? AND user_role = 'athlete'";
        try (PreparedStatement ps = con.prepareStatement(query)) {
            ps.setString(1, firstname);
            ps.setString(2, lastname);

            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    int user_id = rs.getInt("user_id");
                    String user_fname = rs.getString("user_fname");
                    String user_lname = rs.getString("user_lname");
                    String user_email = rs.getString("user_email");
                    String user_pwd = rs.getString("user_pwd");
                    String user_nbr = rs.getString("user_nbr");

                    users.add(new Athlete(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr,
                            rs.getDate("athlete_DoB"), rs.getString("athlete_gender"),
                            rs.getString("athlete_address"), rs.getFloat("athlete_height"),
                            rs.getFloat("athlete_weight"), rs.getInt("isInjured")));
                }
            }
        } catch (SQLException e) {
            System.out.println("Error retrieving athlete: " + e.getMessage());
        }
        return users.isEmpty() ? null : users.get(0);
    }


    public List<user> getUsersByRole(String role) {
        List<user> users = new ArrayList<>();
        String query = "SELECT * FROM user WHERE user_role = ?";

        try (PreparedStatement stmt = con.prepareStatement(query)) {
            stmt.setString(1, role);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    int user_id = rs.getInt("user_id");
                    String user_fname = rs.getString("user_fname");
                    String user_lname = rs.getString("user_lname");
                    String user_email = rs.getString("user_email");
                    String user_pwd = rs.getString("user_pwd");
                    String user_nbr = rs.getString("user_nbr");

                    if ("coach".equalsIgnoreCase(role)) {
                        users.add(new Coach(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr,
                                rs.getInt("nb_teams")));
                    } else if ("medical_staff".equalsIgnoreCase(role)) {
                        users.add(new Medical_staff(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr,
                                rs.getString("med_specialty")));
                    } else if ("athlete".equalsIgnoreCase(role)) {
                        users.add(new Athlete(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr,
                                rs.getDate("athlete_DoB"), rs.getString("athlete_gender"),
                                rs.getString("athlete_address"), rs.getFloat("athlete_height"),
                                rs.getFloat("athlete_weight"), rs.getInt("isInjured")));
                    } else if ("admin".equalsIgnoreCase(role)) {
                        users.add(new Admin(user_id, user_fname, user_lname, user_email, user_pwd, user_nbr));
                    }
                }
            }
        } catch (SQLException e) {
            System.out.println("Error retrieving users by role: " + e.getMessage());
        }

        return users;
    }



}