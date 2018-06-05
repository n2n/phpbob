<?php
namespace phpbob\representation;

class PhpTraitUse {
	private $phpClassLike;
	private $phpTypeDef;
	
	public function __construct(PhpClassLike $phpClassLike, PhpTypeDef $phpTypeDef) {
		$this->phpClassLike = $phpClassLike;
		$this->phpTypeDef = $phpTypeDef;
	}
	
	public function getPhpTypeDef() {
		return $this->phpTypeDef;
	}
}