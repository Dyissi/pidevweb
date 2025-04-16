package tn.esprit.entities;

import java.time.LocalDate;
import java.sql.Date;

public class tournament {
    private int tournamentId;
    private String tournamentName;
    private Date tournamentStartDate;
    private Date tournamentEndDate;
    private String tournamentLocation;
    private String tournamentTOS;
    private int tournamentNbteams;

    public tournament() {
    }

    public tournament(String tournamentName, Date tournamentStartDate, Date tournamentEndDate, String tournamentLocation, String tournamentTOS, int tournamentNbteams) {
        this.tournamentName = tournamentName;
        this.tournamentStartDate = tournamentStartDate;
        this.tournamentEndDate = tournamentEndDate;
        this.tournamentLocation = tournamentLocation;
        this.tournamentTOS = tournamentTOS;
        this.tournamentNbteams = tournamentNbteams;
    }

    public tournament(int tournamentId, String tournamentName, Date tournamentStartDate, Date tournamentEndDate, String tournamentLocation, String tournamentTOS, int tournamentNbteams) {
        this.tournamentId = tournamentId;
        this.tournamentName = tournamentName;
        this.tournamentStartDate = tournamentStartDate;
        this.tournamentEndDate = tournamentEndDate;
        this.tournamentLocation = tournamentLocation;
        this.tournamentTOS = tournamentTOS;
        this.tournamentNbteams = tournamentNbteams;
    }

    public int getTournamentId() {
        return tournamentId;
    }

    public void setTournamentId(int tournamentId) {
        this.tournamentId = tournamentId;
    }

    public String getTournamentName() {
        return tournamentName;
    }

    public void setTournamentName(String tournamentName) {
        this.tournamentName = tournamentName;
    }

    public Date getTournamentStartDate() {
        return tournamentStartDate;
    }

    public void setTournamentStartDate(Date tournamentStartDate) {
        this.tournamentStartDate = tournamentStartDate;
    }

    public Date getTournamentEndDate() {
        return tournamentEndDate;
    }

    public void setTournamentEndDate(Date tournamentEndDate) {
        this.tournamentEndDate = tournamentEndDate;
    }

    public String getTournamentLocation() {
        return tournamentLocation;
    }

    public void setTournamentLocation(String tournamentLocation) {
        this.tournamentLocation = tournamentLocation;
    }

    public String getTournamentTOS() {
        return tournamentTOS;
    }

    public void setTournamentTOS(String tournamentTOS) {
        this.tournamentTOS = tournamentTOS;
    }

    public int getTournamentNbteams() {
        return tournamentNbteams;
    }

    public void setTournamentNbteams(int tournamentNbteams) {
        this.tournamentNbteams = tournamentNbteams;
    }

    @Override
    public String toString() {
        return "tournament{" +
                "id=" + tournamentId +
                ", tournamentName='" + tournamentName + '\'' +
                ", tournamentStartDate=" + tournamentStartDate +
                ", tournamentEndDate=" + tournamentEndDate +
                ", tournamentLocation='" + tournamentLocation + '\'' +
                ", tournamentTOS='" + tournamentTOS + '\'' +
                ", tournamentNbteams=" + tournamentNbteams +
                '}';
    }
}

