package tn.esprit.entities;

import java.time.LocalDate;
import java.util.Date;

public class ClaimAction {
    private int claimActionId;
    private Claim claim;
    private ClaimActionType claimActionType;
    private LocalDate claimActionStartDate;
    private LocalDate claimActionEndDate;
    private String claimActionNotes;

    public ClaimAction(int claimActionId, Claim claim, ClaimActionType claimActionType, LocalDate claimActionStartDate, LocalDate claimActionEndDate, String claimActionNotes) {
        this.claimActionId = claimActionId;
        this.claim = claim;
        this.claimActionType = claimActionType;
        this.claimActionStartDate = claimActionStartDate;
        this.claimActionEndDate = claimActionEndDate;
        this.claimActionNotes = claimActionNotes;
    }

    public ClaimAction(Claim claim, ClaimActionType claimActionType, LocalDate claimActionStartDate, LocalDate claimActionEndDate, String claimActionNotes) {
        this.claim = claim;
        this.claimActionType = claimActionType;
        this.claimActionStartDate = claimActionStartDate;
        this.claimActionEndDate = claimActionEndDate;
        this.claimActionNotes = claimActionNotes;
    }

    public ClaimAction() {
    }

    public int getClaimActionId() {
        return claimActionId;
    }

    public void setClaimActionId(int claimActionId) {
        this.claimActionId = claimActionId;
    }

    public Claim getClaim() {
        return claim;
    }

    public void setClaim(Claim claim) {
        this.claim = claim;
    }

    public ClaimActionType getClaimActionType() {
        return claimActionType;
    }

    public void setClaimActionType(ClaimActionType claimActionType) {
        this.claimActionType = claimActionType;
    }

    public LocalDate getClaimActionStartDate() {
        return claimActionStartDate;
    }

    public void setClaimActionStartDate(LocalDate claimActionStartDate) {
        this.claimActionStartDate = claimActionStartDate;
    }

    public LocalDate getClaimActionEndDate() {
        return claimActionEndDate;
    }

    public void setClaimActionEndDate(LocalDate claimActionEndDate) {
        this.claimActionEndDate = claimActionEndDate;
    }

    public String getClaimActionNotes() {
        return claimActionNotes;
    }

    public void setClaimActionNotes(String claimActionNotes) {
        this.claimActionNotes = claimActionNotes;
    }

    @Override
    public String toString() {
        return "ClaimAction{" +
                "claimActionId=" + claimActionId +
                ", claim=" + claim +
                ", claimActionType=" + claimActionType +
                ", claimActionStartDate=" + claimActionStartDate +
                ", claimActionEndDate=" + claimActionEndDate +
                ", claimActionNotes='" + claimActionNotes + '\'' +
                '}';
    }
}
