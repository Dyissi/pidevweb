<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Entity\Team;
use App\Entity\Results;
use App\Form\TournamentType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use JMS\Serializer\SerializerInterface;

#[Route('/tournament')]
final class TournamentController extends AbstractController
{
    #[Route(path: '/', name: 'app_tournament_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $searchTerm = trim($request->query->get('search', ''));
        $filterSport = $request->query->get('sport', '');
        $filterStartDate = $request->query->get('start_date', '');

        $queryBuilder = $entityManager->getRepository(Tournament::class)->createQueryBuilder('t');

        try {
            if (!empty($searchTerm)) {
                $queryBuilder->andWhere('LOWER(t.tournamentName) LIKE :searchTerm OR LOWER(t.tournamentTOS) LIKE :searchTerm')
                    ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%');
            }

            if (!empty($filterSport)) {
                $queryBuilder->andWhere('t.tournamentTOS = :sport')
                    ->setParameter('sport', $filterSport);
            }

            if (!empty($filterStartDate)) {
                try {
                    $deadline = new \DateTime($filterStartDate);
                    $logger->debug('Parsed deadline date: ' . $deadline->format('Y-m-d'), ['input' => $filterStartDate]);
                    $queryBuilder->andWhere('t.tournamentEndDate <= :deadline')
                        ->setParameter('deadline', $deadline);
                } catch (\Exception $e) {
                    $logger->warning('Invalid deadline date provided: ' . $filterStartDate, ['exception' => $e->getMessage()]);
                    $this->addFlash('error', 'Invalid deadline date provided. Please use YYYY-MM-DD format.');
                }
            }

            $logger->debug('Executing query: ' . $queryBuilder->getQuery()->getSQL());
            $tournaments = $queryBuilder->getQuery()->getResult();
        } catch (\Exception $e) {
            $logger->error('Error executing tournament query: ' . $e->getMessage(), ['exception' => $e]);
            $tournaments = [];
            $this->addFlash('error', 'Failed to load tournaments. Please try again.');
        }

        $templateData = [
            'tournaments' => $tournaments,
            'searchTerm' => $searchTerm,
            'filterSport' => $filterSport,
            'filterStartDate' => $filterStartDate,
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('tournament/index.html.twig', $templateData);
        }

        return $this->render('tournament/index.html.twig', $templateData);
    }

    #[Route(path: '/new', name: 'app_tournament_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tournament);
            $entityManager->flush();

            return $this->redirectToRoute('app_tournament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournament/new.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id<\d+>}', name: 'app_tournament_show', methods: ['GET'])]
    public function show(Tournament $tournament): Response
    {
        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    #[Route(path: '/{id<\d+>}/edit', name: 'app_tournament_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tournament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id<\d+>}', name: 'app_tournament_delete', methods: ['POST'])]
    public function delete(Request $request, Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tournament->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tournament);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tournament_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{id<\d+>}/manage', name: 'app_tournament_manage', methods: ['GET', 'POST'])]
    public function manage(Request $request, Tournament $tournament, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $allTeams = $entityManager->getRepository(Team::class)->findBy([
            'teamTypeOfSport' => $tournament->getTournamentTOS(),
        ]);

        $sessionKey = 'teams_in_tournament_' . $tournament->getId();
        $teamsInTournament = $request->getSession()->get($sessionKey, []);

        $isLocked = $tournament->getTournamentWinner() !== null;

        if (!$isLocked && $request->isMethod('POST') && $request->request->has('add_team')) {
            $teamId = $request->request->get('add_team');
            $team = $entityManager->getRepository(Team::class)->find($teamId);
            if ($team && !in_array((string) $teamId, $teamsInTournament, true)) {
                $teamsInTournament[] = (string) $teamId;
                $tournament->setTournamentNbteams($tournament->getTournamentNbteams() + 1);
            } else {
                $this->addFlash('error', 'Invalid team or team already added.');
            }
        }

        if (!$isLocked && $request->isMethod('POST') && $request->request->has('remove_team')) {
            $teamId = $request->request->get('remove_team');
            if (($key = array_search((string) $teamId, $teamsInTournament)) !== false) {
                unset($teamsInTournament[$key]);
                $teamsInTournament = array_values($teamsInTournament);
                $tournament->setTournamentNbteams($tournament->getTournamentNbteams() - 1);
            }
        }

        $request->getSession()->set($sessionKey, $teamsInTournament);
        $logger->debug('Updated teams_in_tournament', [
            'tournamentId' => $tournament->getId(),
            'teamsInTournament' => $teamsInTournament
        ]);

        $entityManager->persist($tournament);
        $entityManager->flush();

        return $this->render('tournament/manage.html.twig', [
            'tournament' => $tournament,
            'allTeams' => $allTeams,
            'teamsInTournament' => $teamsInTournament,
            'isLocked' => $isLocked,
        ]);
    }

    #[Route('/{id}/bracket', name: 'app_tournament_bracket', methods: ['GET', 'POST'])]
    public function bracket(Tournament $tournament, Request $request, SessionInterface $session, EntityManagerInterface $entityManager, SerializerInterface $serializer, LoggerInterface $logger): Response
    {
        $sessionKey = "teams_in_tournament_{$tournament->getId()}";
        $teamIds = $session->get($sessionKey, []);
    
        // Fetch teams
        $teams = $entityManager->getRepository(Team::class)->findBy(['id' => $teamIds]);
    
        $teamNames = [];
        foreach ($teams as $team) {
            $teamNames[] = $team->getTeamName();
        }
    
        $teamCount = count($teamIds);
        $validTeamCounts = [2, 4, 8, 16];
        $isValidTeamCount = in_array($teamCount, $validTeamCounts);
    
        $logger->debug('Fetched teams', [
            'teamCount' => $teamCount,
            'teams' => array_map(fn($team) => ['id' => $team->getId(), 'name' => $team->getTeamName()], $teams)
        ]);
    
        // Create a new SerializationContext for serializing teams
        $teamsContext = \JMS\Serializer\SerializationContext::create()->setGroups(['default']);
        $serializedTeams = $serializer->serialize($teams, 'json', $teamsContext);
        $logger->debug('Serialized teams', ['serializedTeams' => $serializedTeams]);
    
        if ($isValidTeamCount && empty($teams)) {
            $logger->error('No valid teams found for provided team IDs', [
                'tournamentId' => $tournament->getId(),
                'teamIds' => $teamIds
            ]);
            $this->addFlash('error', 'No valid teams found. Please ensure teams are correctly assigned.');
        }
    
        $bracketData = $tournament->getBracketData();
        // Validate and fix bracketData if valid team count
        if ($isValidTeamCount && (empty($bracketData) || !is_array($bracketData) || !isset($bracketData['teams']) || !is_array($bracketData['teams']) || empty($bracketData['teams'][0]))) {
            // Shuffle teams to randomize matchups
            shuffle($teamNames);
            $pairedTeams = [];
            for ($i = 0; $i < $teamCount; $i += 2) {
                $team1 = $teamNames[$i];
                $team2 = isset($teamNames[$i + 1]) ? $teamNames[$i + 1] : 'TBD';
                $pairedTeams[] = [$team1, $team2];
            }
            $bracketData = ['teams' => [$pairedTeams], 'results' => [], 'winner' => null];
            $tournament->setBracketData($bracketData);
            $entityManager->persist($tournament);
            $entityManager->flush();
            $logger->info('Initialized new bracket data', ['bracketData' => $bracketData]);
        } elseif ($isValidTeamCount && !is_array($bracketData['teams'][0])) {
            // Handle case where teams is a string or invalid
            $logger->warning('Invalid bracketData.teams format, resetting', ['bracketData' => $bracketData]);
            shuffle($teamNames);
            $pairedTeams = [];
            for ($i = 0; $i < $teamCount; $i += 2) {
                $team1 = $teamNames[$i];
                $team2 = isset($teamNames[$i + 1]) ? $teamNames[$i + 1] : 'TBD';
                $pairedTeams[] = [$team1, $team2];
            }
            $bracketData = ['teams' => [$pairedTeams], 'results' => [], 'winner' => null];
            $tournament->setBracketData($bracketData);
            $entityManager->persist($tournament);
            $entityManager->flush();
            $logger->info('Reset bracket data due to invalid format', ['bracketData' => $bracketData]);
        }
    
        // Ensure results is always an array
        if (!isset($bracketData['results']) || !is_array($bracketData['results'])) {
            $bracketData['results'] = [];
            $tournament->setBracketData($bracketData);
            $entityManager->persist($tournament);
            $entityManager->flush();
            $logger->info('Reset bracketData.results to empty array due to invalid format', ['bracketData' => $bracketData]);
        }
    
        if ($request->isMethod('POST')) {
            $data = json_decode($request->request->get('bracket_data'), true);
            $logger->debug('Received bracket data', ['data' => $data]);
    
            if ($data && is_array($data) && isset($data['teams']) && is_array($data['teams'])) {
                try {
                    $logger->debug('Processing bracket data update', [
                        'teams' => $data['teams'],
                        'results' => $data['results'] ?? 'none',
                        'winner' => $data['winner'] ?? 'none'
                    ]);
    
                    $bracketData['teams'] = $data['teams'];
                    // Convert results to numeric values
                    $bracketData['results'] = isset($data['results']) && is_array($data['results']) ? array_map(function($round) {
                        if (!is_array($round)) return [];
                        return array_map(function($match) {
                            if (!is_array($match)) return [0, 0];
                            return array_map('intval', $match);
                        }, $round);
                    }, $data['results']) : [];
                    if (isset($data['winner'])) {
                        $bracketData['winner'] = $data['winner'];
                        if ($data['winner']) {
                            $winnerName = $data['winner'];
                            $winnerTeam = null;
                            foreach ($teams as $team) {
                                if ($team->getTeamName() === $winnerName) {
                                    $winnerTeam = $team;
                                    break;
                                }
                            }
                            if ($winnerTeam) {
                                $logger->info('Updating winner team stats', [
                                    'winnerTeamId' => $winnerTeam->getId(),
                                    'winnerTeamName' => $winnerTeam->getTeamName()
                                ]);
                                // Increment winner's wins
                                $winnerTeam->setTeamWins($winnerTeam->getTeamWins() + 1);
                                // Create Results entry for winner
                                $result = new Results();
                                $result->setTeam($winnerTeam);
                                $result->setTournament($tournament);
                                $entityManager->persist($result);
                                // Set tournament winner to lock the tournament
                                $tournament->setTournamentWinner($winnerTeam->getId());
    
                                // Update losses for other teams
                                foreach ($teams as $team) {
                                    if ($team->getId() !== $winnerTeam->getId()) {
                                        $team->setTeamLosses($team->getTeamLosses() + 1);
                                        $logger->info('Updating losing team stats', [
                                            'teamId' => $team->getId(),
                                            'teamName' => $team->getTeamName(),
                                            'newLosses' => $team->getTeamLosses()
                                        ]);
                                    }
                                }
                            } else {
                                $logger->warning('Winner team not found', ['winnerName' => $winnerName]);
                            }
                        }
                    }
                    $tournament->setBracketData($bracketData);
                    $entityManager->persist($tournament);
                    $entityManager->flush();
                    $logger->info('Bracket data successfully updated', [
                        'tournamentId' => $tournament->getId(),
                        'bracketData' => $bracketData
                    ]);
    
                    // Create a new SerializationContext for serializing updated bracket data
                    $bracketContext = \JMS\Serializer\SerializationContext::create()->setGroups(['default']);
                    $updatedBracketData = $serializer->serialize($tournament->getBracketData(), 'json', $bracketContext);
                    $logger->debug('Serialized updated bracket data', ['updatedBracketData' => $updatedBracketData]);
                    return new Response($updatedBracketData, 200, ['Content-Type' => 'application/json']);
                } catch (\Exception $e) {
                    $logger->error('Failed to update bracket data', [
                        'exception' => $e->getMessage(),
                        'data' => $data
                    ]);
                    return new Response('Error updating bracket data: ' . $e->getMessage(), 500);
                }
            }
            $logger->warning('Invalid bracket data format', ['data' => $data]);
            return new Response('Invalid bracket data', 400);
        }
    
        $logger->debug('Rendering bracket page', [
            'bracketData' => $bracketData,
            'teamCount' => $teamCount
        ]);
    
        return $this->render('tournament/bracket.html.twig', [
            'tournament' => $tournament,
            'bracketData' => $tournament->getBracketData(),
            'teams' => $teams,
            'teamsJson' => $serializedTeams,
            'isValidTeamCount' => $isValidTeamCount,
            'teamCount' => $teamCount
        ]);
    }
}