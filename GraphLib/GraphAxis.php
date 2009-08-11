<?php

/**
 * Abstraktná trieda na prevod hodnôt grafu do súradníc v obrázku.
 */
class Scale
{

/// Minimálna hodnota v grafe.
protected $_min = null;
/// Maximálna hodnota v grafe.
protected $_max = null;
/// Reálna veľkosť oblasti vykresľovania.
protected $_realSize = 1;
/// Posun oblasti vykresľovania.
protected $_offset = 0;
/// Prevrátené hodnoty.
protected $_reverse = false;

/// Nastavenie minimálnej hodnoty mierky.
public function setMin($min)
{
	$this->_min = floor($min);
}

/// Nastavenie maximálnej hodnoty mierky.
public function setMax($max)
{
	$this->_max = ceil($max);
}

/// Zistenie minimálnej hodnoty mierky.
public function min()
{
	return $this->_min;
}

/// Zistenie maximálnej hodnoty mierky.
public function max()
{
	return $this->_max;
}

/**
 * Nastavenie hodnôt
 *
 * K vytvoreniu výslednej hodnoty sa použijú už existujúce hodnoty.
 */
public function setScale($min, $max)
{
	if (is_null($this->_min) || $min < $this->_min)
		$this->_min = $min;
	if (is_null($this->_max) || $max < $this->_max)
		$this->_max = $max;

/*
	// Ak sú nesprávne nastavené hodnoty prehodíme ich
	if ($this->_min > $this->_max)
		list($this->_min, $this->_max) = array($this->_max, $this->_min);

	// Zabránime rovnakej minimálnej a maximálnej hodnote
	if ($this->_min == $this->_max)
		$this->_max++;
*/
}

/// Nastavenie veľkosti oblasti, na ktorú sa vykresľuje graf.
public function setRealSize($size)
{
	$this->_realSize = $size;
}

/// Nastavenie posunu súradníc.
public function setOffset($offset)
{
	$this->_offset = $offset;
}

/**
 * Nastavenie prevrátených hodnôt
 *
 * Minimálne hodnoty sa tak stanú maximálnymi a opačne. Pri vykresľovaní obrázkov
 * pomocou GD má os \e y prevrátené hodnoty.
 */
public function setReverse($reverse = true)
{
	$this->_reverse = $reverse;
}

/**
 * "Virtuálna funkcia" na prepočet súradníc z hodnôt grafu na pixely vo výstupnom grafe.
 */
public function translate($coord)
{
}

/**
 * "Virtuálna funkcia", ktorá vracia hodnoty značiek na osi.
 *
 * \return Zoznam značiek je poľom skladajúcim sa z ďalších 2 polí. Prvým poľom
 *   je zonzam primárnych značiek, druhým zoznam sekundárnych značiek.
 */
public function ticks()
{
}

/**
 * Zaokruhlenie kroku najbližšie vyššie okrúhle číslo.
 *
 * Táto funkcia zaokrúhľuje na násobky 1, 2, 5 a 10.
 */
protected function roundTick($num)
{
	$sign = ($num >= 0)?1:-1;
	$num = abs($num);
	$pom = 0.00001;
	if ($num == $pom)
	{
		return $num * $sign;
	}
	elseif ($num > $pom)
	{
		$multiply = 10;
		for ($i = 0; $i < 30; ++$i)
		{
			if ($pom >= $num)
				return $pom * $sign;
			if ($pom * 2 >= $num)
				return $pom * 2 * $sign;
			if ($pom * 5 >= $num)
				return $pom * 5 * $sign;
			$pom = $pom * $multiply;
		}
	}
	else
	{
		return $pom * $sign;
	}
}

}

/**
 * Lineárna mierka grafu.
 */
class LinearScale extends Scale
{

private $dataScale = 0;

/// \overload
public function setMin($min) { parent::setMin($min); $this->computeScale(); }
/// \overload
public function setMax($max) { parent::setMax($max); $this->computeScale(); }
/// \overload
public function setScale($min, $max) { parent::setScale($min, $max); $this->computeScale(); }
/// \overload
public function setRealSize($size) { parent::setRealSize($size); $this->computeScale(); }

/// Výpočet škály.
private function computeScale()
{
	if ($this->_min != $this->_max)
	{
		$this->dataScale = $this->_realSize / ($this->_max - $this->_min);
	}
}

/// \overload
public function translate($coord)
{
	if ($this->_reverse)
		$coord = $this->_max - $coord;
	return $this->_offset + $coord * $this->dataScale;
}

/// \overload
public function ticks()
{
	$out = array(array(), array());

	/// TODO: nahradiť pevné hodnoty
	$subTick = $this->roundTick(4 * 1 / ($this->dataScale));
	$tick = $this->roundTick(20 * 1 / ($this->dataScale));

	if ($tick < 0 || $subTick < 0)
	{
		return $out;
	}

	$subVal  = ceil($this->_min / $subTick) * $subTick;
	$tickVal = ceil($this->_min / $tick) * $tick;

	do
	{
		while ($subVal < $tickVal && $subVal <= $this->_max)
		{
			array_push($out[1], $subVal);
			$subVal += $subTick;
		}
		if ($tickVal <= $this->_max)
		{
			array_push($out[0], $tickVal);
		}
		$tickVal += $tick;
		$subVal += $subTick;
	} while($tickVal <= $this->_max || $subVal <= $this->_max);

	return $out;
}

};

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
			imagettftext($img, $this->fontSize, $this->labelAngle, $pos - $midX, $offset + $offY + $height / 2 + $this->labelMargin, $color, $this->font->getAbsPath(), $t);
		}
		elseif ($this->type === 'y')
		{
			imagettftext($img, $this->fontSize, $this->labelAngle, $offset + $offX - $width / 2 - $this->labelMargin, $pos - $midY, $color, $this->font->getAbsPath(), $t);
		}
	}
}

/// Metóda vracia šírku popisu osí.
public function width()
{
	if (is_null($this->ticks))
		$this->ticks = $this->scale->ticks();

	if ($this->type === 'x')
	{
		$maxHeight = 0;
		foreach ($this->ticks[0] as $t)
		{
			$dim = imagettfbbox ($this->fontSize, $this->labelAngle, $this->font->getAbsPath(), $t);
			$height = max($dim[1], $dim[3], $dim[5], $dim[7]) - min($dim[1], $dim[3], $dim[5], $dim[7]) + 1;
			if ($height > $maxHeight)
				$maxHeight = $height;
		}
		return $maxHeight + $this->labelMargin;
	}
	elseif ($this->type === 'y')
	{
		$maxWidth = 0;
		foreach ($this->ticks[0] as $t)
		{
			$dim = imagettfbbox ($this->fontSize, $this->labelAngle, $this->font->getAbsPath(), $t);
			$width  = max($dim[0], $dim[2], $dim[4], $dim[6]) - min($dim[0], $dim[2], $dim[4], $dim[6]) + 1;
			if ($width > $maxWidth)
				$maxWidth = $width;
		}
		return $maxWidth + $this->labelMargin;
	}
}

};

?>