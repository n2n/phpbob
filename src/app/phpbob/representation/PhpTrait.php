<?php
namespace phpbob\representation;

use phpbob\Phpbob;

class PhpTrait extends PhpClassLikeAdapter {
	public function __toString() {
		return Phpbob::KEYWORD_TRAIT . ' ' . $this->getName() . ' ' . Phpbob::GROUP_STATEMENT_OPEN . PHP_EOL . $this->gen;
	}
}