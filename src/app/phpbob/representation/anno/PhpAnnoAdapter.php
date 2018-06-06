<?php
namespace phpbob\representation\anno;

use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;

abstract class PhpAnnoAdapter {
	use PrependingCodeTrait;
	
	protected $phpAnnotationSet;
	protected $phpAnnoParams = array();
	
	public function __construct(PhpAnnotationSet $phpAnnotationSet, $prependingCode = null) {
		$this->phpAnnotationSet = $phpAnnotationSet;
		$this->prependingCode = $prependingCode;
	}

	/**
	 * @param string $typeName
	 * @return bool
	 */
	public function hasPhpAnnoParam(string $typeName) {
		return isset($this->phpAnnoParams[$typeName]);
	}
	
	/**
	 * @param string $typeName
	 * @throws UnknownElementException
	 * @return PhpAnnoParam
	 */
	public function getPhpAnnoParam(string $typeName) {
		if (!isset($this->phpAnnoParams[$typeName])) {
			throw new UnknownElementException('No Anno Param with name "' . $typeName . '" given.');
		}
	
		return $this->phpAnnoParams[$typeName];
	}
	
	/**
	 * @return PhpAnnoParam []
	 */
	public function getPhpAnnoParams() {
		return $this->phpAnnoParams;
	}
	
	/**
	 * @param string $typeName
	 * @param string $value
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpAnnoParam
	 */
	public function createPhpAnnoParam(string $typeName) {
		$this->checkPhpAnnoParamName($typeName);
	
		$phpAnnoParam = new PhpAnnoParam($this, $typeName);
	
		$this->phpAnnoParams[$this->buildConstKey($typeName)] = $phpAnnoParam;

		return $phpAnnoParam;
	}
	
	/**
	 * @param string $typeName
	 * @return \phpbob\representation\anno\PhpAnnoAdapter
	 */
	public function removePhpAnnoParam(string $typeName) {
		unset($this->phpAnnoParams);
		
		return $this;
	}
	
	public function resetPhpAnnoParams() {
		$this->phpAnnoParams = [];
		
		return $this;
	}
	
	private function checkAnnoParamName(string $typeName) {
		if (!isset($this->phpAnnoParams[$typeName])) return;
		
		throw new IllegalStateException('Anno Param with tyename ' . $typeName . ' already defined.');
 	}
	
	public function getAnnotationString() {
		return implode(', ', $this->phpAnnoParams);
 	}
	
// 	public function mergeWith(PhpAnnoAdapter $anno) {
// 		$this->prependingCode .= $anno->getPrependingCode();
// 		foreach ($anno->getParams() as $param) {
// 			$this->addParam($param);
// 		}
// 	}
	
	public function isEmpty() {
		return count($this->phpAnnoParams) === 0;
	}
}