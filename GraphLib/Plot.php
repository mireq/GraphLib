<?php

require_once("GraphLib.php");

class Plot
{

/**
 * Virtuálna funkcia na vykreslenie grafu do obrázku.
 */
public function draw($img, Scale $xScale, Scale $yScale) {}

/// Virtuálna funkcia, vracia minimálnu hodnotu na X-ovej osi.
public function minX() {}
/// Virtuálna funkcia, vracia maximálnu hodnotu na X-ovej osi.
public function maxX() {}
/// Virtuálna funkcia, vracia minimálnu hodnotu na Y-ovej osi.
public function minY() {}
/// Virtuálna funkcia, vracia maximálnu hodnotu na Y-ovej osi.
public function maxY() {}

}

?>