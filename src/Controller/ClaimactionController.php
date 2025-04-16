<?php

namespace App\Controller;

use App\Entity\Claimaction;
use App\Entity\Claim;
use App\Form\ClaimactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/claimaction')]
final class ClaimactionController extends AbstractController
{
    #[Route(name: 'app_claimaction_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $claimactions = $entityManager
            ->getRepository(Claimaction::class)
            ->findAll();

        return $this->render('claimaction/index.html.twig', [
            'claimactions' => $claimactions,
        ]);
    }

    #[Route('/new', name: 'app_claimaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $claimaction = new Claimaction();

        // 👇 Get the claimId from the URL (if present)
        $claimIdFromUrl = $request->query->get('claimId');
        $claim = null;

        if ($claimIdFromUrl) {
            $claim = $entityManager->getRepository(Claim::class)->find($claimIdFromUrl);
            if ($claim) {
                $claimaction->setClaim($claim); // Set the Claim before binding the form
            }
        }

        $form = $this->createForm(ClaimactionType::class, $claimaction, [
            'claim_fixed' => (bool) $claimIdFromUrl,
        ]);

        $form->handleRequest($request);

        // Re-ensure claim is set even if form doesn't include the field
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$claimaction->getClaim() && $claim) {
                $claimaction->setClaim($claim);
            }

            $entityManager->persist($claimaction);
            $entityManager->flush();

            $this->addFlash('success', 'Claim action created successfully!');
            return $this->redirectToRoute('app_claim_index');
        }

        return $this->render('claimaction/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{claimActionId}', name: 'app_claimaction_show', methods: ['GET'])]
    public function show(Claimaction $claimaction): Response
    {
        return $this->render('claimaction/show.html.twig', [
            'claimaction' => $claimaction,
        ]);
    }

    #[Route('/{claimActionId}/edit', name: 'app_claimaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Claimaction $claimaction, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClaimactionType::class, $claimaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Claim action updated successfully!');
            return $this->redirectToRoute('app_claimaction_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('claimaction/edit.html.twig', [
            'claimaction' => $claimaction,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{claimActionId}', name: 'app_claimaction_delete', methods: ['POST'])]
    public function delete(Request $request, Claimaction $claimaction, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $claimaction->getClaimActionId(), $request->request->get('_token'))) {
            $entityManager->remove($claimaction);
            $entityManager->flush();

            $this->addFlash('success', 'Claim action deleted successfully!');
        }

        return $this->redirectToRoute('app_claimaction_index', [], Response::HTTP_SEE_OTHER);
    }
}
