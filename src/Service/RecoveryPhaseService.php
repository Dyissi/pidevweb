<?php 

namespace App\Service;

use App\Entity\Recoveryplan;

class RecoveryPhaseService
{
    public function getRecoveryPhase(Recoveryplan $recoveryPlan): string
    {
        $startDate = $recoveryPlan->getRecoveryStartDate();
        $endDate = $recoveryPlan->getRecoveryEndDate();
        $today = new \DateTime();

        if (!$endDate) {
            return 'Unknown (no end date set)';
        }

        $totalDays = $startDate->diff($endDate)->days;
        $daysPassed = $startDate->diff($today)->days;

        if ($totalDays <= 0) {
            return 'Invalid recovery period';
        }

        $progress = ($daysPassed / $totalDays) * 100;

        if ($progress < 33) {
            return 'Early Phase';
        } elseif ($progress < 66) {
            return 'Mid Phase';
        } elseif ($progress <= 100) {
            return 'Late Phase';
        } else {
            return 'Recovery Complete';
        }
    }
}