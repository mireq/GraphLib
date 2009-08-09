<?php

require_once("GraphLib.php");

class Graph
{

/**
 * Obrázok, na ktorý sa vykresľuje graf.
 */
public $img = null;

private $width = 0;
private $height = 0;

/**
 * Poradie: \e top, \e right, \e bottom a \e left.
 */
private $margins = array(0, 0, 0, 0);

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

	// vykreslenie pozadia
	$color = GraphColor::createFromName($this->background);
	imagefill($this->img, 0, 0, $color->allocColor($this->img));

	foreach ($this->plots as $plot)
	{
		$plot->draw($this->img,
		            $this->margins[3],
		            $this->margins[0],
		            $this->width  - $this->margins[1],
		            $this->height - $this->margins[2]);
	}

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