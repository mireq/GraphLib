<?php

require_once("GraphLib.php");

class Graph
{

/**
 * Obrázok, na ktorý sa vykresľuje graf.
 */
public $img = NULL;

/**
 * Farba pozadia celého obrázku.
 */
private $background = 'white';

/**
 * Vytvorenie nového grafu s výškou \a $width a šírkou \a height.
 */
public function __construct($width, $height)
{
	$this->img = @imagecreatetruecolor($width, $height)
	             or die('Cannot Initialize new GD image stream');
}

/**
 * Dealokácia použitých prostriedkov.
 */
public function __destruct()
{
	if ($this->img != NULL)
	{
		imagedestroy($this->img);
	}
	$this->img = NULL;
}

/**
 * Odoslanie hlavičky PNG obrázku a vykreslenie grafu.
 */
public function plot()
{
	$color = GraphColor::createFromName($this->background);
	imagesavealpha($this->img, true);
	imagefill($this->img, 0, 0, $color->allocate($this->img));
	header('Content-Type: image/png');
	imagepng($this->img);
}

}

?>