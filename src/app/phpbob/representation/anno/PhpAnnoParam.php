<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;
use n2n\reflection\annotation\Annotation;
use n2n\util\StringUtils;
use phpbob\representation\PhpAnno;

class PhpAnnoParam {
	private $phpAnno;
	private $constructorParams = array();
	private $typeName;
	private $annotation;
	
	public function __construct(PhpAnno $phpAnno, string $typeName) {
		$this->phpAnno = $phpAnno;
		$this->typeName = $typeName;
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
		return Phpbob::KEYWORD_NEW . ' ' . $this->typeName . '(' . implode(', ', $this->constructorParams) . ')';
	}
	
	public function escapeConstructorParams() {
		foreach ($this->constructorParams as $key => $constructorParam) {
			$this->constructorParams[$key] = self::escapeConstructorParam($constructorParam);
		}
	}
	
	public static function escapeConstructorParam($constructorParam) {
		if (StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, $constructorParam)
				|| mb_strpos($constructorParam, Phpbob::CONST_SEPERATOR) !== false
				|| StringUtils::startsWith($constructorParam, Phpbob::STRING_LITERAL_SEPERATOR)
				|| StringUtils::startsWith($constructorParam, Phpbob::STRING_LITERAL_ALTERNATIVE_SEPERATOR)) {
			return $constructorParam;
		}
			
		return Phpbob::STRING_LITERAL_SEPERATOR . $constructorParam . Phpbob::STRING_LITERAL_SEPERATOR;
	}
}