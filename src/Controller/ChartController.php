<?php

namespace App\Controller;

use App\Service\QuickChartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChartController extends AbstractController
{
    #[Route('/charts', name: 'app_charts')]
    public function index(QuickChartService $quickChart): Response
    {
        // Example data
        $labels = ['January', 'February', 'March', 'April', 'May', 'June'];
        $data = [65, 59, 80, 81, 56, 55];

        // Generate different types of charts
        $barChartUrl = $quickChart->generateBarChart(
            $labels,
            $data,
            'Monthly Performance',
            ['width' => 600, 'height' => 400]
        );

        $lineChartUrl = $quickChart->generateLineChart(
            $labels,
            $data,
            'Trend Analysis',
            ['width' => 600, 'height' => 400]
        );

        $pieChartUrl = $quickChart->generatePieChart(
            $labels,
            $data,
            ['width' => 400, 'height' => 400]
        );

        return $this->render('chart/index.html.twig', [
            'barChartUrl' => $barChartUrl,
            'lineChartUrl' => $lineChartUrl,
            'pieChartUrl' => $pieChartUrl,
        ]);
    }
} 