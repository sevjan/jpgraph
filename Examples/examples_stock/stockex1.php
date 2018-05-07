<?php

/**
 * JPGraph v3.6.15
 */

// Example of a stock chart
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_stock.php';

// Data must be in the format : open,close,min,max
$datay = [
    34, 42, 27, 45,
    55, 25, 14, 59,
    15, 40, 12, 47,
    62, 38, 25, 65,
    38, 49, 32, 64, ];

// Setup a simple graph
$graph = new Graph\Graph(300, 200);
$graph->SetScale('textlin');
$graph->SetMarginColor('lightblue');
$graph->title->Set('Stockchart example');

// Create a new stock plot
$p1 = new StockPlot($datay);

// Width of the bars (in pixels)
$p1->SetWidth(9);

// Uncomment the following line to hide the horizontal end lines
//$p1->HideEndLines();

// Add the plot to the graph and send it back to the browser
$graph->Add($p1);
$graph->Stroke();
