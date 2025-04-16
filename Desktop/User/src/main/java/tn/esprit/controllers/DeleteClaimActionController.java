package tn.esprit.controllers;

import tn.esprit.entities.ClaimAction;
import tn.esprit.services.ClaimActionServices;

import java.sql.SQLException;

public class DeleteClaimActionController {

    private ClaimActionServices claimActionServices;

    public DeleteClaimActionController() {
        claimActionServices = new ClaimActionServices();
    }

    public void deleteClaimAction(ClaimAction claimAction) {
        try {
            claimActionServices.delete(claimAction);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}