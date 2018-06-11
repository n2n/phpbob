<?php
namespace phpbob\representation\anno;

use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;
use phpbob\representation\PhpAnnoCollection;

abstract class PhpAnnoCollectionAdapter implements PhpAnnoCollection {
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
	public function hasPhpAnno(string $typeName) {
		return isset($this->phpAnnoParams[$typeName]);
	}
	
	/**
	 * @param string $typeName
	 * @throws UnknownElementException
	 * @return PhpAnno
	 */
	public function getPhpAnno(string $typeName) {
		if (!isset($this->phpAnnoParams[$typeName])) {
			throw new UnknownElementException('No Anno Param with name "' . $typeName . '" given.');
		}
	
		return $this->phpAnnoParams[$typeName];
	}
	
	/**
	 * @return PhpAnno []
	 */
	public function getPhpAnnos() {
		return $this->phpAnnoParams;
	}
	
	/**
	 * @param string $typeName
	 * @param string $value
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpAnno
	 */
	public function createPhpAnno(string $typeName) {
		$this->checkPhpAnnoName($typeName);
	
		$phpAnnoParam = new PhpAnno($this, $typeName);
	
		$this->phpAnnoParams[$this->buildConstKey($typeName)] = $phpAnnoParam;

		return $phpAnnoParam;
	}
	
	/**
	 * @param string $typeName
	 * @return \phpbob\representation\anno\PhpAnnoAdapter
	 */
	public function removePhpAnno(string $typeName) {
		unset($this->phpAnnoParams);
		
		return $this;
	}
	
	public function resetPhpAnnos() {
		$this->phpAnnoParams = [];
		
		return $this;
	}
	
	private function checkAnnoName(string $typeName) {
		if (!isset($this->phpAnnoParams[$typeName])) return;
		
		throw new IllegalStateException('Anno Param with tyename ' . $typeName . ' already defined.');
 	}
	
 	public function getPhpAnnotationSet(): PhpAnnotationSet {
 		return $this->phpAnnotationSet;
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