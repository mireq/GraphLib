<?php

require_once("GraphLib.php");
require_once("GraphAxis.php");

/**
 * Hlavná trieda na tvorbu grafov.
 */
class Graph
{


/// Obrázok, na ktorý sa vykresľuje graf.
public $img = null;
/// X - ová os.
public $xAxis = null;
/// Y - ová os.
public $yAxis = null;

private $xScale = null;
private $yScale = null;

private $width = 0;
private $height = 0;


/**
 * Poradie: \e top, \e right, \e bottom a \e left.
 */
private $margins = array(5, 5, 5, 5);

private $background = 'white';

private $plots = array();

/**
 * Vytvorenie nového grafu s výškou \a $width a šírkou \a height.
 */
public function __construct($width, $height)
{
	$this->img = @imagecreatetruecolor($width, $height)
	             or die('Cannot Initialize new GD image stream');
	$this->width  = $width;
	$this->height = $height;

	$this->xScale = new LinearScale;
	$this->yScale = new LinearScale;
	$this->yScale->setReverse();

	$this->xAxis = new Axis('x');
	$this->yAxis = new Axis('y');
	$this->xAxis->setScale($this->xScale);
	$this->yAxis->setScale($this->yScale);
}

/**
 * Dealokácia použitých prostriedkov.
 */
public function __destruct()
{
	if ($this->img != null)
	{
		imagedestroy($this->img);
	}
	$this->img = null;
}

/**
 * Odoslanie hlavičky PNG obrázku a vykreslenie grafu.
 */
public function stroke()
{
	imagesavealpha($this->img, true);

	$margins = $this->margins;

	foreach ($this->plots as $plot)
	{
		$this->xScale->setScale($plot->minX(), $plot->maxX());
		$this->yScale->setScale($plot->minY(), $plot->maxY());
	}

	$this->xScale->setRealSize($this->width - $margins[1] - $margins[3]);
	$this->yScale->setRealSize($this->height - $margins[0] - $margins[2]);

	$margins[2] += $this->xAxis->width();
	$margins[3] += $this->yAxis->width();

	// vykreslenie pozadia
	$color = GraphColor::createFromName($this->background);
	imagefill($this->img, 0, 0, $color->allocColor($this->img));

	$this->xScale->setRealSize($this->width - $margins[1] - $margins[3]);
	$this->yScale->setRealSize($this->height - $margins[0] - $margins[2]);
	$this->xScale->setOffset($margins[3]);
	$this->yScale->setOffset($margins[0]);

	foreach ($this->plots as $plot)
	{
		$plot->draw($this->img,
		            $this->xScale,
		            $this->yScale);
	}

	$this->xAxis->draw($this->img, $this->height - $margins[2]);
	$this->yAxis->draw($this->img, $margins[3]);

	header('Content-Type: image/png');
	imagepng($this->img);
}

/**
 * Pridanie grafu.
 */
public function addPlot(Plot $plot)
{
	array_push($this->plots, $plot);
}

}

?>