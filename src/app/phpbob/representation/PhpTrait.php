<?php
namespace phpbob\representation;

class PhpTrait extends PhpTypeAdapter implements PhpTraitContainer {
	use TraitsTrait;
	use PropertiesTrait;
	
	public function isTrait() {
		return true;
	}
}