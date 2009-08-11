<?php

require_once("Plot.php");

/**
 * Čiarový graf.
 */
class PlotLine extends Plot
{

private $xData = array();
private $yData = array();

private $minX = null;
private $minY = null;
private $maxX = null;
private $maxY = null;

private $lineColor = 'black';
private $fillColor = null;

/**
 * Vytvorenie nového čiarového grafu s dátami \a yData. Voliteľne je možné
 * nastaviť dáta na x-ovej osi (\a xData). Ak táto položka zostane nenastavená
 * trieda automaticky doplní za \a xData čísla od 0 po počet dát - 1.
 */
public function __construct(array $yData, $xData = null)
{
	if (is_null($xData))
	{
		$xData = array();
		for ($i = 0; $i < count($yData); ++$i)
		{
			array_push($xData, $i);
		}
	}

	$this->xData = $xData;
	$this->yData = $yData;

	if (count($xData) == 0)
		return;

	$this->minX = $xData[0];
	$this->maxX = $xData[0];
	$this->minY = $yData[0];
	$this->maxY = $yData[0];

	for ($i = 0; $i < count($xData); ++$i)
	{
		if ($xData[$i] < $this->minX)
			$this->minX = $xData[$i];
		if ($xData[$i] > $this->maxX)
			$this->maxX = $xData[$i];
		if ($yData[$i] < $this->minY)
			$this->minY = $yData[$i];
		if ($yData[$i] > $this->maxY)
			$this->maxY = $yData[$i];
	}

	// Zabránime chybe pri identických minimálnych a maximálnych hodnotách
	if ($this->minX == $this->maxX)
		$this->maxX++;
	if ($this->minY == $this->maxY)
		$this->maxY++;
}

/**
 * Nastavenie farby čiary.
 *
 * \sa GraphColor
 */
public function setLineColor($color)
{
	$this->lineColor = $color;
}

/**
 * Nastavenie farby pozadia.
 *
 * \sa GraphColor
 */
public function setFillColor($color)
{
	$this->fillColor = $color;
}

/// \overload
public function minX() { return $this->minX; }
/// \overload
public function maxX() { return $this->maxX; }
/// \overload
public function minY() { return $this->minY; }
/// \overload
public function maxY() { return $this->maxY; }

/// \overload
public function draw($img, Scale $xScale, Scale $yScale)
{
/// TODO: Nastavenie hrúbky čiar
	imagesetthickness($img, 1);
/// TODO: Nastavenia antialiasingu
	imageantialias($img, true);


	if (!is_null($this->lineColor))
		$lineColor = GraphColor::create($this->lineColor)->allocColor($img);
	if (!is_null($this->fillColor))
		$fillColor = GraphColor::create($this->fillColor)->allocColor($img);

	$computed = array();
	for ($i = 0; $i < count($this->xData); ++$i)
	{
		array_push($computed, $xScale->translate($this->xData[$i]));
		array_push($computed, $yScale->translate($this->yData[$i]));
	}

	if (!is_null($this->fillColor))
	{
		$polygon = array($xScale->translate($xScale->min()), $yScale->translate($yScale->min()));
		$polygon = array_merge($polygon, $computed);
		array_push($polygon, $xScale->translate($xScale->max()));
		array_push($polygon, $yScale->translate($yScale->min()));

		imagefilledpolygon($img, $polygon, count($polygon) / 2, $fillColor);
		unset($polygon);
	}

	$ox = null;
	$oy = null;
	for ($i = 0; $i < count($this->xData); ++$i)
	{
		$cx = $computed[$i * 2];
		$cy = $computed[$i * 2 + 1];
		if (!is_null($this->lineColor))
		{
			if (!is_null($ox))
			{
				imageline($img, $ox, $oy, $cx, $cy, $lineColor);
			}
			imagesetpixel($img, $cx, $cy, $lineColor);
		}
		$ox = $cx;
		$oy = $cy;
	}

	imageantialias($img, false);
}

}

?>