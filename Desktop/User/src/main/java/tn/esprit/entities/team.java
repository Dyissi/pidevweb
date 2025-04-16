package tn.esprit.entities;

public class team {
    private int teamId;
    private String teamName;
    private int teamNath;
    private String teamTOS;
    private int teamW,teamL;


    public team(String teamName, int teamNath, String teamTOS, int teamW, int teamL) {
        this.teamNath = teamNath;
        this.teamW = teamW;
        this.teamL = teamL;
        this.teamName = teamName;
        this.teamTOS = teamTOS;
    }

    public int getTeamId() {
        return teamId;
    }

    public void setTeamId(int teamId) {
        this.teamId = teamId;
    }

    public int getTeamNath() {
        return teamNath;
    }

    public void setTeamNath(int teamNath) {
        this.teamNath = teamNath;
    }

    public int getTeamW() {
        return teamW;
    }

    public void setTeamW(int teamW) {
        this.teamW = teamW;
    }

    public int getTeamL() {
        return teamL;
    }

    public void setTeamL(int teamL) {
        this.teamL = teamL;
    }

    public String getTeamName() {
        return teamName;
    }

    public void setTeamName(String teamName) {
        this.teamName = teamName;
    }

    public String getTeamTOS() {
        return teamTOS;
    }

    public void setTeamTOS(String teamTOS) {
        this.teamTOS = teamTOS;
    }

    public team(int teamId, String teamName, int teamNath, String teamTOS, int teamW, int teamL) {
        this.teamId = teamId;
        this.teamNath = teamNath;
        this.teamW = teamW;
        this.teamL = teamL;
        this.teamName = teamName;
        this.teamTOS = teamTOS;
    }

    public team() {
    }

    @Override
    public String toString() {
        return "team{" +
                "teamId=" + teamId +
                ", teamName='" + teamName + '\'' +
                ", teamNath=" + teamNath +
                ", teamTOS='" + teamTOS + '\'' +
                ", teamW=" + teamW +
                ", teamL=" + teamL +
                '}';
    }
}
