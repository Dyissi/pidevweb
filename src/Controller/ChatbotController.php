<?php

namespace App\Controller;

use App\Entity\Recoveryplan;
use App\Entity\Injury;
use App\Service\RecoveryPhaseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/show', name: 'chatbot_show')]
    public function show(EntityManagerInterface $em, RecoveryPhaseService $phaseService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->logger->warning('No user logged in for chatbot access');
            return $this->redirectToRoute('app_login');
        }

        $this->logger->info('Fetching data for user: ' . get_class($user));

        $recoveryPlans = $em->getRepository(Recoveryplan::class)->findBy(['user' => $user]);
        $injuries = $em->getRepository(Injury::class)->findBy(['user' => $user]);
        $injuryTypes = array_map(function ($injury) {
            $type = $injury->getInjuryType();
            $this->logger->info('Found injury type: ' . $type);
            return $type;
        }, $injuries);

        $this->logger->info('Injury types for user: ' . json_encode($injuryTypes));
        $this->logger->info('Recovery plans count: ' . count($recoveryPlans));

        $plans = $this->getPlans($injuryTypes);

        $recoveryPlanData = array_map(function ($plan) use ($phaseService) {
            $phase = $phaseService->getRecoveryPhase($plan) ?? 'Unknown';
            $this->logger->info('Recovery plan phase: ' . $phase);
            return [
                'goal' => $plan->getRecoveryGoal() ?? 'No goal set',
                'phase' => $phase,
                'startDate' => $plan->getRecoveryStartDate() ? $plan->getRecoveryStartDate()->format('Y-m-d') : 'Unknown',
                'endDate' => $plan->getRecoveryEndDate() ? $plan->getRecoveryEndDate()->format('Y-m-d') : 'Unknown',
            ];
        }, $recoveryPlans);

        return $this->render('recoveryplan/chatbot.html.twig', [
            'recoveryData' => [
                'injuries' => $injuryTypes,
                'recoveryPlans' => $recoveryPlanData,
                'nutritionPlan' => $plans['nutrition'],
                'exercisePlan' => $plans['exercise'],
            ],
        ]);
    }

    private function getPlans(array $injuryTypes): array
    {
        $this->logger->info('Generating plans for injury types: ' . json_encode($injuryTypes));

        $baseNutrition = "Eat a balanced diet with protein (chicken, fish, legumes), healthy fats (avocado, nuts), and complex carbs (whole grains). Stay hydrated.";
        $baseExercise = "Consult your doctor before starting any exercise. Rest is key during early recovery.";

        if (empty($injuryTypes)) {
            $this->logger->info('No injuries found, returning default plans');
            return [
                'nutrition' => "You may stick to your regular diet.",
                'exercise' => "Stick to your regular exercise routine or try light cardio like walking."
            ];
        }

        $nutritionPlans = [];
        $exercisePlans = [];

        foreach ($injuryTypes as $injuryType) {
            $normalizedType = strtolower(trim($injuryType));
            $this->logger->info('Processing injury type: ' . $normalizedType);
            switch ($normalizedType) {
                case 'bruise':
                    $nutritionPlans[] = "Vitamin C-rich foods (citrus, bell peppers) for tissue repair.";
                    $exercisePlans[] = "Gentle range-of-motion exercises after initial rest. Avoid pressure on the bruised area.";
                    break;
                case 'concussion':
                    $nutritionPlans[] = "Omega-3 foods (salmon, walnuts) to support brain health.";
                    $exercisePlans[] = "Avoid physical activity until cleared by a doctor. Start with light walking if symptom-free.";
                    break;
                case 'fracture':
                    $nutritionPlans[] = "Calcium-rich foods (dairy, leafy greens) and vitamin D for bone healing.";
                    $exercisePlans[] = "Immobilize the area as directed. Gentle, non-weight-bearing exercises after initial healing.";
                    break;
                case 'sprain':
                    $nutritionPlans[] = "Anti-inflammatory foods (berries, turmeric) to reduce swelling.";
                    $exercisePlans[] = "Rest initially, then gentle stretching and strengthening exercises as advised.";
                    break;
                default:
                    $this->logger->warning('Unknown injury type: ' . $normalizedType);
                    $nutritionPlans[] = "No specific nutrition recommendations for this injury type.";
                    $exercisePlans[] = "Consult your doctor for appropriate exercises.";
                    break;
            }
        }

        $nutritionPlan = $baseNutrition . (count($nutritionPlans) > 0 ? " " . implode(" Also, ", array_unique($nutritionPlans)) : "");
        $exercisePlan = $baseExercise . (count($exercisePlans) > 0 ? " " . implode(" Also, ", array_unique($exercisePlans)) : "");

        $this->logger->info('Generated nutrition plan: ' . $nutritionPlan);
        $this->logger->info('Generated exercise plan: ' . $exercisePlan);

        return [
            'nutrition' => $nutritionPlan,
            'exercise' => $exercisePlan
        ];
    }
}