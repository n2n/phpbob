<?php
namespace phpbob\representation;

use phpbob\representation\anno\PhpAnnotationSet;

interface PhpAnnoCollection {
	public function getPhpAnnotationSet(): PhpAnnotationSet;
}