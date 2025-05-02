<?php

namespace App\Controller;

use App\Entity\TrainingSession;
use App\Repository\TrainingSessionRepository;
use App\Service\GeminiService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ColumnChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class TrainingSessionAnalysisController extends AbstractController
{
    #[Route('/training/analysis', name: 'app_training_analysis')]
    public function index(TrainingSessionRepository $trainingSessionRepository, GeminiService $geminiService, PaginatorInterface $paginator, Request $request): Response
    {
        // Get all training sessions with pagination
        $query = $trainingSessionRepository->createQueryBuilder('s')
            ->orderBy('s.sessionStartTime', 'DESC');
            
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        // Get all training sessions for charts
        $sessions = $trainingSessionRepository->findAll();
        
        // Prepare data for charts
        $focusData = [];
        $focusCounts = [];
        $durationSums = [];
        $durationCounts = [];
        $sessionsByDate = [];

        foreach ($sessions as $session) {
            $focus = $session->getSessionFocus();
            $duration = $session->getSessionDuration();
            $date = $session->getSessionStartTime()->format('Y-m-d');

            // Focus distribution
            if (!isset($focusCounts[$focus])) {
                $focusCounts[$focus] = 0;
            }
            $focusCounts[$focus]++;

            // Duration analysis
            if (!isset($durationSums[$focus])) {
                $durationSums[$focus] = 0;
                $durationCounts[$focus] = 0;
            }
            $durationSums[$focus] += $duration;
            $durationCounts[$focus]++;

            // Time analysis
            if (!isset($sessionsByDate[$date])) {
                $sessionsByDate[$date] = 0;
            }
            $sessionsByDate[$date]++;
        }

        // Focus Distribution Chart
        $focusDistribution = new PieChart();
        $focusData[] = ['Focus Type', 'Number of Sessions'];
        foreach ($focusCounts as $focus => $count) {
            $focusData[] = [$focus, $count];
        }
        $focusDistribution->getData()->setArrayToDataTable($focusData);
        $focusDistribution->getOptions()
            ->setTitle('Training Session Focus Distribution')
            ->setHeight(500)
            ->setWidth(900)
            ->setPieSliceText('label')
            ->getTitleTextStyle()
                ->setBold(true)
                ->setColor('#009900')
                ->setFontSize(20);

        // Duration Chart
        $durationChart = new ColumnChart();
        $durationData[] = ['Focus Type', 'Average Duration (minutes)'];
        foreach ($durationSums as $focus => $sum) {
            $average = $sum / $durationCounts[$focus];
            $durationData[] = [$focus, $average];
        }
        $durationChart->getData()->setArrayToDataTable($durationData);
        $durationChart->getOptions()
            ->setTitle('Average Duration by Focus Type')
            ->setHeight(500)
            ->setWidth(900)
            ->getTitleTextStyle()
                ->setBold(true)
                ->setColor('#009900')
                ->setFontSize(20);

        // Time Chart
        $timeChart = new LineChart();
        ksort($sessionsByDate);
        $timeData[] = ['Date', 'Number of Sessions'];
        foreach ($sessionsByDate as $date => $count) {
            $timeData[] = [$date, $count];
        }
        $timeChart->getData()->setArrayToDataTable($timeData);
        $timeChart->getOptions()
            ->setTitle('Training Sessions Over Time')
            ->setHeight(500)
            ->setWidth(900)
            ->getTitleTextStyle()
                ->setBold(true)
                ->setColor('#009900')
                ->setFontSize(20);

        return $this->render('training_session/analysis.html.twig', [
            'training_sessions' => $pagination,
            'focusDistribution' => $focusDistribution,
            'durationChart' => $durationChart,
            'timeChart' => $timeChart,
        ]);
    }

    #[Route('/training/analysis/ask', name: 'app_training_analysis_ask', methods: ['POST'])]
    public function askAnalysis(Request $request, GeminiService $geminiService): JsonResponse
    {
        $question = $request->request->get('question');
        if (!$question) {
            return new JsonResponse(['error' => 'No question provided'], 400);
        }
        $response = $geminiService->makeApiCall($question);
        return new JsonResponse($response);
    }
} 