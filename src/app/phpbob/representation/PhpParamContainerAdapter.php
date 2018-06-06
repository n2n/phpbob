<?php
namespace phpbob\representation;

abstract class PhpParamContainerAdapter implements PhpParamContainer {
	
	private $returnPhpTypeDef;
	private $methodCode;
	private $phpParams;

	public function getReturnPhpTypeDef() {
		return $this->returnPhpTypeDef;
	}

	public function setReturnPhpTypeDef(PhpTypeDef $returnPhpTypeDef = null) {
		$this->returnPhpTypeDef = $returnPhpTypeDef;
		
		return $this;
	}

	public function getMethodCode() {
		return $this->methodCode;
	}

	public function setMethodCode(string $methodCode = null) {
		$this->methodCode = (string) $methodCode;
		
		return $this;
	}

	public function getPhpParams() {
		return $this->phpParams;
	}
	
	public function resetPhpParams() {
		$this->phpParams = [];
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return PhpParam|NULL
	 */
	public function getPhpParam(string $name) {
		if (isset($this->phpParams[$name])) return $this->phpParams[$name];
		
		return null;
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @param PhpTypeDef $phpTypeDef
	 * @param bool $splat
	 * 
	 * @return PhpParam
	 * 
	 * Creates a PhpParam for this Container, if there is already a param with this name, it gets replaced
	 */
	public function createPhpParam(string $name, string $value = null, 
			PhpTypeDef $phpTypeDef = null, bool $splat = false) {
		$phpParam = new PhpParam($this, $name, $value, $phpTypeDef, $splat);
		$this->phpParams[$name] = $phpParam;
		
		return $phpParam;
	}
	
	public function getPhpTypeDefs() : array {
		$typeDefs = [$this->returnPhpTypeDef];
		
		foreach ($this->phpParams as $phpParam) {
			$typeDefs[] = $phpParam->getPhpTypeDef();
		}
		
		return $typeDefs;
	}
}