<?php

namespace App\Controller;

use App\Entity\Recoveryplan;
use App\Form\RecoveryplanFormType;
use App\Repository\RecoveryplanRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TwilioService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recoveryplan')]
class RecoveryplanController extends AbstractController
{
    public function __construct(private TwilioService $twilio) {}

    #[Route('/', name: 'app_recoveryplan_index', methods: ['GET'])]
    public function index(Request $request, RecoveryplanRepository $recoveryplanRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'asc');

        $queryBuilder = $recoveryplanRepository->createQueryBuilder('r')
            ->leftJoin('r.injury', 'i')
            ->leftJoin('r.user', 'u')
            ->addSelect('i', 'u');

        if ($search) {
            $queryBuilder->andWhere('r.recoveryGoal LIKE :search 
                OR u.user_fname LIKE :search 
                OR u.user_lname LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Sorting by recovery status
        $orderDirection = ($sort === 'desc') ? 'DESC' : 'ASC';
        if ($sort === 'status_asc') {
            $queryBuilder->orderBy('r.recoveryStatus', 'ASC');
        } elseif ($sort === 'status_desc') {
            $queryBuilder->orderBy('r.recoveryStatus', 'DESC');
        } else {
            $queryBuilder->orderBy('r.recoveryId', $orderDirection);
        }

        $recoveryplans = $queryBuilder->getQuery()->getResult();

        return $this->render('recoveryplan/index.html.twig', [
            'recoveryplans' => $recoveryplans,
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_recoveryplan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $recoveryplan = new Recoveryplan();
        $form = $this->createForm(RecoveryplanFormType::class, $recoveryplan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recoveryplan);
            $em->flush();

            // Send SMS after successful creation
            $this->twilio->sendSms(
                $recoveryplan->getUser()->getUserNbr(), // Assuming phone is stored in user_nbr
                "New recovery plan created: {$recoveryplan->getRecoveryGoal()}\n" .
                "Start Date: {$recoveryplan->getRecoveryStartDate()->format('Y-m-d')}\n" .
                "End Date: {$recoveryplan->getRecoveryEndDate()->format('Y-m-d')}"
            );

            $this->addFlash('success', 'Recovery Plan created successfully!');
            return $this->redirectToRoute('app_recoveryplan_index');
        }

        return $this->render('recoveryplan/new.html.twig', [
            'recoveryplan' => $recoveryplan,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_recoveryplan_show', methods: ['GET'])]
    public function show(int $id, RecoveryplanRepository $recoveryplanRepository): Response
    {
        $recoveryplan = $recoveryplanRepository->find($id);

        if (!$recoveryplan) {
            throw $this->createNotFoundException('Recovery Plan not found');
        }

        return $this->render('recoveryplan/show.html.twig', [
            'recoveryplan' => $recoveryplan,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recoveryplan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, RecoveryplanRepository $recoveryplanRepository, EntityManagerInterface $em): Response
    {
        $recoveryplan = $recoveryplanRepository->find($id);

        if (!$recoveryplan) {
            throw $this->createNotFoundException('The recovery plan does not exist.');
        }

        $form = $this->createForm(RecoveryplanFormType::class, $recoveryplan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Send SMS after successful update
            $this->twilio->sendSms(
                $recoveryplan->getUser()->getUserNbr(),
                "Your recovery plan has been updated:\n" .
                "New goal: {$recoveryplan->getRecoveryGoal()}\n" .
                "Status: {$recoveryplan->getRecoveryStatus()}"
            );

            $this->addFlash('success', 'Recovery Plan updated successfully!');
            return $this->redirectToRoute('app_recoveryplan_index');
        }

        return $this->render('recoveryplan/edit.html.twig', [
            'recoveryplan' => $recoveryplan,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_recoveryplan_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, RecoveryplanRepository $recoveryplanRepository, EntityManagerInterface $em): Response
    {
        $recoveryplan = $recoveryplanRepository->find($id);

        if (!$recoveryplan) {
            throw $this->createNotFoundException('The recovery plan does not exist.');
        }

        if ($this->isCsrfTokenValid('delete' . $recoveryplan->getRecoveryId(), $request->request->get('_token'))) {
            $em->remove($recoveryplan);
            $em->flush();

            $this->addFlash('success', 'Recovery Plan deleted successfully!');
        }

        return $this->redirectToRoute('app_recoveryplan_index');
    }
}
