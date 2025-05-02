<?php

namespace App\Service;

use App\Entity\Injury;
use App\Entity\Recoveryplan;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RecoveryPhaseService
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

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

        $progress = ($daysPassed / $totalDays) * 10;

        if ($progress < 2) {
            return 'Early Phase';
        } elseif ($progress < 5) {
            return 'Mid Phase';
        } elseif ($progress <= 10) {
            return 'Late Phase';
        } else {
            return 'Recovery Complete';
        }
    }

    public function getChatBotResponse(string $message, User $user): string
    {
        $this->logger->info('Processing message: ' . $message . ' for user: ' . get_class($user));

        $message = strtolower(trim($message));
        $injuryRepository = $this->entityManager->getRepository(Injury::class);
        $recoveryPlanRepository = $this->entityManager->getRepository(Recoveryplan::class);

        $injuries = $injuryRepository->findBy(['user' => $user]);
        $recoveryPlans = $recoveryPlanRepository->findBy(['user' => $user]);

        $this->logger->info('Found ' . count($injuries) . ' injuries and ' . count($recoveryPlans) . ' recovery plans');

        $injuryTypes = array_filter(array_map(function ($injury) {
            $type = $injury->getInjuryType();
            if ($type === null) {
                $this->logger->warning('Null injury type found');
                return null;
            }
            return $type;
        }, $injuries));

        $plans = $this->getPlans($injuryTypes);

        // Handle greetings
        if (str_contains($message, 'hello') || str_contains($message, 'hi') || str_contains($message, 'hey') || str_contains($message, 'yo')) {
            $this->logger->info('Returning welcome message');
            return $this->getWelcomeMessage();
        }

        // Handle no data case
        if (empty($injuries) && empty($recoveryPlans)) {
            return 'No recovery data found.';
        }

        $response = '';
        $nutritionAdded = false;
        $exerciseAdded = false;

        // Injury details
        if (str_contains($message, 'what are my injuries') || str_contains($message, 'tell me about my injuries') || str_contains($message, 'injury details') || str_contains($message, 'my injuries')) {
            $this->logger->info('Processing injury details query');
            if (empty($injuries)) {
                $response .= "You have no recorded injuries.\n";
            } else {
                $response .= "Your injuries:\n";
                foreach ($injuries as $injury) {
                    $response .= 'Type: ' . ($injury->getInjuryType() ?? 'Unknown') . "\n";
                    $response .= 'Date: ' . ($injury->getInjuryDate() ? $injury->getInjuryDate()->format('Y-m-d') : 'Unknown') . "\n";
                    $response .= "\n";
                }
            }
        }

        // Recovery phase or progress
        if (str_contains($message, 'recovery phase') || str_contains($message, 'progress')) {
            $this->logger->info('Processing recovery phase query');
            foreach ($recoveryPlans as $plan) {
                $phase = $this->getRecoveryPhase($plan);
                $daysLeft = $this->calculateRecoveryDays($plan);
                $response .= "You're in the $phase phase. You have $daysLeft days left.\n";
            }
        }

        // Nutrition or exercise (use first injury if available)
        if (!empty($injuryTypes)) {
            if ((str_contains($message, 'eat') || str_contains($message, 'nutrition') || str_contains($message, 'diet') || str_contains($message, 'food')) && !$nutritionAdded) {
                $this->logger->info('Processing nutrition query');
                $response .= 'Recommended diet: ' . $plans['nutrition'] . "\n";
                $nutritionAdded = true;
            }
            if ((str_contains($message, 'exercise') || str_contains($message, 'workout') || str_contains($message, 'training') || str_contains($message, 'activity')) && !$exerciseAdded) {
                $this->logger->info('Processing exercise query');
                $response .= 'Recommended activity: ' . $plans['exercise'] . "\n";
                $exerciseAdded = true;
            }
        }

        // Full recovery plan details
        if (str_contains($message, 'recovery plan') || str_contains($message, 'show my recovery plan') || str_contains($message, 'details of my recovery')) {
            $this->logger->info('Processing recovery plan query');
            $response .= "Your recovery plans:\n";
            foreach ($recoveryPlans as $plan) {
                $response .= 'Goal: ' . ($plan->getRecoveryGoal() ?? 'No goal set') . "\n";
                $response .= 'Start Date: ' . ($plan->getRecoveryStartDate() ? $plan->getRecoveryStartDate()->format('Y-m-d') : 'Unknown') . "\n";
                $response .= 'End Date: ' . ($plan->getRecoveryEndDate() ? $plan->getRecoveryEndDate()->format('Y-m-d') : 'Unknown') . "\n";
                $response .= "\n";
            }
        }

        // Fallback response
        if (empty($response)) {
            $this->logger->info('Returning fallback response');
            return "I didn't understand that. Try asking about your injuries, recovery phase, nutrition, or exercises.";
        }

        return trim($response);
    }

    private function getWelcomeMessage(): string
    {
        return <<<EOT
Welcome to the Recovery ChatDoc! 🤖 Here are some things you can ask:
- "What are my injuries?" → Get details about your recorded injuries.
- "What is my recovery phase?" → Get your current recovery phase and remaining days.
- "What should I eat for recovery?" → Get nutrition advice based on your injury.
- "What exercises can I do?" → Get activity recommendations.
- "Show my recovery plan" → View your full recovery plan details.
- "Hi" or "Hello" → See this message again.

How can I assist you today?
EOT;
    }

    private function calculateRecoveryDays(Recoveryplan $recoveryPlan): int
    {
        $endDate = $recoveryPlan->getRecoveryEndDate();
        if (!$endDate) {
            return 0;
        }
        $today = new \DateTime();
        $diff = $today->diff($endDate);
        return $diff->days * ($diff->invert ? -1 : 1);
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
                case 'sprain':
                    $nutritionPlans[] = "Anti-inflammatory foods like turmeric, ginger, and leafy greens.";
                    $exercisePlans[] = "Gentle stretching and ice therapy.";
                    break;
                case 'fracture':
                    $nutritionPlans[] = "Calcium and vitamin D-rich foods like dairy, almonds, and fish.";
                    $exercisePlans[] = "Rest and light movement when advised by a doctor.";
                    break;
                case 'concussion':
                    $nutritionPlans[] = "Omega-3-rich foods like salmon and flaxseeds.";
                    $exercisePlans[] = "Complete rest; avoid screen time.";
                    break;
                case 'bruise':
                    $nutritionPlans[] = "Iron-rich foods like spinach and red meat.";
                    $exercisePlans[] = "Cold compress and light massage.";
                    break;
                default:
                    $this->logger->warning('Unknown injury type: ' . $normalizedType);
                    $nutritionPlans[] = "Balanced diet with proteins and vitamins.";
                    $exercisePlans[] = "Consult your physician.";
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