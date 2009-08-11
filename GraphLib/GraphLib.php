<?php

if (!defined('FONT_DIR'))
{
	define('FONT_DIR','/usr/share/fonts/corefonts');
}


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

/// Minimálna hodnota vo vykresľovanom grafe.
public function realMin()
{
	if ($this->_reverse)
		return $this->_offset + $this->_realSize;
	return $this->_offset;
}

/// Maximálna hodnota vo vykresľovanom grafe.
public function realMax()
{
	if ($this->_reverse)
		return $this->_offset;
	return $this->_offset + $this->_realSize;
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
	if (is_null($this->_max) || $max > $this->_max)
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

}


/**
 * Lineárna mierka grafu.
 */
class LinearScale extends Scale
{

private $dataScale = 0;
private $tick = -1;
private $subTick = -1;
private $compMin = null;
private $compMax = null;
private $roundScale = true;

/// \overload
public function setMin($min) { parent::setMin($min); $this->computeScale(); }
/// \overload
public function setMax($max) { parent::setMax($max); $this->computeScale(); }
/// \overload
public function setScale($min, $max) { parent::setScale($min, $max); $this->computeScale(); }
/// \overload
public function setRealSize($size) { parent::setRealSize($size); $this->computeScale(); }
/// \overload
public function min() {return (is_null($this->compMin) || (!$this->roundScale))?(parent::min()):$this->compMin; }
/// \overload
public function max() {return (is_null($this->compMax) || (!$this->roundScale))?(parent::max()):$this->compMax; }

/**
 * Zapnutie / vypnutie zaokrúhľovania mierky.
 *
 * Pri zapnutom zaokrúhľovaní sa zaokrúhli minimum a maximum tak, aby bolo
 * okrúhlym číslom.
 */
public function setRoundScale($round)
{
	$this->roundScale = $round;
	$this->computeScale();
}

/// Výpočet škály.
private function computeScale()
{
	if ($this->_min != $this->_max)
	{
		$this->dataScale = $this->_realSize / ($this->_max - $this->_min);
	}
	if ($this->dataScale == 0)
		return;

	/// TODO: nahradiť pevné hodnoty
	$this->subTick = $this->roundTick(4 * 1 / ($this->dataScale));
	$this->tick = $this->roundTick(20 * 1 / ($this->dataScale));

	if (!$this->roundScale)
		return;

	$this->compMin = floor($this->_min / $this->tick) * $this->tick;
	$this->compMax = ceil($this->_max / $this->tick) * $this->tick;

	$this->dataScale = $this->_realSize / ($this->compMax - $this->compMin);
}

/// \overload
public function translate($coord)
{
	if ($this->_reverse)
		$coord = $this->max() - $coord;
	return $this->_offset + $coord * $this->dataScale;
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

/// \overload
public function ticks()
{
	$out = array(array(), array());

	if ($this->tick < 0 || $this->subTick < 0)
	{
		return $out;
	}

	$min = $this->min();
	$max = $this->max();

	$subVal  = ceil($min / $this->subTick);
	$mult = round($this->tick / $this->subTick);
	$val = $subVal * $this->subTick;

	while ($val <= $max)
	{
		if ($subVal % $mult == 0)
			array_push($out[0], round($val));
		else
			array_push($out[1], round($val));
		$subVal++;
		$val = $subVal * $this->subTick;
	}

	return $out;
}

};

/**
 * Tvorba a mixovanie farieb.
 */
class GraphColor
{
private static $colorNames = null;
private $r = 0;
private $g = 0;
private $b = 0;
private $alpha = 0;
private $hasAlpha = false;

/**
 * Vytvorenie novej farby z farebných zložiek.
 *
 * \param[in] r     Hodnota červenej farebnej zložky (0 - 255).
 * \param[in] g     Hodnota zelenej farebnej zložky (0 - 255).
 * \param[in] b     Hodnota modrej farebnej zložky (0 - 255).
 * \param[in] alpha Priehľadnosť (0 - 255), 0 úplne neprehľadná, 255 úplne prehľadná.
 */
public static function createFromComponents($r, $g, $b, $alpha = null)
{
	$c = new GraphColor;
	$c->r = $r;
	$c->g = $g;
	$c->b = $b;

	if (!is_null($alpha))
	{
		$c->alpha = $alpha;
		$c->hasAlpha = true;
	}
	return $c;
}

/**
 * Vytvorenie farby z jej názvu.
 *
 * Názvy farieb sú prebrané z 16. základných HTML farieb. Názvy farieb je možné
 * písať ľubovoľnou veľkosťou písma.
 *
 * Farby je možné navzájom kombinovať. Zložky sa v tom prípade oddeľujú znakom
 * \@. Mix 50% zelenej a 50% bielej sa dá zapísať ako 'white\@green'. Pre každú
 * farbu je možné určiť jej váhu. Mix 80% bielej a 20% zelenej sa dá zapísať
 * ako '0.8\@white\@0.2\@green', '0.8\@white\@green', alebo 'white\@0.2\@green'.
 * Ak nie sú určene hodnoty niektorých farieb automaticky sa dopočítajú do 100%.
 * V prípade, že súčet váh prekračuje 100% budú hodnoty farieb bez váhy
 * nastavené na 1 a všetky farby sa normalizujú.
 */
public static function createFromName($name)
{
	self::generateColorNames();

	// Rozdelenie farby na jej časti
	$colors = array();
	$parts = explode('@', $name);

	$colorPart = -1;
	$colorTotal = 0;
	$colorsNoVal = 0;
	$useAlpha = false;
	$alpha = 0;
	foreach ($parts as $part)
	{
		if (is_numeric($part))
		{
			$colorPart = (float)$part;
			$useAlpha = true;
		}
		else
		{
			$color = array(0, 0, 0);
			if (array_key_exists(strtolower($part), self::$colorNames))
				$color = self::$colorNames[strtolower($part)];

			array_push($colors, array($color, $colorPart));
			if ($colorPart >= 0)
			{
				$colorTotal += $colorPart;
			}
			else
			{
				$colorsNoVal++;
			}
			$colorPart = -1;
			$useAlpha = false;
		}
	}
	if ($useAlpha)
	{
		$alpha = (int)($colorPart * 255);
	}

	// Pridanie hodnoty farbám, ktoré nemajú hodnotu
	if ($colorsNoVal > 0)
	{
		$colorVal = 1;
		if ($colorTotal <= 1)
		{
			$colorVal = (1 - $colorTotal) / $colorsNoVal;
		}

		foreach ($colors as $id => $color)
		{
			if ($color[1] < 0)
			{
				$colors[$id][1] = $colorVal;
				$colorTotal += $colorVal;
			}
		}
	}

	// Nastavenie farby
	$color = array(0, 0, 0);
	foreach ($colors as $col)
	{
		for ($i = 0; $i < 3; ++$i)
		{
			$color[$i] += $col[0][$i] * ($col[1] / $colorTotal);
		}
	}

	// Normalizácia farieb
	for ($i = 0; $i < 3; ++$i)
	{
		if ($color[$i] > 255)
			$color[$i] = 255;
		elseif ($color[$i] < 0)
			$color[$i] = 0;
		else
			$color[$i] = round($color[$i]);
	}

	$c = new GraphColor;
	$c->r = $color[0];
	$c->g = $color[1];
	$c->b = $color[2];

	if ($useAlpha)
	{
		$c->alpha = $alpha;
		$c->hasAlpha = true;
	}
	return $c;
}

/**
 * Funkcia sa pokúsi vytvoriť objekt podľa zadaného parametru, ktorý môže
 * byť buď názov farby (podobne ako u createFromName), alebo pole obsahujúce
 * zložky r, g, b a voliteľne alfa kanál.
 */
public static function create($data)
{
	if (is_array($data))
	{
		switch(count($data))
		{
			case 3:
				return self::createFromComponents($data[0], $data[1], $data[2]);
			case 4:
				return self::createFromComponents($data[0], $data[1], $data[2], $data[3]);
			default:
				return false;
		}
	}
	else if (is_string($data))
		return self::createFromName($data);
	else
		return false;
}

/// Zízkanie červenej farebnej zložky.
public function r() { return $this->r; }
/// Zízkanie zelenej farebnej zložky.
public function g() { return $this->r; }
/// Zízkanie modrej farebnej zložky.
public function b() { return $this->r; }
/// Zízkanie hodnoty alfa kanálu.
public function alpha() { return $this->r; }
/// Ak farba má alfa zložku vráti \a true.
public function hasAlpha() { return $this->hasAlpha; }

/**
 * Alokovanie farby v obrázku.
 *
 * \param[in] img Vstupný obrázok.
 * \return Identifikátor alokovanej farby.
 */
public function allocColor($img)
{
	if ($this->hasAlpha)
	{
		return imagecolorallocatealpha($img, $this->r, $this->g, $this->b, (int)($this->alpha / 2));
	}
	else
	{
		return imagecolorallocate($img, $this->r, $this->g, $this->b);
	}
}

private static function generateColorNames()
{
	if (self::$colorNames != null)
	{
		return;
	}

	self::$colorNames = array(
		'black'   => array(  0,   0,   0),
		'gray'    => array(128, 128, 128),
		'silver'  => array(192, 192, 192),
		'white'   => array(255, 255, 255),
		'red'     => array(255,   0,   0),
		'maroon'  => array(128,   0,   0),
		'purple'  => array(128,   0, 128),
		'fuchsia' => array(255,   0, 255),
		'green'   => array(  0, 128,   0),
		'lime'    => array(  0, 255,   0),
		'olive'   => array(128, 128,   0),
		'yellow'  => array(255, 255,   0),
		'navy'    => array(  0,   0, 128),
		'blue'    => array(  0,   0, 255),
		'teal'    => array(  0, 128, 128),
		'aqua'    => array(  0, 255, 255)
	);
}

};

/**
 * Táto trieda vyhľadáva fonty podľa názvu a prevádza ich na absolútnu cestu.
 */
class FontTTF
{

private $absPath = '';

/** Vytvorenie nového fontu z jeho názvu.
 *
 * \sa setFont
 */
public function __construct($font)
{
	$this->setFont($font);
}

/**
 * Nastavenie fontu.
 *
 * Font sa vyhľadáva v ceste určenej definíciou \c FONT_DIR. Názov fontu sa
 * zadáva bez prípony.
 */
public function setFont($font)
{
	if (is_string($font))
	{
		if (strlen($font) > 0 && $font[0] == '/')
		{
			$this->absPath = $font;
		}
		else
		{
			$this->absPath = FONT_DIR . '/' . $font . '.ttf';
		}
	}
}

/// Získanie absolútnej cesty k nastavenému fontu.
public function getAbsPath()
{
	return $this->absPath;
}

}


?>