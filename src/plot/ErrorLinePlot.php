<?php

/**
 * JPGraph - Community Edition
 */

namespace Amenadiel\JpGraph\Plot;

/**
 * @class ErrorLinePlot
 * // Description: Combine a line and error plot
 * // THIS IS A DEPRECATED PLOT TYPE JUST KEPT FOR
 * // BACKWARD COMPATIBILITY
 */
class ErrorLinePlot extends ErrorPlot
{
    public $line;

    /**
     * @param mixed $datay
     * @param mixed $datax
     */
    public function __construct($datay, $datax = false)
    {
        parent::__construct($datay, $datax);
        // Calculate line coordinates as the average of the error limits
        $n = Configs::safe_count($datay);

        for ($i = 0; $i < $n; $i += 2) {
            $ly[] = ($datay[$i] + $datay[$i + 1]) / 2;
        }
        $this->line = new LinePlot($ly, $datax);
    }

    /**
     * PUBLIC METHODS.
     *
     * @param mixed $aGraph
     */
    public function Legend($aGraph)
    {
        if ('' !== $this->legend) {
            $aGraph->legend->Add($this->legend, $this->color);
        }

        $this->line->Legend($aGraph);
    }

    public function Stroke($img, $xscale, $yscale)
    {
        parent::Stroke($img, $xscale, $yscale);
        $this->line->Stroke($img, $xscale, $yscale);
    }
} // @class
