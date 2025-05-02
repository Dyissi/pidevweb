<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuickChartService
{
    private HttpClientInterface $httpClient;
    private string $baseUrl;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = 'https://quickchart.io/chart';
    }

    /**
     * Generate a chart using QuickChart.io API
     *
     * @param array $chartConfig Chart.js configuration array
     * @param array $options Additional options (width, height, etc.)
     * @return string The URL of the generated chart
     */
    public function generateChart(array $chartConfig, array $options = []): string
    {
        $defaultOptions = [
            'width' => 500,
            'height' => 300,
            'devicePixelRatio' => 1.0,
            'format' => 'png',
            'backgroundColor' => 'transparent'
        ];

        $options = array_merge($defaultOptions, $options);

        $queryParams = [
            'c' => json_encode($chartConfig),
            'w' => $options['width'],
            'h' => $options['height'],
            'devicePixelRatio' => $options['devicePixelRatio'],
            'f' => $options['format'],
            'bkg' => $options['backgroundColor']
        ];

        return $this->baseUrl . '?' . http_build_query($queryParams);
    }

    /**
     * Generate a bar chart
     *
     * @param array $labels X-axis labels
     * @param array $data Data points
     * @param string $label Dataset label
     * @param array $options Additional chart options
     * @return string The URL of the generated chart
     */
    public function generateBarChart(array $labels, array $data, string $label = 'Data', array $options = []): string
    {
        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 1
                ]]
            ],
            'options' => [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];

        return $this->generateChart($chartConfig, $options);
    }

    /**
     * Generate a line chart
     *
     * @param array $labels X-axis labels
     * @param array $data Data points
     * @param string $label Dataset label
     * @param array $options Additional chart options
     * @return string The URL of the generated chart
     */
    public function generateLineChart(array $labels, array $data, string $label = 'Data', array $options = []): string
    {
        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => $data,
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1
                ]]
            ]
        ];

        return $this->generateChart($chartConfig, $options);
    }

    /**
     * Generate a pie chart
     *
     * @param array $labels Labels for each segment
     * @param array $data Data points
     * @param array $options Additional chart options
     * @return string The URL of the generated chart
     */
    public function generatePieChart(array $labels, array $data, array $options = []): string
    {
        $chartConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)'
                    ]
                ]]
            ]
        ];

        return $this->generateChart($chartConfig, $options);
    }
} 