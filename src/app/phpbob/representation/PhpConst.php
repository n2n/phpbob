<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use n2n\reflection\ArgUtils;
use phpbob\representation\traits\NameChangeSubjectTrait;
class PhpConst implements PhpNamespaceElement {
	use NameChangeSubjectTrait;
	
	private $phpFile;
	private $value;
	private $phpNamespace;
	private $phpClassLike;
	
	public function __construct(PhpFile $phpFile, string $name, string $value, 
			PhpNamespace $phpNameSpace = null, PhpClassLike $phpClassLike = null) {
		$this->phpFile = $phpFile;
		$this->name = $name;
		$this->value = $value;
		$this->phpNamespace = $phpNameSpace;
		ArgUtils::assertTrue(null === $phpClassLike || null !== $phpClassLike && null !== $phpClassLike, 
				'There can not be a classlike without a namespace');
		$this->phpClassLike = $phpClassLike;
	}
	
	/**
	 * @return \phpbob\representation\PhpFile
	 */
	public function getPhpFile() {
		return $this->phpFile;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue(string $value) {
		$this->value = $value;
		
		return $this;
	}

	/**
	 * @return \phpbob\representation\PhpNamespace|null
	 */
	public function getPhpNamespace() {
		return $this->phpNamespace;
	}

	/**
	 * @return \phpbob\representation\PhpClassLike|null
	 */
	public function getPhpClassLike() {
		return $this->phpClassLike;
	}

	public function __toString() {
		return $this->getPrependingString() . 
				"\t" . Phpbob::KEYWORD_CONST . ' ' . $this->getNameValueString() . Phpbob::SINGLE_STATEMENT_STOP;
	}
	
	public function getPhpTypeDefs() : array {
		return null;
	}
}