<?php

namespace App\Controller;

use App\Entity\Results;
use App\Form\ResultsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/results')]
final class ResultsController extends AbstractController
{
    #[Route(name: 'app_results_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $results = $entityManager
            ->getRepository(Results::class)
            ->findAll();

        return $this->render('results/index.html.twig', [
            'results' => $results,
        ]);
    }

    #[Route('/new', name: 'app_results_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $result = new Results();
        $form = $this->createForm(ResultsType::class, $result);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($result);
            $entityManager->flush();

            return $this->redirectToRoute('app_results_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('results/new.html.twig', [
            'result' => $result,
            'form' => $form,
        ]);
    }

    #[Route('/{tournament}', name: 'app_results_show', methods: ['GET'])]
    public function show(Results $result): Response
    {
        return $this->render('results/show.html.twig', [
            'result' => $result,
        ]);
    }

    #[Route('/{tournament}/edit', name: 'app_results_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Results $result, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResultsType::class, $result);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_results_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('results/edit.html.twig', [
            'result' => $result,
            'form' => $form,
        ]);
    }

    #[Route('/{tournament}', name: 'app_results_delete', methods: ['POST'])]
    public function delete(Request $request, Results $result, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$result->getTournament(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($result);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_results_index', [], Response::HTTP_SEE_OTHER);
    }
}
