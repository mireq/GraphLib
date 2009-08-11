<?php

/**
 * Trieda na vykresľovanie mriežky a pozadia pod grafom.
 */
class GraphGrid
{

private $fillColor = null;
private $lineColor = null;

/**
 * Nastavenie farby výplne pod grafom.
 *
 * Atribút \a color je poľom položiek s rovnakým formátom, ako prijíma funkcia
 * GraphColor::create. V prípade, že je zadaná jediná položka poľa bude mať
 * pozadie jednotnú farbu. V prípade viacerých farieb sa budú striedať podľa
 * značiek osí.
 *
 * \code
$graph = new Graph(600, 300);
$graph->xGrid->setFillColor(array('silver', 'silver@white'));
 * \endcode
 *
 * \sa GraphColor::create
 */
public function setFillColor($color)
{
	$this->fillColor = $color;
}

/**
 * Nastavenie farby pomocných čiar v grafe.
 */
public function setLineColor($color)
{
	$this->lineColor = $color;
}

/// Výpočet pomocných hodnôt pre vykreslenie mriežky a pozadia grafu.
private function getPomVars(Scale $xScale, Scale $yScale, $type)
{
	$min = 0;
	$max = 0;
	$minPos = 0;
	$maxPos = 0;
	$ticks = array();
	$scale = null;
	if ($type === 'x')
	{
		$min = $xScale->realMin();
		$max = $xScale->realMax();
		$minPos = $yScale->realMin();
		$maxPos = $yScale->realMax();
		list($ticks, $subTicks) = $yScale->ticks();
		$scale = $yScale;
		
	}
	elseif ($type === 'y')
	{
		$min = $yScale->realMin();
		$max = $yScale->realMax();
		$minPos = $xScale->realMin();
		$maxPos = $xScale->realMax();
		list($ticks, $subTicks) = $xScale->ticks();
		$scale = $xScale;
	}
	return array($min, $max, $minPos, $maxPos, $ticks, $scale);
}

/**
 * Vykreslenie mirežky grafu.
 *
 * \param img Obrázok, na ktorý sa vykresľuje mriežka.
 * \param xScale Mierka X-ovej osi.
 * \param yScale Mierka Y-ovej osi.
 * \param type Typ mriežky (buď rovnobežná s X - 'x', alebo rovnobežná s Y - 'y').
 */
public function drawGrid($img, Scale $xScale, Scale $yScale, $type)
{
	if (is_null($this->lineColor))
		return;

	$lineColor = GraphColor::create($this->lineColor)->allocColor($img);

	list($min, $max, $minPos, $maxPos, $ticks, $scale) = $this->getPomVars($xScale, $yScale, $type);

	foreach($ticks as $t)
	{
		$pos = $scale->translate($t);
		if ($type === 'x')
			imageline($img, $min, $pos, $max, $pos, $lineColor);
		elseif ($type === 'y')
			imageline($img, $pos, $min, $pos, $max, $lineColor);
	}
}

/**
 * Vykreslenie pozadia grafu.
 *
 * Parametre sú zhodné s funkciou drawGrid.
 */
public function drawGridBg($img, Scale $xScale, Scale $yScale, $type)
{
	if (is_null($this->fillColor))
		return;

	$fillColor = array();
	if (!is_null($this->fillColor))
	{
		foreach ($this->fillColor as $color)
		{
			array_push($fillColor, GraphColor::create($color)->allocColor($img));
		}
	}

	list($min, $max, $minPos, $maxPos, $ticks, $scale) = $this->getPomVars($xScale, $yScale, $type);

	if (count($fillColor) > 0)
	{
		$oldPos = $minPos;
		$rownum = 1;
		
		foreach ($ticks as $t)
		{
			$pos = $scale->translate($t);
			if ($type === 'x')
				imagefilledrectangle($img, $min, $oldPos, $max, $pos, $fillColor[$rownum % count($fillColor)]);
			if ($type === 'y')
				imagefilledrectangle($img, $oldPos, $min, $pos, $max, $fillColor[$rownum % count($fillColor)]);
			$oldPos = $pos;
			$rownum++;
		}
		if ($type === 'x')
			imagefilledrectangle($img, $min, $oldPos, $max, $maxPos, $fillColor[$rownum % count($fillColor)]);
		if ($type === 'y')
			imagefilledrectangle($img, $oldPos, $min, $maxPos, $max, $fillColor[$rownum % count($fillColor)]);
	}
}

};

?>