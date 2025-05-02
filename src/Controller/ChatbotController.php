<?php

namespace App\Controller;

use App\Service\RecoveryPhaseService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    private $logger;
    private $recoveryPhaseService;

    public function __construct(LoggerInterface $logger, RecoveryPhaseService $recoveryPhaseService)
    {
        $this->logger = $logger;
        $this->recoveryPhaseService = $recoveryPhaseService;
    }

    #[Route('/show', name: 'chatbot_show')]
    public function show(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->logger->warning('No user logged in for chatbot access');
            return $this->redirectToRoute('app_login');
        }

        $this->logger->info('Fetching data for user: ' . get_class($user));

        // Handle AJAX request for message processing
        if ($request->isXmlHttpRequest()) {
            $message = $request->request->get('message', '');
            if (empty($message)) {
                return $this->json(['error' => 'Empty message'], 400);
            }

            $response = $this->recoveryPhaseService->getChatBotResponse($message, $user);
            return $this->json(['response' => $response]);
        }

        return $this->render('recoveryplan/chatbot.html.twig', [
            'back_route' => 'app_home',
        ]);
    }
}