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

	// Ak sú nesprávne nastavené hodnoty prehodíme ich
	if ($this->_min > $this->_max)
		list($this->_min, $this->_max) = array($this->_max, $this->_min);

	// Zabránime rovnakej minimálnej a maximálnej hodnote
	if ($this->_min == $this->_max)
		$this->_max++;
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

}

/**
 * Lineárna mierka grafu.
 */
class LinearScale extends Scale
{

/// \overload
public function translate($coord)
{
	if ($this->_reverse)
		$coord = $this->_max - $coord;
	return $this->_offset + $coord * $this->_realSize / ($this->_max - $this->_min);
}

};

?>