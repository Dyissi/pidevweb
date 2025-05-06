<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Results;

#[Route('/team')]
final class TeamController extends AbstractController
{
    #[Route(path: '/', name: 'app_team_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get sorting and filter parameters from query
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'asc');
        $searchTerm = trim($request->query->get('search', ''));
        $filterTeamSport = $request->query->get('sport', '');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['id', 'teamName', 'teamTypeOfSport', 'teamNbAthletes'];
        if (!in_array($sort, $allowedSortFields, true)) {
            $sort = 'id';
        }

        // Validate direction
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // Build query with sorting
        $queryBuilder = $entityManager
            ->getRepository(Team::class)
            ->createQueryBuilder('t');

        // Filter by coach
        /** @var User $user */
        $user = $this->getUser();
        if ($user && $user->getUserRole() === 'coach') {
            $queryBuilder->andWhere('t.coach = :coach')
                ->setParameter('coach', $user);
        }

        // Apply search term filter
        if (!empty($searchTerm)) {
            $queryBuilder->andWhere('LOWER(t.teamName) LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%');
        }

        // Apply sport type filter
        if (!empty($filterTeamSport)) {
            $queryBuilder->andWhere('t.teamTypeOfSport = :sport')
                ->setParameter('sport', $filterTeamSport);
        }

        $queryBuilder->orderBy('t.' . $sort, $direction);

        // Execute the query
        $teams = $queryBuilder->getQuery()->getResult();

        // Render the index page with the filtered teams
        return $this->render('team/index.html.twig', [
            'teams' => $teams,
            'sort' => $sort,
            'direction' => $direction,
            'searchTerm' => $searchTerm,
            'filterTeamSport' => $filterTeamSport,
        ]);
    }

    #[Route(path: '/search', name: 'app_team_search', methods: ['GET'])]
    public function searchTeams(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = trim($request->query->get('search', ''));
        $filterTeamSport = $request->query->get('sport', '');

        // Get sorting parameters from query
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'asc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['id', 'teamName', 'teamTypeOfSport', 'teamNbAthletes'];
        if (!in_array($sort, $allowedSortFields, true)) {
            $sort = 'id';
        }

        // Validate direction
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // Create a query builder to search teams
        $queryBuilder = $entityManager->getRepository(Team::class)->createQueryBuilder('t');

        // Filter by coach
        /** @var User $user */
        $user = $this->getUser();
        if ($user && $user->getUserRole() === 'coach') {
            $queryBuilder->andWhere('t.coach = :coach')
                ->setParameter('coach', $user);
        }

        // Apply search term filter
        if (!empty($searchTerm)) {
            $queryBuilder->andWhere('LOWER(t.teamName) LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%');
        }

        // Apply sport type filter
        if (!empty($filterTeamSport)) {
            $queryBuilder->andWhere('t.teamTypeOfSport = :sport')
                ->setParameter('sport', $filterTeamSport);
        }

        $queryBuilder->orderBy('t.' . $sort, $direction);

        // Execute the query
        $teams = $queryBuilder->getQuery()->getResult();

        // Render the index page with the filtered teams
        return $this->render('team/index.html.twig', [
            'teams' => $teams,
            'sort' => $sort,
            'direction' => $direction,
            'searchTerm' => $searchTerm,
            'filterTeamSport' => $filterTeamSport,
        ]);
    }

    #[Route(path: '/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $team->setTeamWins(0);
        $team->setTeamLosses(0);
        /** @var User $user */
        $user = $this->getUser();
        if (!$user || $user->getUserRole() !== 'coach') {
            throw $this->createAccessDeniedException('Only coaches can create teams.');
        }
        $team->setCoach($user);
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();

            $this->addFlash('success', 'Team created successfully!');

            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id<\d+>}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route(path: '/{id<\d+>}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Team updated successfully!');

            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team/edit.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id<\d+>}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();

            $this->addFlash('success', 'Team deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id<\d+>}/manage-players', name: 'app_team_manage_players', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_COACH')]
    public function managePlayers(Team $team, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST') && $request->request->has('add_players')) {
            $postData = $request->request->all();
            $athleteIds = isset($postData['athletes']) && is_array($postData['athletes']) ? $postData['athletes'] : [];

            if (empty($athleteIds)) {
                $this->addFlash('warning', 'No athletes were selected to add.');
                return $this->redirectToRoute('app_team_manage_players', ['id' => $team->getId()]);
            }

            $addedCount = 0;
            foreach ($athleteIds as $athleteId) {
                if (!is_scalar($athleteId)) {
                    continue;
                }
                $athlete = $entityManager->getRepository(User::class)->find($athleteId);
                if ($athlete && $athlete->getUserRole() === 'athlete' && !$athlete->getTeam()) {
                    $team->addUser($athlete);
                    $addedCount++;
                }
            }

            if ($addedCount > 0) {
                $entityManager->flush();
                $this->addFlash('success', sprintf('%d athlete(s) have been added to the team.', $addedCount));
            } else {
                $this->addFlash('warning', 'No eligible athletes were added. Ensure selected athletes are not already assigned to a team.');
            }

            return $this->redirectToRoute('app_team_manage_players', ['id' => $team->getId()]);
        }

        $availableAthletes = $entityManager->getRepository(User::class)->findBy([
            'user_role' => 'athlete',
            'team' => null,
        ]);

        $teamAthletes = $team->getUsers()->filter(function (User $user) {
            return $user->getUserRole() === 'athlete';
        });

        return $this->render('team/manage_players.html.twig', [
            'team' => $team,
            'availableAthletes' => $availableAthletes,
            'teamAthletes' => $teamAthletes,
        ]);
    }

    #[Route('/{id<\d+>}/manage-players/remove', name: 'app_team_remove_players', methods: ['POST'])]
    #[IsGranted('ROLE_COACH')]
    public function removePlayers(Team $team, Request $request, EntityManagerInterface $entityManager): Response
    {
        $postData = $request->request->all();
        $athleteIds = isset($postData['athletes']) && is_array($postData['athletes']) ? $postData['athletes'] : [];

        if (empty($athleteIds)) {
            $this->addFlash('warning', 'No athletes were selected to remove.');
            return $this->redirectToRoute('app_team_manage_players', ['id' => $team->getId()]);
        }

        $removedCount = 0;
        foreach ($athleteIds as $athleteId) {
            if (!is_scalar($athleteId)) {
                continue;
            }
            $athlete = $entityManager->getRepository(User::class)->find($athleteId);
            if ($athlete && $athlete->getTeam() === $team) {
                $team->removeUser($athlete);
                $removedCount++;
            }
        }

        if ($removedCount > 0) {
            $entityManager->flush();
            $this->addFlash('success', sprintf('%d athlete(s) have been removed from the team.', $removedCount));
        } else {
            $this->addFlash('warning', 'No eligible athletes were removed. Ensure selected athletes are part of this team.');
        }

        return $this->redirectToRoute('app_team_manage_players', ['id' => $team->getId()]);
    }

    #[Route(path: '/my-team', name: 'app_team_my_team', methods: ['GET'])]
    #[IsGranted('ROLE_ATHLETE')]
    public function myTeam(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $team = $user->getTeam();

        if (!$team) {
            $this->addFlash('warning', 'You are not assigned to any team.');
            return $this->redirectToRoute('app_home');
        }

        // Fetch teammates (athletes only)
        $teammates = $team->getUsers()->filter(function (User $user) {
            return $user->getUserRole() === 'athlete';
        });

        // Fetch tournaments won
        $tournamentsWon = $entityManager->getRepository(Results::class)
            ->findBy(['team' => $team]);

        // Generate vCard for the coach
        $coach = $team->getCoach();
        $vCard = null;
        if ($coach) {
            $fullName = trim($coach->getUserFname() . ' ' . $coach->getUserLname());
            $vCardLines = [
                'BEGIN:VCARD',
                'VERSION:3.0',
                'N:' . $coach->getUserLname() . ';' . $coach->getUserFname() . ';;;',
                'FN:' . $fullName,
                'EMAIL;TYPE=WORK:' . $coach->getUserEmail(),
            ];
            if ($coach->getUserNbr()) {
                $vCardLines[] = 'TEL;TYPE=CELL:' . $coach->getUserNbr();
            }
            $vCardLines[] = 'END:VCARD';
            $vCard = implode("\n", $vCardLines);
        }

        return $this->render('team/my_team.html.twig', [
            'team' => $team,
            'teammates' => $teammates,
            'tournamentsWon' => $tournamentsWon,
            'coachVCard' => $vCard,
        ]);
    }
}