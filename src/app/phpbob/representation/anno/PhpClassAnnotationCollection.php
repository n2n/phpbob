<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;

class PhpClassAnnotationCollection extends PhpAnnoCollectionAdapter {
	public function __toString() {
		if ($this->isEmpty()) return $this->getPrependingString();
		
		return $this->getPrependingString() . "\t\t" . $this->phpAnnotationSet->getAiVariableName() 
				. '->c(' . $this->getAnnotationString() . ')'. Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
}