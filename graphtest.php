<?php

require_once("GraphLib/Graph.php");
require_once("GraphLib/PlotLine.php");

$pos = -50;
$ydata = array();
for ($i = 0; $i <= 300; ++$i)
{
	array_push($ydata, max(-50, $pos));
	$pos += rand(-2,3);
}

$graph = new Graph(600, 300);

$lineGraph = new PlotLine($ydata);
$lineGraph->setLineColor('0.55@blue@0.15@green@white');
$lineGraph->setFillColor(array(80, 90, 170, 210));

$graph->addPlot($lineGraph);
$graph->stroke();

?>