<?php

namespace App\Controller;

use App\Entity\Recoveryplan;
use App\Service\RecoveryPhaseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    #[Route('/show', name: 'chatbot_show')]
    public function show(EntityManagerInterface $em, RecoveryPhaseService $phaseService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $recoveryPlan = $em->getRepository(Recoveryplan::class)->findOneBy(['user' => $user]);
        if (!$recoveryPlan) {
            return $this->render('recoveryplan/chatbot.html.twig', [
                'error' => 'No recovery plan found.',
            ]);
        }

        $injury = $recoveryPlan->getInjury();
        $phase = $phaseService->getRecoveryPhase($recoveryPlan);
        $nutritionPlan = $this->getNutritionPlan($injury ? $injury->getInjuryType() : 'Unknown', $phase);

        return $this->render('recoveryplan/chatbot.html.twig', [
            'injury' => $injury ? $injury->getInjuryType() : 'Unknown',
            'phase' => $phase,
            'nutrition' => $nutritionPlan,
            'recoveryData' => [
                'injuryType' => $injury ? $injury->getInjuryType() : 'Unknown',
                'recoveryPhase' => $phase,
                'nutritionPlan' => $nutritionPlan,
            ],
        ]);
    }

    private function getNutritionPlan(string $injuryType, string $phase): string
    {
        $basePlan = "Eat a balanced diet with protein (chicken, fish, legumes), healthy fats (avocado, nuts), and complex carbs (whole grains). Stay hydrated.";
        if (stripos($injuryType, 'fracture') !== false) {
            return $basePlan . " Include calcium-rich foods (dairy, leafy greens) and vitamin D for bone healing.";
        } elseif (stripos($injuryType, 'sprain') !== false) {
            return $basePlan . " Add anti-inflammatory foods (berries, turmeric) to reduce swelling.";
        }
        return $basePlan;
    }
}