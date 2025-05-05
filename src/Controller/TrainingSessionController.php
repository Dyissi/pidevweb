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
use App\Service\NinjaApiService;
use Knp\Component\Pager\PaginatorInterface;



#[Route('/training/session')]
final class TrainingSessionController extends AbstractController
{
    private $ninjaApiService;

    public function __construct(NinjaApiService $ninjaApiService)
    {
        $this->ninjaApiService = $ninjaApiService;
    }

    #[Route(name: 'app_training_session_index', methods: ['GET'])]
    public function index(Request $request, TrainingSessionRepository $trainingSessionRepository, PaginatorInterface $paginator): Response
    {
        $focus = $request->query->get('focus', 'all');
        $filter = $request->query->get('filter', 'all');
        $limit = $request->query->getInt('limit', 10);

        // Get filtered data from repository
        $sessions = $trainingSessionRepository->findFiltered($filter, $focus);

        // Create pagination
        $pagination = $paginator->paginate(
            $sessions,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('training_session/index.html.twig', [
            'training_sessions' => $pagination,
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

    #[Route('/exercises/{focus}', name: 'app_training_session_exercises', methods: ['GET'], priority: 1000)]
    public function getExercises(string $focus, LoggerInterface $logger): JsonResponse
    {
        try {
            $logger->info('Exercise request received', ['focus' => $focus]);
            
            // Validate focus parameter
            if (empty($focus)) {
                throw new \InvalidArgumentException('Focus parameter cannot be empty');
            }

            $exercises = $this->ninjaApiService->getExercisesByMuscle($focus);
            
            if (empty($exercises)) {
                $logger->info('No exercises found', ['focus' => $focus]);
                return new JsonResponse([
                    'message' => 'No exercises found for the selected category.',
                    'data' => []
                ]);
            }
            
            return new JsonResponse([
                'message' => 'Exercises found successfully',
                'data' => $exercises
            ]);
            
        } catch (\Exception $e) {
            $logger->error('Exercise API error', [
                'focus' => $focus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'An error occurred while fetching exercises. ';
            if (str_contains($e->getMessage(), '400')) {
                $errorMessage .= 'The API request was invalid. This might be due to maintenance or the free tier being temporarily unavailable.';
            } elseif (str_contains($e->getMessage(), '402')) {
                $errorMessage .= 'The free tier is currently unavailable. Please try again later or consider upgrading to premium.';
            } else {
                $errorMessage .= 'Please try again later.';
            }
            
            return new JsonResponse([
                'error' => true,
                'message' => $errorMessage
            ], 500);
        }
    }

    #[Route('/analysis', name: 'app_training_session_analysis', methods: ['GET'])]
    public function analysis(Request $request, TrainingSessionRepository $trainingSessionRepository, PaginatorInterface $paginator): Response
    {
        $limit = $request->query->getInt('limit', 10);
        
        $query = $trainingSessionRepository->createQueryBuilder('s')
            ->orderBy('s.sessionStartTime', 'DESC');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('training_session/analysis.html.twig', [
            'training_sessions' => $pagination
        ]);
    }
}
