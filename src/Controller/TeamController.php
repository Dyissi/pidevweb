<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/team')]
final class TeamController extends AbstractController
{
    #[Route(path: '/', name: 'app_team_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
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

        // Apply search term filter
        if (!empty($searchTerm)) {
            $queryBuilder->where('LOWER(t.teamName) LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%');
        }

        // Apply sport type filter
        if (!empty($filterTeamSport)) {
            $queryBuilder->andWhere('t.teamTypeOfSport = :sport')
                ->setParameter('sport', $filterTeamSport);
        }

        $queryBuilder->orderBy('t.' . $sort, $direction);

        // Paginate results
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10 // Items per page
        );

        return $this->render('team/index.html.twig', [
            'teams' => $pagination,
            'sort' => $sort,
            'direction' => $direction,
            'searchTerm' => $searchTerm,
            'filterTeamSport' => $filterTeamSport,
        ]);
    }

    #[Route(path: '/search', name: 'app_team_search', methods: ['GET'])]
    public function searchTeams(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
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

        // Apply search term filter
        if (!empty($searchTerm)) {
            $queryBuilder->where('LOWER(t.teamName) LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%');
        }

        // Apply sport type filter
        if (!empty($filterTeamSport)) {
            $queryBuilder->andWhere('t.teamTypeOfSport = :sport')
                ->setParameter('sport', $filterTeamSport);
        }

        $queryBuilder->orderBy('t.' . $sort, $direction);

        // Paginate results
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10 // Items per page
        );

        // Render the index page with the filtered teams
        return $this->render('team/index.html.twig', [
            'teams' => $pagination,
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
        $team->setTeamWins(0); // Set default wins to 0
        $team->setTeamLosses(0); // Set default losses to 0
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
}