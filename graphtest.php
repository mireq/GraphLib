<?php

require_once("GraphLib/Graph.php");
require_once("GraphLib/PlotLine.php");

$pos = 0;
$ydata = array();
for ($i = 0; $i < 1000; ++$i)
{
	$pos += rand(-2,3);
	array_push($ydata, $pos);
}

$graph = new Graph(600, 300);

$lineGraph = new PlotLine($ydata);
$lineGraph->setLineColor('0.55@blue@0.15@green@white');
$lineGraph->setFillColor(array(80, 90, 170, 210));

$graph->addPlot($lineGraph);
$graph->stroke();

?>