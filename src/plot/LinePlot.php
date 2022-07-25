<?php

/**
 * JPGraph - Community Edition
 */

namespace Amenadiel\JpGraph\Plot;

use Amenadiel\JpGraph\Util;

/*
 * File:           JPGRAPH_LINE.PHP
 * // Description: Line plot extension for JpGraph
 * // Created:       2001-01-08
 * // Ver:           $Id: jpgraph_line.php 1921 2009-12-11 11:46:39Z ljp $
 * //
 * // Copyright (c) Asial Corporation. All rights reserved.
 */
// constants for the (filled) area
\define('LP_AREA_FILLED', true);
\define('LP_AREA_NOT_FILLED', false);
\define('LP_AREA_BORDER', false);
\define('LP_AREA_NO_BORDER', true);

/**
 * @class LinePlot
 * // Description:
 */
class LinePlot extends Plot
{
    public $mark;

    public $barcenter = false; // When we mix line and bar. Should we center the line in the bar.

    protected $filled = false;

    protected $fill_color = 'blue';

    protected $step_style = false;

    protected $center = false;

    protected $line_style = 1; // Default to solid

    protected $filledAreas = []; // array of arrays(with min,max,col,filled in them)

    protected $fillFromMin = false;

    protected $fillFromMax = false;

    protected $fillgrad = false;

    protected $fillgrad_fromcolor = 'navy';

    protected $fillgrad_tocolor = 'silver';

    protected $fillgrad_numcolors = 100;

    protected $iFastStroke = false;

    /**
     * @param mixed $datay
     * @param mixed $datax
     */
    public function __construct($datay, $datax = false)
    {
        parent::__construct($datay, $datax);
        $this->mark = new PlotMark();
        $this->color = Util\ColorFactory::getColor();
        $this->fill_color = $this->color;
    }

    /**
     * PUBLIC METHODS.
     *
     * @param mixed $aFlg
     */
    public function SetFilled($aFlg = true)
    {
        $this->filled = $aFlg;
    }

    public function SetBarCenter($aFlag = true)
    {
        $this->barcenter = $aFlag;
    }

    public function SetStyle($aStyle)
    {
        $this->line_style = $aStyle;
    }

    public function SetStepStyle($aFlag = true)
    {
        $this->step_style = $aFlag;
    }

    public function SetColor($aColor)
    {
        parent::SetColor($aColor);
    }

    public function SetFillFromYMin($f = true)
    {
        $this->fillFromMin = $f;
    }

    public function SetFillFromYMax($f = true)
    {
        $this->fillFromMax = $f;
    }

    public function SetFillColor($aColor, $aFilled = true)
    {
        //$this->color = $aColor;
        $this->fill_color = $aColor;
        $this->filled = $aFilled;
    }

    public function SetFillGradient($aFromColor, $aToColor, $aNumColors = 100, $aFilled = true)
    {
        $this->fillgrad_fromcolor = $aFromColor;
        $this->fillgrad_tocolor = $aToColor;
        $this->fillgrad_numcolors = $aNumColors;
        $this->filled = $aFilled;
        $this->fillgrad = true;
    }

    public function Legend($aGraph)
    {
        if ('' === $this->legend) {
            return;
        }

        if ($this->filled && !$this->fillgrad) {
            $aGraph->legend->Add(
                $this->legend,
                $this->fill_color,
                $this->mark,
                0,
                $this->legendcsimtarget,
                $this->legendcsimalt,
                $this->legendcsimwintarget
            );
        } elseif ($this->fillgrad) {
            $color = [$this->fillgrad_fromcolor, $this->fillgrad_tocolor];
            // In order to differentiate between gradients and cooors specified as an Image\RGB triple
            $aGraph->legend->Add(
                $this->legend,
                $color,
                '',
                -2/* -GRAD_HOR */,
                $this->legendcsimtarget,
                $this->legendcsimalt,
                $this->legendcsimwintarget
            );
        } else {
            $aGraph->legend->Add(
                $this->legend,
                $this->color,
                $this->mark,
                $this->line_style,
                $this->legendcsimtarget,
                $this->legendcsimalt,
                $this->legendcsimwintarget
            );
        }
    }

    public function AddArea($aMin = 0, $aMax = 0, $aFilled = LP_AREA_NOT_FILLED, $aColor = 'gray9', $aBorder = LP_AREA_BORDER)
    {
        if ($aMin > $aMax) {
            // swap
            $tmp = $aMin;
            $aMin = $aMax;
            $aMax = $tmp;
        }
        $this->filledAreas[] = [$aMin, $aMax, $aColor, $aFilled, $aBorder];
    }

    // Gets called before any axis are stroked
    public function PreStrokeAdjust($aGraph)
    {
        // If another plot type have already adjusted the
        // offset we don't touch it.
        // (We check for empty in case the scale is  a log scale
        // and hence doesn't contain any xlabel_offset)
        if (!empty($aGraph->xaxis->scale->ticks->xlabel_offset) && 0 !== $aGraph->xaxis->scale->ticks->xlabel_offset) {
            // If another plot type have already adjusted the
            // offset we don't touch it.
            // (We check for empty in case the scale is  a log scale
            // and hence doesn't contain any xlabel_offset)
            return;
            // If another plot type have already adjusted the
            // offset we don't touch it.
            // (We check for empty in case the scale is  a log scale
            // and hence doesn't contain any xlabel_offset)
        }

        if ($this->center) {
            ++$this->numpoints;
            $a = 0.5;
            $b = 0.5;
        } else {
            $a = 0;
            $b = 0;
        }
        $aGraph->xaxis->scale->ticks->SetXLabelOffset($a);
        $aGraph->SetTextScaleOff($b);
        //$graph->xaxis->scale->ticks->SupressMinorTickMarks();
    }

    public function SetFastStroke($aFlg = true)
    {
        $this->iFastStroke = $aFlg;
    }

    public function FastStroke($img, $xscale, $yscale, $aStartPoint = 0, $exist_x = true)
    {
        // An optimized stroke for many data points with no extra
        // features but 60% faster. You can't have values or line styles, or null
        // values in plots.
        $numpoints = Configs::safe_count($this->coords[0]);

        if ($this->barcenter) {
            $textadj = 0.5 - $xscale->text_scale_off;
        } else {
            $textadj = 0;
        }

        $img->SetColor($this->color);
        $img->SetLineWeight($this->weight);
        $pnts = $aStartPoint;

        while ($pnts < $numpoints) {
            if ($exist_x) {
                $x = $this->coords[1][$pnts];
            } else {
                $x = $pnts + $textadj;
            }
            $xt = $xscale->Translate($x);
            $y = $this->coords[0][$pnts];
            $yt = $yscale->Translate($y);

            if (\is_numeric($y)) {
                $cord[] = $xt;
                $cord[] = $yt;
            } elseif ('-' === $y && 0 < $pnts) {
                // Just ignore
            } else {
                throw Util\JpGraphError::make(10002); //('Plot too complicated for fast line Stroke. Use standard Stroke()');
            }
            ++$pnts;
        } // WHILE

        $img->Polygon($cord, false, true);
    }

    public function Stroke($aImg, $aXScale, $aYScale)
    {
        $idx = 0;
        $numpoints = Configs::safe_count($this->coords[0]);

        if (isset($this->coords[1])) {
            if (Configs::safe_count($this->coords[1]) !== $numpoints
            ) {
                throw Util\JpGraphError::make(2003, Configs::safe_count($this->coords[1]), $numpoints);
                //("Number of X and Y points are not equal. Number of X-points:". Configs::safe_count($this->coords[1])." Number of Y-points:$numpoints");
            }
            $exist_x = true;
        } else {
            $exist_x = false;
        }

        if ($this->barcenter) {
            $textadj = 0.5 - $aXScale->text_scale_off;
        } else {
            $textadj = 0;
        }

        // Find the first numeric data point
        $startpoint = 0;

        while ($startpoint < $numpoints && !\is_numeric($this->coords[0][$startpoint])) {
            ++$startpoint;
        }

        // Bail out if no data points
        if ($startpoint === $numpoints) {
            return;
        }

        if ($this->iFastStroke) {
            $this->FastStroke($aImg, $aXScale, $aYScale, $startpoint, $exist_x);

            return;
        }

        if ($exist_x) {
            $xs = $this->coords[1][$startpoint];
        } else {
            $xs = $textadj + $startpoint;
        }

        $aImg->SetStartPoint(
            $aXScale->Translate($xs),
            $aYScale->Translate($this->coords[0][$startpoint])
        );

        if ($this->filled) {
            if ($this->fillFromMax) {
                //$max = $yscale->GetMaxVal();
                $cord[$idx++] = $aXScale->Translate($xs);
                $cord[$idx++] = $aYScale->scale_abs[1];
            } else {
                $min = $aYScale->GetMinVal();

                if (0 < $min || $this->fillFromMin) {
                    $fillmin = $aYScale->scale_abs[0]; //Translate($min);
                } else {
                    $fillmin = $aYScale->Translate(0);
                }

                $cord[$idx++] = $aXScale->Translate($xs);
                $cord[$idx++] = $fillmin;
            }
        }
        $xt = $aXScale->Translate($xs);
        $yt = $aYScale->Translate($this->coords[0][$startpoint]);
        $cord[$idx++] = $xt;
        $cord[$idx++] = $yt;
        $yt_old = $yt;
        $xt_old = $xt;
        $y_old = $this->coords[0][$startpoint];

        $this->value->Stroke($aImg, $this->coords[0][$startpoint], $xt, $yt);

        $aImg->SetColor($this->color);
        $aImg->SetLineWeight($this->weight);
        $aImg->SetLineStyle($this->line_style);
        $pnts = $startpoint + 1;
        $firstnonumeric = false;

        while ($pnts < $numpoints) {
            if ($exist_x) {
                $x = $this->coords[1][$pnts];
            } else {
                $x = $pnts + $textadj;
            }
            $xt = $aXScale->Translate($x);
            $yt = $aYScale->Translate($this->coords[0][$pnts]);

            $y = $this->coords[0][$pnts];

            if ($this->step_style) {
                // To handle null values within step style we need to record the
                // first non numeric value so we know from where to start if the
                // non value is '-'.
                if (\is_numeric($y)) {
                    $firstnonumeric = false;

                    if (\is_numeric($y_old)) {
                        $aImg->StyleLine($xt_old, $yt_old, $xt, $yt_old);
                        $aImg->StyleLine($xt, $yt_old, $xt, $yt);
                    } elseif ('-' === $y_old) {
                        $aImg->StyleLine($xt_first, $yt_first, $xt, $yt_first);
                        $aImg->StyleLine($xt, $yt_first, $xt, $yt);
                    } else {
                        $yt_old = $yt;
                        $xt_old = $xt;
                    }
                    $cord[$idx++] = $xt;
                    $cord[$idx++] = $yt_old;
                    $cord[$idx++] = $xt;
                    $cord[$idx++] = $yt;
                } elseif (false === $firstnonumeric) {
                    $firstnonumeric = true;
                    $yt_first = $yt_old;
                    $xt_first = $xt_old;
                }
            } else {
                $tmp1 = $y;
                $prev = $this->coords[0][$pnts - 1];

                if ('' === $tmp1 || null === $tmp1 || 'X' === $tmp1) {
                    $tmp1 = 'x';
                }

                if ('' === $prev || null === $prev || 'X' === $prev) {
                    $prev = 'x';
                }

                if (\is_numeric($y) || (\is_string($y) && '-' !== $y)) {
                    if (\is_numeric($y) && (\is_numeric($prev) || '-' === $prev)) {
                        $aImg->StyleLineTo($xt, $yt);
                    } else {
                        $aImg->SetStartPoint($xt, $yt);
                    }
                }

                if ($this->filled && '-' !== $tmp1) {
                    if ('x' === $tmp1) {
                        $cord[$idx++] = $cord[$idx - 3];
                        $cord[$idx++] = $fillmin;
                    } elseif ('x' === $prev) {
                        $cord[$idx++] = $xt;
                        $cord[$idx++] = $fillmin;
                        $cord[$idx++] = $xt;
                        $cord[$idx++] = $yt;
                    } else {
                        $cord[$idx++] = $xt;
                        $cord[$idx++] = $yt;
                    }
                } else {
                    if (\is_numeric($tmp1) && (\is_numeric($prev) || '-' === $prev)) {
                        $cord[$idx++] = $xt;
                        $cord[$idx++] = $yt;
                    }
                }
            }
            $yt_old = $yt;
            $xt_old = $xt;
            $y_old = $y;

            $this->StrokeDataValue($aImg, $this->coords[0][$pnts], $xt, $yt);

            ++$pnts;
        }

        if ($this->filled) {
            $cord[$idx++] = $xt;

            if ($this->fillFromMax) {
                $cord[$idx++] = $aYScale->scale_abs[1];
            } else {
                if (0 < $min || $this->fillFromMin) {
                    $cord[$idx++] = $aYScale->Translate($min);
                } else {
                    $cord[$idx++] = $aYScale->Translate(0);
                }
            }

            if ($this->fillgrad) {
                $aImg->SetLineWeight(1);
                $grad = new Gradient($aImg);
                $grad->SetNumColors($this->fillgrad_numcolors);
                $grad->FilledFlatPolygon($cord, $this->fillgrad_fromcolor, $this->fillgrad_tocolor);
                $aImg->SetLineWeight($this->weight);
            } else {
                $aImg->SetColor($this->fill_color);
                $aImg->FilledPolygon($cord);
            }

            if (0 < $this->weight) {
                $aImg->SetLineWeight($this->weight);
                $aImg->SetColor($this->color);
                // Remove first and last coordinate before drawing the line
                // sine we otherwise get the vertical start and end lines which
                // doesn't look appropriate
                $aImg->Polygon(\array_slice($cord, 2, Configs::safe_count($cord) - 4));
            }
        }

        if (!empty($this->filledAreas)) {
            $minY = $aYScale->Translate($aYScale->GetMinVal());
            $factor = ($this->step_style ? 4 : 2);

            for ($i = 0; Configs::safe_count($this->filledAreas) > $i; ++$i) {
                // go through all filled area elements ordered by insertion
                // fill polygon array
                $areaCoords[] = $cord[$this->filledAreas[$i][0] * $factor];
                $areaCoords[] = $minY;

                $areaCoords =
                    \array_merge(
                        $areaCoords,
                        \array_slice(
                            $cord,
                            $this->filledAreas[$i][0] * $factor,
                            ($this->filledAreas[$i][1] - $this->filledAreas[$i][0] + ($this->step_style ? 0 : 1)) * $factor
                        )
                    );
                $areaCoords[] = $areaCoords[Configs::safe_count($areaCoords) - 2]; // last x
                $areaCoords[] = $minY; // last y

                if ($this->filledAreas[$i][3]) {
                    $aImg->SetColor($this->filledAreas[$i][2]);
                    $aImg->FilledPolygon($areaCoords);
                    $aImg->SetColor($this->color);
                }
                // Check if we should draw the frame.
                // If not we still re-draw the line since it might have been
                // partially overwritten by the filled area and it doesn't look
                // very good.
                if ($this->filledAreas[$i][4]) {
                    $aImg->Polygon($areaCoords);
                } else {
                    $aImg->Polygon($cord);
                }

                $areaCoords = [];
            }
        }

        if (!\is_object($this->mark) || -1 === $this->mark->type || false === $this->mark->show) {
            return;
        }

        for ($pnts = 0; $pnts < $numpoints; ++$pnts) {
            if ($exist_x) {
                $x = $this->coords[1][$pnts];
            } else {
                $x = $pnts + $textadj;
            }
            $xt = $aXScale->Translate($x);
            $yt = $aYScale->Translate($this->coords[0][$pnts]);

            if (!\is_numeric($this->coords[0][$pnts])) {
                continue;
            }

            if (!empty($this->csimtargets[$pnts])) {
                if (!empty($this->csimwintargets[$pnts])) {
                    $this->mark->SetCSIMTarget($this->csimtargets[$pnts], $this->csimwintargets[$pnts]);
                } else {
                    $this->mark->SetCSIMTarget($this->csimtargets[$pnts]);
                }
                $this->mark->SetCSIMAlt($this->csimalts[$pnts]);
            }

            if ($exist_x) {
                $x = $this->coords[1][$pnts];
            } else {
                $x = $pnts;
            }
            $this->mark->SetCSIMAltVal($this->coords[0][$pnts], $x);
            $this->mark->Stroke($aImg, $xt, $yt);
            $this->csimareas .= $this->mark->GetCSIMAreas();
        }
    }
}

// @class
