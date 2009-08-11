<?php

require_once("GraphLib/Graph.php");
require_once("GraphLib/PlotLine.php");

$pos = -50;
$ydata = array();
for ($i = 0; $i < 290; ++$i)
{
	array_push($ydata, max(-50, $pos));
	$pos += rand(-2,3);
}
$ydata2 = array();
for ($i = 0; $i < 290; ++$i)
{
	array_push($ydata2, max(-50, $pos));
	$pos += rand(-3,2);
}
$xdata = array();
for ($i = 0; $i < 290; ++$i)
{
	array_push($xdata, $i + 20);
}

$graph = new Graph(600, 300);
$graph->setPlotFrameColor('gray@black');
$graph->xGrid->setFillColor(array(array(240, 240, 240), array(220, 220, 220)));
$graph->xGrid->setLineColor('gray');
$graph->yGrid->setLineColor('silver@gray');

$lg = new PlotLine($ydata2, $xdata);
$lg->setLineColor('0.55@red@0.15@green@black');
$lg->setFillColor(array(150, 20, 0, 190));
$graph->addPlot($lg);

$lineGraph = new PlotLine($ydata);
$lineGraph->setLineColor('0.55@blue@0.15@green@white');
$lineGraph->setFillColor(array(150, 170, 220, 100));
$graph->addPlot($lineGraph);

$graph->stroke();

?>