<?php

/**
 * Trieda na vykreslenie osí grafu.
 */
class Axis
{

private $scale = null;
private $ticks = null;
private $font = null;
private $fontSize = 10;
private $labelAngle = 0;
private $labelMargin = 5;
private $type;

/**
 * Vytvorenie novej osi.
 *
 * \param type Typ osi ('x' pre os X, 'y' pre os Y).
 */
public function __construct($type)
{
	$this->type = $type;
	$this->font = new FontTTF('arial');
}

/// Nastavenie fontu hodnôt na osi.
public function setFont($font)
{
	$this->font->setFont($font);
}

/// Nastavenie veľkosti fontu.
public function setFontSize($size)
{
	$this->fontSize = $size;
}

/// Nastavenie uhlu pre popisy osí.
public function setLabelAngle($angle)
{
	$this->labelAngle = $angle;
}

/// Nastavenie odstupu popisov od osi.
public function setLabelMargin($margin)
{
	$this->labelMargin = $margin;
}

/**
 * Vykreslenie značky na osi.
 *
 * \param img       Vykresľovaný obrázok.
 * \param pos       Pozícia značky na osi.
 * \param offset    Pozícia osi.
 * \param lineColor Farba čiary.
 * \param length    Dĺžka značky.
 */
private function drawTick($img, $pos, $offset, $lineColor, $length)
{
/// TODO: Pridať výnimky
	if ($this->type === 'x')
		imageline($img, $pos, $offset, $pos, $offset - $length, $lineColor);
	else if ($this->type === 'y')
		imageline($img, $offset, $pos, $offset + $length, $pos, $lineColor);
}

/**
 * Nastavenie mierky osi.
 */
public function setScale(Scale $scale)
{
	$this->scale = $scale;
	$this->ticks = null;
}

/**
 * Vykreslenie osi grafu.
 *
 * \param img    Vykresľovaný obrázok.
 * \param offset Poloha osi.
 */
public function draw($img, $offset)
{
	$lineColor = GraphColor::create('black')->allocColor($img);

	if (is_null($this->ticks))
		$this->ticks = $this->scale->ticks();

/// TODO: Nahradiť pevné čísla
	foreach ($this->ticks[0] as $t)
	{
		$pos = $this->scale->translate($t);
		$this->drawTick($img, $pos, $offset, $lineColor, 5);
	}
	foreach ($this->ticks[1] as $t)
	{
		$pos = $this->scale->translate($t);
		$this->drawTick($img, $pos, $offset, $lineColor, 2);
	}
	$this->drawLabels($img, $offset, $lineColor);
}

/// Vykreslenie popisov osí.
private function drawLabels($img, $offset, $color)
{
/// TODO: vlastný formát popisov
	foreach ($this->ticks[0] as $t)
	{
		$pos = $this->scale->translate($t);
		$dim = imagettfbbox ($this->fontSize, $this->labelAngle, $this->font->getAbsPath(), $t);
		$midX = ($dim[0] + $dim[4]) / 2;
		$midY = ($dim[1] + $dim[5]) / 2;
		$offX = $dim[0] - $midX;
		$offY = $dim[1] - $midY;
		$width  = max($dim[0], $dim[2], $dim[4], $dim[6]) - min($dim[0], $dim[2], $dim[4], $dim[6]) + 1;
		$height = max($dim[1], $dim[3], $dim[5], $dim[7]) - min($dim[1], $dim[3], $dim[5], $dim[7]) + 1;
		if ($this->type === 'x')
		{
			imagettftext($img,
			             $this->fontSize,
			             $this->labelAngle,
			             $pos - $midX,
			             $offset + $offY + $height / 2 + $this->labelMargin,
			             $color,
			             $this->font->getAbsPath(),
			             $t);
		}
		elseif ($this->type === 'y')
		{
			imagettftext($img,
			             $this->fontSize,
			             $this->labelAngle,
			             $offset + $offX - $width / 2 - $this->labelMargin,
			             $pos - $midY,
			             $color,
			             $this->font->getAbsPath(),
			             $t);
		}
	}
}

/// Metóda vracia šírku popisu osí.
public function width()
{
	$ticks = $this->scale->ticks();

	if ($this->type === 'x')
	{
		$maxHeight = 0;
		foreach ($ticks[0] as $t)
		{
			$dim = imagettfbbox ($this->fontSize, $this->labelAngle,
			                     $this->font->getAbsPath(), $t);
			$height = max($dim[1], $dim[3], $dim[5], $dim[7]) -
			          min($dim[1], $dim[3], $dim[5], $dim[7]) + 1;
			if ($height > $maxHeight)
				$maxHeight = $height;
		}
		return $maxHeight + $this->labelMargin;
	}
	elseif ($this->type === 'y')
	{
		$maxWidth = 0;
		foreach ($ticks[0] as $t)
		{
			$dim = imagettfbbox ($this->fontSize, $this->labelAngle,
			                     $this->font->getAbsPath(), $t);
			$width  = max($dim[0], $dim[2], $dim[4], $dim[6]) -
			          min($dim[0], $dim[2], $dim[4], $dim[6]) + 1;
			if ($width > $maxWidth)
				$maxWidth = $width;
		}
		return $maxWidth + $this->labelMargin;
	}
}

};

?>