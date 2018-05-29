<?php
namespace phpbob\representation;

abstract class PhpAnno {
	use PrependingCodeTrait;
	
	protected $params = array();
	
	public function __construct(array $params = null, $prependingCode = null) {
		$this->prependingCode = $prependingCode;
		if (null !== $params) {
			$this->params = $params;
		}
	}
	/**
	 * @return PhpAnnoParam[]
	 */
	public function getParams() {
		return $this->params;
	}
	
	public function setParams(array $params) {
		$this->params = $params;
	}
	
	public function addParam(PhpAnnoParam $param) {
		$this->params[$param->getTypeName()] = $param;
		return $this;
	}
	
	/**
	 * @param string $typeName
	 * @return PhpAnnoParam
	 */
	public function getParam($typeName) {
		if (!$this->hasParam($typeName)) return null;
		
		return $this->params[$typeName];
	}
	/**
	 * @param string $typeName
	 * @return PhpAnnoParam
	 */
	public function getOrCreateParam($typeName) {
		if (!isset($this->params[$typeName])) {
			$this->params[$typeName] = new PhpAnnoParam($typeName);
		}
		
		return $this->params[$typeName];
	}
	
	public function removeParam($typeName) {
		if (!$this->hasParam($typeName)) return;
		unset($this->params[$typeName]);
		return $this;
	}
	
	public function hasParam($typeName) {
		return isset($this->params[$typeName]);
	}
	
	public function getAnnotationString() {
		return implode(', ', $this->params);
	}
	
	public function mergeWith(PhpAnno $anno) {
		$this->prependingCode .= $anno->getPrependingCode();
		foreach ($anno->getParams() as $param) {
			$this->addParam($param);
		}
	}
	
	public function isEmpty() {
		return count($this->params) === 0;
	}
}