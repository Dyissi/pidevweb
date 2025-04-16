<?php

namespace App\Controller;

use App\Entity\Data;
use App\Form\DataType;
use App\Repository\DataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/data')]

final class DataController extends AbstractController
{
    
    #[Route(name: 'app_data_index', methods: ['GET'])]
    public function index(Request $request,DataRepository $dataRepository): Response
    {
        $filter = $request->query->get('filter');  // Get ?filter=value from URL

        if ($filter) {
            $data = $dataRepository->findFiltered($filter); // Use custom method in repo
        } else {
            $data = $dataRepository->findAll(); // Default behavior
        }
    
        return $this->render('data/index.html.twig', [
            'data' => $data,
            'selectedFilter' => $filter,
        ]);
    }

    #[Route('/new', name: 'app_data_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $data = new Data();
        $form = $this->createForm(DataType::class, $data);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($data);
            $em->flush();
    
            // Add a success flash message
            $this->addFlash('success', 'Performance data saved successfully!');
    
            // Redirect back to the same form (refresh)
            return $this->redirectToRoute('app_data_new');
        }
    
        return $this->render('data/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{performanceId}', name: 'app_data_show', methods: ['GET'])]
    public function show(int $performanceId, DataRepository $dataRepository): Response
    {
        $data = $dataRepository->find((int) $performanceId);
        
        if (!$data) {
            throw $this->createNotFoundException('Performance data not found');
        }
        
        return $this->render('data/show.html.twig', [
            'data' => $data,
        ]);
    }
    #[Route('/{performanceId}/edit', name: 'app_data_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Data $data, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DataType::class, $data);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', 'Performance data updated successfully!');
                return $this->redirectToRoute('app_data_index');
            }
            $this->addFlash('error', 'Please correct the errors in the form.');
        }
    
        return $this->render('data/edit.html.twig', [
            'data' => $data,
            'form' => $form,
        ]);
    }
    #[Route('/{performanceId}', name: 'app_data_delete', methods: ['POST'])]
    public function delete(Request $request, Data $data, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$data->getPerformanceId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($data);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_data_index', [], Response::HTTP_SEE_OTHER);
    }
}
