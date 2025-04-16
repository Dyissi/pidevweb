package tn.esprit.controllers;

import tn.esprit.entities.Claim;
import tn.esprit.services.ClaimServices;

import java.sql.SQLException;

public class DeleteClaimController {

    private ClaimServices claimServices;

    public DeleteClaimController() {
        claimServices = new ClaimServices();
    }

    public void deleteClaim(Claim claim) {
        try {
            claimServices.delete(claim);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
