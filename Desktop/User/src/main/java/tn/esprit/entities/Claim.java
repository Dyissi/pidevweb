package tn.esprit.entities;

import java.time.LocalDate;

public class Claim {
    private int claimId;
    private String claimDescription;
    private ClaimStatus claimStatus;
    private LocalDate claimDate;
    private ClaimCategory claimCategory;

    public Claim(int claimId, String claimDescription, ClaimStatus claimStatus, LocalDate claimDate, ClaimCategory claimCategory) {
        this.claimId = claimId;
        this.claimDescription = claimDescription;
        this.claimStatus = claimStatus;
        this.claimDate = claimDate;
        this.claimCategory = claimCategory;
    }

    public Claim(String claimDescription, ClaimStatus claimStatus, LocalDate claimDate, ClaimCategory claimCategory) {
        this.claimDescription = claimDescription;
        this.claimStatus = claimStatus;
        this.claimDate = claimDate;
        this.claimCategory = claimCategory;
    }

    public Claim() {
    }

    public int getClaimId() {
        return claimId;
    }

    public void setClaimId(int claimId) {
        this.claimId = claimId;
    }

    public String getClaimDescription() {
        return claimDescription;
    }

    public void setClaimDescription(String claimDescription) {
        this.claimDescription = claimDescription;
    }

    public ClaimStatus getClaimStatus() {
        return claimStatus;
    }

    public void setClaimStatus(ClaimStatus claimStatus) {
        this.claimStatus = claimStatus;
    }

    public LocalDate getClaimDate() {
        return claimDate;
    }

    public void setClaimDate(LocalDate claimDate) {
        this.claimDate = claimDate;
    }

    public ClaimCategory getClaimCategory() {
        return claimCategory;
    }

    public void setClaimCategory(ClaimCategory claimCategory) {
        this.claimCategory = claimCategory;
    }

    @Override
    public String toString() {
        return "Claim{" +
                "claimDescription='" + claimDescription + '\'' +
                ", claimStatus=" + claimStatus +
                ", claimDate=" + claimDate +
                ", claimCategory=" + claimCategory +
                '}';
    }
}




