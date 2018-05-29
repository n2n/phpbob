<?php
namespace phpbob\representation;

class PhpInterface extends PhpTypeAdapter {
	use InterfacesTrait;
	
	public function extendsInterface($typeName) {
		return $this->hasInterface($typeName);
	}
	
	public function isInterface() {
		return true;
	}
}