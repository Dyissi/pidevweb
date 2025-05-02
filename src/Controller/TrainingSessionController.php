<?php

namespace App\Controller;

use App\Entity\TrainingSession;
use App\Form\TrainingSessionType;
use App\Repository\TrainingSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\WgerApiService;



#[Route('/training/session')]
final class TrainingSessionController extends AbstractController
{
    private $wgerApiService;

    public function __construct(WgerApiService $wgerApiService)
    {
        $this->wgerApiService = $wgerApiService;
    }

    #[Route(name: 'app_training_session_index', methods: ['GET'])]
    public function index(Request $request, TrainingSessionRepository $trainingSessionRepository): Response
    {
        $focus = $request->query->get('focus', 'all');
        $filter = $request->query->get('filter', 'all');

        // Get base query
        $sessions = $trainingSessionRepository->findAll();

        // Apply focus filter
        if ($focus !== 'all') {
            $sessions = array_filter($sessions, function($session) use ($focus) {
                return $session->getSessionFocus() === $focus;
            });
        }

        // Apply duration filter
        if ($filter === 'longest') {
            usort($sessions, function($a, $b) {
                return $b->getSessionDuration() - $a->getSessionDuration();
            });
        } elseif ($filter === 'shortest') {
            usort($sessions, function($a, $b) {
                return $a->getSessionDuration() - $b->getSessionDuration();
            });
        }

        return $this->render('training_session/index.html.twig', [
            'training_sessions' => $sessions,
            'current_focus' => $focus,
            'current_filter' => $filter,
            'focus_choices' => ["Agility", "Strength", "Dribbling", "Endurance", "Sprint", "Speed"]
        ]);
    }
    #[Route('/new', name: 'app_training_session_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new TrainingSession();
        $form = $this->createForm(TrainingSessionType::class, $session);
        
        $form->handleRequest($request);
        
        // Debugging (temporary)
        dump([
            'submitted' => $form->isSubmitted(),
            'valid' => $form->isSubmitted() ? $form->isValid() : null,
            'errors' => $form->getErrors(true)
        ]);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($session);
            $entityManager->flush();
            
            $this->addFlash('success', 'Created successfully!');
            return $this->redirectToRoute('app_training_session_index');
        }
        
        return $this->render('training_session/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{sessionId}', name: 'app_training_session_show', methods: ['GET'])]
    public function show(TrainingSession $trainingSession): Response
    {
        return $this->render('training_session/show.html.twig', [
            'training_session' => $trainingSession,
        ]);
    }
    #[Route('/calendar', name: 'app_training_session_calendar', methods: ['GET'])]
    public function calendar(): Response
    {
        return $this->render('training_session/calendar.html.twig');    
    }
    
    #[Route('/calendar/data', name: 'app_training_session_calendar_data', methods: ['GET'])]
    public function calendarData(TrainingSessionRepository $repository): JsonResponse
    {
        $sessions = $repository->findAll();
        $events = [];
    
        foreach ($sessions as $session) {
            $start = $session->getSessionStartTime();
            if (!$start instanceof \DateTime) {
                continue;
            }
    
            // Create end time safely
            $end = clone $start;
            $end->modify('+'.$session->getSessionDuration().' minutes');
    
            $events[] = [
                'id' => $session->getSessionId(),
                'title' => $session->getSessionFocus(),
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end' => $end->format('Y-m-d\TH:i:s'),
                'url' => $this->generateUrl('app_training_session_show', ['sessionId' => $session->getSessionId()]),
                'backgroundColor' => $this->getColorForFocus($session->getSessionFocus()),
                'borderColor' => $this->getColorForFocus($session->getSessionFocus()),
                'extendedProps' => [
                    'description' => sprintf(
                        "Team: %s\nLocation: %s\nNotes: %s",
                        $session->getTeam() ? $session->getTeam()->getTeamName() : 'N/A',
                        $session->getLocation()->getLocationName(),
                        $session->getSessionNotes() ?: 'No notes'
                    )
                ]
            ];
        }
    
        return new JsonResponse($events);
    }

    #[Route('/calendar-events', name: 'app_training_session_calendar_events', methods: ['GET'])]
    public function calendarEvents(TrainingSessionRepository $trainingSessionRepository): JsonResponse
    {
        $trainingSessions = $trainingSessionRepository->findAll();
        $events = [];

        foreach ($trainingSessions as $session) {
            $start = $session->getSessionStartTime();
            $end = clone $start;
            $end->modify('+' . $session->getSessionDuration() . ' minutes');

            $events[] = [
                'id' => $session->getSessionId(),
                'title' => $session->getSessionFocus(),
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end' => $end->format('Y-m-d\TH:i:s'),
                'extendedProps' => [
                    'focus' => $session->getSessionFocus(),
                    'location' => $session->getLocation() ? $session->getLocation()->getLocationName() : 'No Location',
                    'team' => $session->getTeam() ? $session->getTeam()->getTeamName() : 'No Team',
                    'notes' => $session->getSessionNotes() ?: 'No notes'
                ],
                'className' => 'training-focus-' . strtolower($session->getSessionFocus())
            ];
        }

        return new JsonResponse($events);
    }

    private function getColorForFocus(string $focus): string
    {
        $colors = [
            'Fitness' => '#ff7675',
            'Technique' => '#74b9ff',
            'Tactics' => '#55efc4',
            'Match' => '#a29bfe'
        ];
        return $colors[$focus] ?? '#34495e';
    }


    

    #[Route('/{sessionId}/edit', name: 'app_training_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TrainingSession $trainingSession, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TrainingSessionType::class, $trainingSession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_training_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training_session/edit.html.twig', [
            'training_session' => $trainingSession,
            'form' => $form,
        ]);
    }

    #[Route('/{sessionId}', name: 'app_training_session_delete', methods: ['POST'])]
    public function delete(Request $request, TrainingSession $trainingSession, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trainingSession->getSessionId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($trainingSession);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_training_session_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/exercises/{focus}', name: 'app_training_session_exercises', methods: ['GET'])]
    public function getExercises(string $focus): JsonResponse
    {
        try {
            // Direct pass-through of the category to the API service
            $exercises = $this->wgerApiService->getExercisesByCategory($focus);
            return new JsonResponse($exercises);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to fetch exercises. Please try again.'], 500);
        }
    }
}
