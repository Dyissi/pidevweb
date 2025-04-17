<?php

namespace App\Controller;

use App\Entity\Injury;
use App\Form\InjuryFormType;
use App\Repository\InjuryRepository;
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

        // Ensure the injuryDate is set to today's date if not provided
        if ($injury->getInjuryDate() === null) {
            $injury->setInjuryDate(new \DateTime());
        }

        $form = $this->createForm(InjuryFormType::class, $injury);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Define the directory directly in the controller
                    $imageDirectory = 'D:\symfonyProjects\pidev\public\frontOffice\img';

                    // Move the file to the directory where images are stored
                    $imageFile->move(
                        $imageDirectory, // Use the directory we defined directly
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception if something goes wrong during file upload
                }

                // Set the imagePath property to the new filename
                $injury->setImagePath($newFilename);
            }

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

    
    #[Route('/{id}', name: 'app_injury_show', methods: ['GET'])]
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
public function edit(Request $request, int $id, InjuryRepository $injuryRepository, EntityManagerInterface $entityManager): Response
{
    // Fetch the Injury entity by ID
    $injury = $injuryRepository->find($id);

    // Check if the injury exists
    if (!$injury) {
        throw $this->createNotFoundException('The injury does not exist.');
    }

    // Create the form and handle the request
    $form = $this->createForm(InjuryFormType::class, $injury);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle file upload if an image is provided
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('imagePath')->getData();

        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();

            try {
                // Move the uploaded image to the public folder
                $imageDirectory = 'D:\symfonyProjects\pidev\public\frontOffice\img';
                $imageFile->move($imageDirectory, $newFilename);
                $injury->setImagePath($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'There was an issue uploading the image.');
                return $this->redirectToRoute('app_injury_edit', ['id' => $injury->getInjuryId()]);
            }
        }

        // Update the injury in the database
        $entityManager->flush();

        $this->addFlash('success', 'Injury updated successfully!');
        return $this->redirectToRoute('app_injury_index');
    }

    // Render the form view if the form is not submitted or is invalid
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


}
