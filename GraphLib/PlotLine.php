<?php

require_once("Plot.php");

class PlotLine extends Plot
{

private $xdata = array();
private $ydata = array();

private $minX = null;
private $minY = null;
private $maxX = null;
private $maxY = null;

private $lineColor = 'black';
private $fillColor = null;

public function __construct($ydata, $xdata = null)
{
	if (is_null($xdata))
	{
		$xdata = array();
		for ($i = 0; $i < count($ydata); ++$i)
		{
			array_push($xdata, $i);
		}
	}

	$this->xdata = $xdata;
	$this->ydata = $ydata;

	if (count($xdata) == 0)
		return;

	$this->minX = $xdata[0];
	$this->maxX = $xdata[0];
	$this->minY = $ydata[0];
	$this->maxY = $ydata[0];

	for ($i = 0; $i < count($xdata); ++$i)
	{
		if ($xdata[$i] < $this->minX)
			$this->minX = $xdata[$i];
		if ($xdata[$i] > $this->maxX)
			$this->maxX = $xdata[$i];
		if ($ydata[$i] < $this->minY)
			$this->minY = $ydata[$i];
		if ($ydata[$i] > $this->maxY)
			$this->maxY = $ydata[$i];
	}
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
public function draw($img, $x1, $y1, $x2, $y2)
{
	imageantialias($img, true);

	$xRange = $this->maxX - $this->minX;
	$yRange = $this->maxY - $this->minY;

	$xCenter = ($x1 + $x2) / 2;
	$yCenter = ($y1 + $y2) / 2;

	if (!is_null($this->lineColor))
		$lineColor = GraphColor::create($this->lineColor)->allocColor($img);
	if (!is_null($this->fillColor))
		$fillColor = GraphColor::create($this->fillColor)->allocColor($img);


	$computed = array();
	for ($i = 0; $i < count($this->xdata); ++$i)
	{
		$xd = $this->xdata[$i];
		$yd = $this->ydata[$i];
		$cx = $xCenter;
		$cy = $yCenter;
		if ($xRange > 0)
			$cx = $x1 + ($x2 - $x1) * (($xd - $this->minX) / $xRange);
		if ($yRange > 0)
			$cy = $y1 + ($y2 - $y1) *(1.0 - (($yd - $this->minY) / $yRange));
		array_push($computed, $cx);
		array_push($computed, $cy);
	}

	$polygon = array($x1, $y2);
	$polygon = array_merge($polygon, $computed);
	array_push($polygon, $x2);
	array_push($polygon, $y2);

	if (!is_null($this->fillColor))
		imagefilledpolygon($img, $polygon, count($polygon) / 2, $fillColor);
	unset($polygon);

	$ox = null;
	$oy = null;
	for ($i = 0; $i < count($this->xdata); ++$i)
	{
		$cx = $computed[$i * 2];
		$cy = $computed[$i * 2 + 1];
		if (!is_null($ox))
		{
			if (!is_null($this->lineColor))
			{
				imageline($img, $ox, $oy, $cx, $cy, $lineColor);
			}
		}
		$ox = $cx;
		$oy = $cy;
	}

	imageantialias($img, false);
}

}

?>