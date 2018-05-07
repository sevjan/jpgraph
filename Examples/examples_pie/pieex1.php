<?php

/**
 * JPGraph v3.6.15
 */
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Some data
$data = [113, 5, 160, 3, 15, 10, 1];

// Create the Pie Graph.
$graph = new Graph\PieGraph(300, 200);
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set('Example 1 Pie plot');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 14);
$graph->title->SetColor('brown');

// Create pie plot
$p1 = new Plot\PiePlot($data);
//$p1->SetSliceColors(array("red","blue","yellow","green"));
$p1->SetTheme('earth');

$p1->value->SetFont(FF_ARIAL, FS_NORMAL, 10);
// Set how many pixels each slice should explode
$p1->Explode([0, 15, 15, 25, 15]);

$graph->Add($p1);
$graph->Stroke();
