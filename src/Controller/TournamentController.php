<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Entity\Team;
use App\Entity\Results;
use App\Form\TournamentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tournament')]
final class TournamentController extends AbstractController
{
    #[Route(path: '/', name: 'app_tournament_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get search and filter parameters from query
        $searchTerm = trim($request->query->get('search', ''));
        $filterSport = $request->query->get('sport', '');
        $filterStartDate = $request->query->get('start_date', '');

        // Create a query builder to fetch tournaments
        $queryBuilder = $entityManager->getRepository(Tournament::class)->createQueryBuilder('t');

        // Apply search term filter
        if (!empty($searchTerm)) {
            $queryBuilder->where('LOWER(t.tournamentName) LIKE :searchTerm')
                ->orWhere('LOWER(t.tournamentTOS) LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%');
        }

        // Apply sport type filter
        if (!empty($filterSport)) {
            $queryBuilder->andWhere('t.tournamentTOS = :sport')
                ->setParameter('sport', $filterSport);
        }

        // Apply start date filter
        if (!empty($filterStartDate)) {
            $queryBuilder->andWhere('t.tournamentStartDate >= :startDate')
                ->setParameter('startDate', new \DateTime($filterStartDate));
        }

        $tournaments = $queryBuilder->getQuery()->getResult();

        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournaments,
            'searchTerm' => $searchTerm,
            'filterSport' => $filterSport,
            'filterStartDate' => $filterStartDate,
        ]);
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
    public function manage(Request $request, Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        // Fetch all teams with the same type of sport as the tournament
        $allTeams = $entityManager->getRepository(Team::class)->findBy([
            'teamTypeOfSport' => $tournament->getTournamentTOS(),
        ]);

        // Simulate teams currently in the tournament (stored in session)
        $teamsInTournament = $request->getSession()->get('teams_in_tournament_' . $tournament->getId(), []);

        // Check if the tournament is locked (winner assigned)
        $isLocked = $tournament->getTournamentWinner() !== null;

        // Handle assigning a winner and locking the tournament
        if (!$isLocked && $request->isMethod('POST') && $request->request->has('assign_winner')) {
            $winnerId = $request->request->get('assign_winner');
            if (in_array($winnerId, $teamsInTournament)) {
                // Assign the winner
                $tournament->setTournamentWinner($winnerId);
                $isLocked = true; // Lock the tournament immediately

                // Increment wins for the winning team
                $winningTeam = $entityManager->getRepository(Team::class)->find($winnerId);
                $winningTeam->setTeamWins($winningTeam->getTeamWins() + 1);

                // Increment losses for all other teams in the tournament
                foreach ($teamsInTournament as $teamId) {
                    if ($teamId != $winnerId) {
                        $losingTeam = $entityManager->getRepository(Team::class)->find($teamId);
                        $losingTeam->setTeamLosses($losingTeam->getTeamLosses() + 1);
                    }
                }

                // Create and persist a Results object for the winner
                $result = new Results();
                $result->setTeam($winningTeam);
                $result->setTournament($tournament);
                $entityManager->persist($result);
            }
        }

        // Handle adding a team (only if the tournament is not locked)
        if (!$isLocked && $request->isMethod('POST') && $request->request->has('add_team')) {
            $teamId = $request->request->get('add_team');
            if (!in_array($teamId, $teamsInTournament)) {
                $teamsInTournament[] = $teamId;
                $tournament->setTournamentNbteams($tournament->getTournamentNbteams() + 1);
            }
        }

        // Handle removing a team (only if the tournament is not locked)
        if (!$isLocked && $request->isMethod('POST') && $request->request->has('remove_team')) {
            $teamId = $request->request->get('remove_team');
            if (($key = array_search($teamId, $teamsInTournament)) !== false) {
                unset($teamsInTournament[$key]);
                $tournament->setTournamentNbteams($tournament->getTournamentNbteams() - 1);
            }
        }

        // Save the updated teams in the session
        $request->getSession()->set('teams_in_tournament_' . $tournament->getId(), $teamsInTournament);

        // Persist changes to the database
        $entityManager->persist($tournament);
        $entityManager->flush();

        // Render the manage page
        return $this->render('tournament/manage.html.twig', [
            'tournament' => $tournament,
            'allTeams' => $allTeams,
            'teamsInTournament' => $teamsInTournament,
            'isLocked' => $isLocked,
        ]);
    }
}