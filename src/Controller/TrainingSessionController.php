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
use Psr\Log\LoggerInterface; // Add this use statement


#[Route('/training/session')]
final class TrainingSessionController extends AbstractController
{
    #[Route(name: 'app_training_session_index', methods: ['GET'])]
    public function index(TrainingSessionRepository $trainingSessionRepository): Response
    {
        return $this->render('training_session/index.html.twig', [
            'training_sessions' => $trainingSessionRepository->findAll(),
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
}
