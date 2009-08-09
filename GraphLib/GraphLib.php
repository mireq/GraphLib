<?php

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
public function allocate($img)
{
	if ($this->hasAlpha)
	{
		return imagecolorallocatealpha($img, $this->r, $this->g, $this->b, (int)$this->alpha / 2);
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

?>