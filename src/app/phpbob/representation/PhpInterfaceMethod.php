<?php
namespace phpbob\representation;

use phpbob\representation\traits\NameChangeSubjectTrait;

class PhpInterfaceMethod extends PhpParamContainerAdapter {
	use NameChangeSubjectTrait;
	
	private $phpInterFace;
	
	public function __construct(PhpInterface $phpInterface, string $name) {
		$this->phpInterFace = $phpInterface;
		$this->name = $name;	
	}
}