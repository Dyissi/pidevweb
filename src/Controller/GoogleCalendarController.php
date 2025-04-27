<?php

namespace App\Controller;

use App\Entity\Recoveryplan;
use App\Service\GoogleCalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calendar')]
class GoogleCalendarController extends AbstractController
{
    #[Route('/auth', name: 'google_auth')]
    public function auth(GoogleCalendarService $calendarService): Response
    {
        return $this->redirect($calendarService->createAuthUrl());
    }

    #[Route('/auth/google/callback', name: 'google_callback')]
    public function callback(Request $request, GoogleCalendarService $calendarService, SessionInterface $session): Response
    {
        $code = $request->query->get('code');
        if ($code) {
            $calendarService->setAccessToken($code);
            $session->set('google_access_token', $calendarService->getClient()->getAccessToken());
        }
        return $this->redirectToRoute('calendar_show');
    }

    #[Route('/show', name: 'calendar_show')]
public function show(EntityManagerInterface $em, GoogleCalendarService $calendarService, SessionInterface $session): Response
{
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $accessToken = $session->get('google_access_token');
    if (!$accessToken) {
        return $this->redirectToRoute('google_auth');
    }

    $calendarService->getClient()->setAccessToken($accessToken);

    // Set date range for events (current month)
    $timeMin = new \DateTime('2000-01-01'); 
    $timeMax = new \DateTime('2100-01-01');    

    // Fetch recovery plans and existing events with date range
    $recoveryPlans = $em->getRepository(Recoveryplan::class)->findBy(['user' => $user]);
    $existingEvents = $calendarService->getEvents('primary', $timeMin, $timeMax);

    foreach ($recoveryPlans as $plan) {
        $startEventSummary = 'Recover Start: ' . $plan->getRecoveryGoal();
        $endEventSummary = 'Recover End: ' . $plan->getRecoveryGoal();

        // Check if start event exists
        $startEventExists = false;
        foreach ($existingEvents as $event) {
            if ($event->getSummary() === $startEventSummary && $event->getStart()->getDate() === $plan->getRecoveryStartDate()->format('Y-m-d')) {
                $startEventExists = true;
                break;
            }
        }

        // Create start event if it doesn't exist
        if (!$startEventExists) {
            $calendarService->createEvent(
                'primary',
                $startEventSummary,
                $plan->getRecoveryStartDate(),
                $plan->getRecoveryStartDate()
            );
        }

        // Check if end event exists
        if ($plan->getRecoveryEndDate()) {
            $endEventExists = false;
            foreach ($existingEvents as $event) {
                if ($event->getSummary() === $endEventSummary && $event->getStart()->getDate() === $plan->getRecoveryEndDate()->format('Y-m-d')) {
                    $endEventExists = true;
                    break;
                }
            }

            // Create end event if it doesn't exist
            if (!$endEventExists) {
                $calendarService->createEvent(
                    'primary',
                    $endEventSummary,
                    $plan->getRecoveryEndDate(),
                    $plan->getRecoveryEndDate()
                );
            }
        }
    }

    // Fetch all events for the current month
    $events = $calendarService->getEvents('primary', $timeMin, $timeMax);

    return $this->render('recoveryplan/calendar.html.twig', [
        'events' => $events,
    ]);
}
}