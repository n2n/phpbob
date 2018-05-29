<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;
use n2n\reflection\annotation\Annotation;
use n2n\util\StringUtils;

class PhpAnnoParam {
	private $constructorParams = array();
	private $typeName;
	private $annotation;
	
	public function __construct($typeName, array $constructorParams = null) {
		$this->typeName = $typeName;
		if (null !== $constructorParams) {
			$this->constructorParams = $constructorParams;
		}
	}
	
	public function getConstructorParams() {
		return $this->constructorParams;
	}

	public function getTypeName(): string {
		return $this->typeName;
	}

	public function setConstructorParams(array $constructorParams, bool $escape = true) {
		$this->constructorParams = $constructorParams;
		
		if ($escape) {
			$this->escapeConstructorParams();
		}
	}
	
	public function hasConstructorParam(int $position) {
		return count($this->constructorParams) >= $position;
	} 
	
	public function setConstructorParam(int $position, $value, bool $escape = false) {
		$constructorParams = array();
		
		if (!$this->hasConstructorParam($position)) {
			throw new \InvalidArgumentException('Position ' . $position 
					. ' not Available in \"' . $this->typeName . '\"');
		}
		
		$i = 1;
		foreach ($this->constructorParams as $constructorParam) {
			if ($position === $i) {
				$constructorParams[] = ($escape) ? $this->escapeConstructorParam($value) : $value;
			} else {
				$constructorParams[] = $constructorParam;
			}
			$i++;
		}
		
		$this->constructorParams = $constructorParams;
	}
	
	public function addConstructorParam($constructorParam, $escape = false) {
		if ($escape) {
			$constructorParam = self::escapeConstructorParam($constructorParam);
		}
		
		$this->constructorParams[] = $constructorParam;
	}
 
	public function setTypeName($typeName) {
		$this->typeName = $typeName;
	}
	
	public function setAnnotation(Annotation $annotation = null) {
		$this->annotation = $annotation;
	}
	/**
	 * @return Annotation
	 */
	public function getAnnotation() {
		return $this->annotation;
	}
	
	public function __toString() {
		return PhpKeyword::KEYWORD_NEW . ' ' . $this->typeName . '(' . implode(', ', $this->constructorParams) . ')';
	}
	
	public function escapeConstructorParams() {
		foreach ($this->constructorParams as $key => $constructorParam) {
			$this->constructorParams[$key] = self::escapeConstructorParam($constructorParam);
		}
	}
	
	public static function escapeConstructorParam($constructorParam) {
		if (StringUtils::startsWith(PhpKeyword::VARIABLE_PREFIX, $constructorParam)
				|| mb_strpos($constructorParam, PhpKeyword::CONST_SEPERATOR) !== false
				|| StringUtils::startsWith($constructorParam, PhpKeyword::STRING_LITERAL_SEPERATOR)
				|| StringUtils::startsWith($constructorParam, PhpKeyword::STRING_LITERAL_ALTERNATIVE_SEPERATOR)) {
			return $constructorParam;
		}
			
		return PhpKeyword::STRING_LITERAL_SEPERATOR . $constructorParam . PhpKeyword::STRING_LITERAL_SEPERATOR;
	}
}