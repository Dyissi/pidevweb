<?php

namespace App\Controller;

use App\Entity\Injury;
use App\Form\InjuryFormType;
use App\Repository\InjuryRepository;
use App\Repository\RecoveryplanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Nucleos\DompdfBundle\Factory\DompdfFactoryInterface;
use Psr\Log\LoggerInterface;

#[Route('/injury')]
final class InjuryController extends AbstractController
{
    #[Route('/', name: 'app_injury_index', methods: ['GET'])]
    public function index(Request $request, InjuryRepository $injuryRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'asc');

        $severityOrder = [
            'Mild' => 1,
            'Moderate' => 2,
            'Severe' => 3,
            'Critical' => 4,
        ];

        $queryBuilder = $injuryRepository->createQueryBuilder('i')
            ->leftJoin('i.user', 'u')
            ->addSelect('u');

        if ($search) {
            $queryBuilder->andWhere('i.injuryType LIKE :search 
                OR u.user_fname LIKE :search 
                OR u.user_lname LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $orderDirection = ($sort === 'desc') ? 'DESC' : 'ASC';
        $queryBuilder->orderBy(
            'CASE
                WHEN i.injury_severity = :mild THEN 1
                WHEN i.injury_severity = :moderate THEN 2
                WHEN i.injury_severity = :severe THEN 3
                WHEN i.injury_severity = :critical THEN 4
                ELSE 5
            END',
            $orderDirection
        )
        ->setParameter('mild', 'Mild')
        ->setParameter('moderate', 'Moderate')
        ->setParameter('severe', 'Severe')
        ->setParameter('critical', 'Critical');

        $injuries = $queryBuilder->getQuery()->getResult();

        return $this->render('injury/index.html.twig', [
            'injuries' => $injuries,
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_injury_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $injury = new Injury();

        if ($injury->getInjuryDate() === null) {
            $injury->setInjuryDate(new \DateTime());
        }

        $form = $this->createForm(InjuryFormType::class, $injury);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($injury);
            $entityManager->flush();

            $this->addFlash('success', 'Injury created successfully!');
            return $this->redirectToRoute('app_injury_index');
        }

        return $this->render('injury/new.html.twig', [
            'injury' => $injury,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_injury_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InjuryRepository $injuryRepository): Response
    {
        $injury = $injuryRepository->find($id);
    
        if (!$injury) {
            throw $this->createNotFoundException('Injury not found');
        }
    
        return $this->render('injury/show.html.twig', [
            'injury' => $injury,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_injury_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Injury $injury, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $form = $this->createForm(InjuryFormType::class, $injury);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image deletion
            if ($form->get('deleteImage')->getData() && $injury->getImage()) {
                try {
                    $filePath = $this->getParameter('kernel.project_dir') . '/public/frontOffice/img/' . $injury->getImage();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $injury->setImage(null);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to delete the image: ' . $e->getMessage());
                }
            }
    
            // Handle image upload
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile instanceof UploadedFile) {
                $tmpFilePath = $imageFile->getPathname();
                $logger->debug('Image file received', [
                    'filename' => $imageFile->getClientOriginalName(),
                    'tmp_name' => $tmpFilePath,
                    'is_valid' => $imageFile->isValid(),
                    'error' => $imageFile->getErrorMessage(),
                    'file_exists' => file_exists($tmpFilePath),
                ]);

                if ($imageFile->isValid() && file_exists($tmpFilePath)) {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/frontOffice/img/';
                    
                    // Ensure upload directory exists
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
    
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                    try {
                        copy($tmpFilePath, $uploadDir . $newFilename);
                        $injury->setImage($newFilename);
                        $logger->debug('Image copied successfully', ['new_filename' => $newFilename]);
                    } catch (\Exception $e) {
                        $logger->error('Failed to copy image', ['error' => $e->getMessage()]);
                        $this->addFlash('error', 'Failed to upload image: ' . $e->getMessage());
                    }
                } else {
                    $logger->warning('Invalid or non-existent uploaded file', [
                        'error' => $imageFile->getErrorMessage(),
                        'tmp_name' => $tmpFilePath,
                    ]);
                    $this->addFlash('error', 'Invalid image file uploaded.');
                }
            }
    
            $entityManager->flush();
    
            $this->addFlash('success', 'Injury updated successfully!');
            return $this->redirectToRoute('app_injury_index');
        }
    
        return $this->render('injury/edit.html.twig', [
            'injury' => $injury,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_injury_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, InjuryRepository $injuryRepository, EntityManagerInterface $entityManager): Response
    {
        $injury = $injuryRepository->find($id);

        if (!$injury) {
            throw $this->createNotFoundException('Injury not found');
        }

        if ($this->isCsrfTokenValid('delete' . $injury->getInjuryId(), $request->request->get('_token'))) {
            $entityManager->remove($injury);
            $entityManager->flush();

            $this->addFlash('success', 'Injury deleted successfully!');
        }

        return $this->redirectToRoute('app_injury_index');
    }

    #[Route('/Myhealth', name: 'app_injury_Myhealth')]
    public function myRecoveryPlans(
        RecoveryplanRepository $recoveryplanRepository,
        InjuryRepository $injuryRepository): Response {
        $user = $this->getUser();

        $injuries = $injuryRepository->findBy(['user' => $user]);
        $recoveryplans = $recoveryplanRepository->findBy(['user' => $user]);

        return $this->render('injury/Myhealth.html.twig', [
            'injuries' => $injuries,
            'recoveryplans' => $recoveryplans,
        ]);
    }

    #[Route('/export/pdf', name: 'app_injury_export_pdf', methods: ['GET'])]
    public function exportInjuriesPdf(
        InjuryRepository $injuryRepository,
        DompdfFactoryInterface $dompdfFactory
    ): Response {
        // Restrict to medical staff
        if (!$this->isGranted('ROLE_MED_STAFF')) {
            throw $this->createAccessDeniedException('Only medical staff can export injury data.');
        }

        // Fetch all injuries with their recovery plans
        $injuries = $injuryRepository->createQueryBuilder('i')
            ->leftJoin('i.recoveryplans', 'r')
            ->addSelect('r')
            ->leftJoin('i.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();

        // Render HTML using Twig template
        $html = $this->renderView('injury/export_pdf.html.twig', [
            'injuries' => $injuries,
        ]);

        // Create Dompdf instance
        $dompdf = $dompdfFactory->create();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Return PDF response
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment;filename="all_injuries_report.pdf"',
            ]
        );
    }
}