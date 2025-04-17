<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Form\ClaimType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/claim')]
final class ClaimController extends AbstractController
{
    #[Route(name: 'app_claim_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $claims = $entityManager->getRepository(Claim::class)->findAll();

        return $this->render('claim/index.html.twig', [
            'claims' => $claims,
        ]);
    }

    #[Route('/new', name: 'app_claim_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        $claim = new Claim();
        $claim->setClaimDate(new \DateTime());
        $claim->setClaimStatus('In Review'); // Set default status
        $claim->setIdUser($user); // Set current user as submitter

        $form = $this->createForm(ClaimType::class, $claim, [
            'is_edit' => false, // 👈 indicates create mode
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($claim);
            $entityManager->flush();

            $this->addFlash('success', 'Claim created successfully!');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('claim/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{claimId}', name: 'app_claim_show', methods: ['GET'])]
    public function show(Claim $claim): Response
    {
        return $this->render('claim/show.html.twig', [
            'claim' => $claim,
        ]);
    }

    #[Route('/{claimId}/edit', name: 'app_claim_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Claim $claim, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClaimType::class, $claim, [
            'is_edit' => true, // 👈 indicates edit mode
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Claim updated successfully!');
            return $this->redirectToRoute('app_claim_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('claim/edit.html.twig', [
            'claim' => $claim,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{claimId}', name: 'app_claim_delete', methods: ['POST'])]
    public function delete(Request $request, Claim $claim, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $claim->getClaimId(), $request->request->get('_token'))) {
            $entityManager->remove($claim);
            $entityManager->flush();
            $this->addFlash('success', 'Claim deleted successfully!');
        }

        return $this->redirectToRoute('app_claim_index', [], Response::HTTP_SEE_OTHER);
    }
}