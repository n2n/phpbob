<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use n2n\reflection\ArgUtils;

class PhpUse {
	use PrependingCodeTrait;
	
	const TYPE_FUNCTION = 'function';
	const TYPE_CONST = 'const';
	
	private $typeName;
	private $type;
	private $asName;
	private $phpNamespace;
	
	public function __construct(string $typeName, PhpNamespace $phpNamespace = null) {
		$this->typeName = $typeName;
		$this->phpNamespace = $phpNamespace;
	}
	
	public function getTypeName() {
		return $this->typeName;
	}

	public function setTypeName($typeName) {
		$this->typeName = $typeName;
	}

	public function getType() {
		return $this->type;
	}

	public function setType(string $type = null) {
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		
		$this->type = $type;
	}

	public function getAsName() {
		return $this->asName;
	}

	public function setAsName(string $asName = null) {
		$this->asName = $asName;
	}

	public function __toString() {
		return $this->getPrependingString() . Phpbob::KEYWORD_USE . ' ' . $this->typeName
				. Phpbob::SINGLE_STATEMENT_STOP;
	}
	
	public static function getTypes() {
		return array(self::TYPE_FUNCTION, self::TYPE_CONST);
	}
}