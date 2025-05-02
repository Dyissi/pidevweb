<?php

namespace App\Controller;

use App\Entity\Data;
use App\Form\DataType;
use App\Repository\DataRepository;
use App\Service\QuickChartService;
use App\Service\OpenMeteoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\CsvImportService;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

#[Route('/data')]

final class DataController extends AbstractController
{
    
    #[Route(name: 'app_data_index', methods: ['GET'])]
    public function index(Request $request, DataRepository $dataRepository, QuickChartService $quickChart, PaginatorInterface $paginator, OpenMeteoService $openMeteo): Response
    {
        $filter = $request->query->get('filter');

        if ($filter) {
            $query = $dataRepository->findFiltered($filter); // Use custom method in repo
        } else {
            $query = $dataRepository->createQueryBuilder('d');
        }

        $limit = $request->query->getInt('limit', 10);
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $limit
        );

        // Get weather data for Tunis (default location)
        $weather = $openMeteo->getWeatherData(36.819, 10.1658);

        // Prepare data for charts (use paginated data)
        $labels = [];
        $speedData = [];
        $agilityData = [];
        $goalsData = [];
        $foulsData = [];

        foreach ($pagination as $item) {
            $labels[] = $item->getPerformanceDateRecorded()->format('M d, Y');
            $speedData[] = $item->getPerformanceSpeed();
            $agilityData[] = $item->getPerformanceAgility();
            $goalsData[] = $item->getPerformanceNbrGoals();
            $foulsData[] = $item->getPerformanceNbrFouls();
        }

        // Generate chart URLs
        $speedChartUrl = $quickChart->generateLineChart(
            $labels,
            $speedData,
            'Speed (km/h)',
            ['width' => 800, 'height' => 400]
        );

        $agilityChartUrl = $quickChart->generateLineChart(
            $labels,
            $agilityData,
            'Agility Score',
            ['width' => 800, 'height' => 400]
        );

        $goalsChartUrl = $quickChart->generateBarChart(
            $labels,
            $goalsData,
            'Goals',
            ['width' => 800, 'height' => 400]
        );

        $foulsChartUrl = $quickChart->generateBarChart(
            $labels,
            $foulsData,
            'Fouls',
            ['width' => 800, 'height' => 400]
        );

        return $this->render('data/index.html.twig', [
            'data' => $pagination,
            'selectedFilter' => $filter,
            'speedChartUrl' => $speedChartUrl,
            'agilityChartUrl' => $agilityChartUrl,
            'goalsChartUrl' => $goalsChartUrl,
            'foulsChartUrl' => $foulsChartUrl,
            'weather' => $weather,
        ]);
    }
    #[Route('/meteo', name: 'app_data_meteo', methods: ['GET'])]
    public function meteo(Request $request, \App\Service\OpenMeteoService $openMeteo): JsonResponse
    {
        $lat = $request->query->get('lat', 36.819); 
        $lon = $request->query->get('lon', 10.1658);
        $weather = $openMeteo->getWeatherData((float)$lat, (float)$lon);
        if ($weather) {
            return new JsonResponse(['success' => true, 'weather' => $weather]);
        }
        return new JsonResponse(['success' => false, 'weather' => null], 400);
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
    
    #[Route('/import', name: 'app_data_import', methods: ['GET', 'POST'])]
    public function import(Request $request, CsvImportService $csvImportService): Response
    {
        $form = $this->createFormBuilder()
            ->add('csv_file', FileType::class, [
                'label' => 'CSV File',
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['text/csv', 'text/plain'],
                        'mimeTypesMessage' => 'Please upload a valid CSV file',
                    ])
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csv_file')->getData();
            
            try {
                $count = $csvImportService->import($file->getPathname(), $this->getUser());
                $this->addFlash('success', "Successfully imported $count records");
                return $this->redirectToRoute('app_data_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error importing CSV: '.$e->getMessage());
            }
        }

        return $this->render('data/import.html.twig', [
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
