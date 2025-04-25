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

        // Create the query builder with proper join
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
        // VichUploader automatically handles file uploads
        // No need for manual file movement
        
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
    public function edit(Request $request, Injury $injury, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InjuryFormType::class, $injury);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image deletion
            if ($form->get('deleteImage')->getData() && $injury->getImage()) {
                try {
                    // Remove the file from filesystem
                    $filePath = $this->getParameter('kernel.project_dir').'/public/frontOffice/img/'.$injury->getImage();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                    // Clear the image fields
                    $injury->setImage(null);
                  
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to delete the image: '.$e->getMessage());
                }
            }
    
            // Handle file upload
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    // Move the file to the upload directory
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/frontOffice/img/',
                        $newFilename
                    );
    
                    // Update the injury entity
                    $injury->setImage($newFilename);
                   
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading your image');
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
    // Fetch the Injury entity by ID manually
    $injury = $injuryRepository->find($id);

    if (!$injury) {
        throw $this->createNotFoundException('Injury not found');
    }

    // Validate CSRF token
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

    // Fetch injuries and recovery plans for this user
    $injuries = $injuryRepository->findBy(['user' => $user]);
    $recoveryplans = $recoveryplanRepository->findBy(['user' => $user]);

    return $this->render('injury/Myhealth.html.twig', [
        'injuries' => $injuries,
        'recoveryplans' => $recoveryplans,
    ]);
}
}
