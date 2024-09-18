<?php

namespace Framework\Controllers\Forms\Charts;

use Framework\Controllers\Forms\AbstractFormElement;

class BasicChart extends AbstractFormElement
{
    const Type = 'chart';

    private array $data = [];

    private string $chartType = 'line';

    private array $xAxisLabels = [];

    private string $datasetLabel = '';

    private string $color = '';

    private string $backgroundColor = '';

    private int $borderWidth = 1;

    private false|string $fill = false;

    public function __construct(string $id) {
        parent::__construct($id, '', '', 'chart');

        $this->notDBField();

        $this->init();
    }

    public function getType(): string
    {
        return $this::Type;
    }

    protected function init(): void
    {
        $this->addJs('chartjs/dist/chart.js');

        $this->addJs('charts.min.js');
    }

    public function setData(array $data): BasicChart
    {
        $this->data = [];

        foreach($data as $key => $value) {
            $this->data[] = [
                'x' => $key,
                'y' => $value,
            ];
        }

        return $this;
    }

    public function getChartType(): string
    {
        return $this->chartType;
    }

    public function setChartType(string $chartType): BasicChart
    {
        $this->chartType = $chartType;

        return $this;
    }

    public function setXAxisLabels(array $xAxisLabels): BasicChart
    {
        $this->xAxisLabels = $xAxisLabels;

        return $this;
    }

    public function setDatasetLabel(string $datasetLabel): BasicChart
    {
        $this->datasetLabel = $datasetLabel;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): BasicChart
    {
        $this->color = $color;

        return $this;
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): BasicChart
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function setBorderWidth(int $borderWidth): BasicChart
    {
        $this->borderWidth = $borderWidth;

        return $this;
    }

    public function setFill(false|string $fill): BasicChart
    {
        $this->fill = $fill;

        return $this;
    }

    public function generateData():string
    {
        $data = [
            'labels' => $this->xAxisLabels,
            'datasets' => [
                0 => [
                    'label' => $this->datasetLabel,
                    'fill' => $this->fill,
                    'borderWidth' => $this->borderWidth,
                    'borderColor' => $this->color,
                    'backgroundColor' => $this->backgroundColor,
                    'data' => $this->data
                ]
            ],
        ];

        return json_encode($data);
    }

    public function generateOptions():string
    {
        $options = [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ]
            ],
            'maintainAspectRatio' => false,
            'responsive' => true,
        ];

        return json_encode($options);
    }
}