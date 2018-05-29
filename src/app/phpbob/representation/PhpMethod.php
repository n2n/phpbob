<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;
use phpbob\PhprepUtils;

class PhpMethod {
	use PrependingCodeTrait;

	private $name;
	private $classifier;
	private $static = false;
	private $final = false;
	private $abstract = false;
	private $returnType;
	private $methodCode;

	private $params = array();

	public function __construct($name, array $params = null, $classifier = null, $prependingCode = null) {
		$this->name = $name;
		if (null !== $params) {
			$this->params = $params;
		}

		//http://php.net/manual/de/language.oop5.visibility.php
		if (null === $classifier) {
			$this->classifier = PhpKeyword::CLASSIFIER_PUBLIC;
		} else {
			$this->classifier = $classifier;
		}
		

		$this->prependingCode = $prependingCode;
	}

	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}

	public function getClassifier() {
		return $this->classifier;
	}

	public function setClassifier($classifier) {
		$this->classifier = $classifier;
	}

	public function isStatic() {
		return $this->static;
	}

	public function setStatic($static) {
		$this->static = (bool)$static;
	}

	public function isFinal() {
		return $this->final;
	}

	public function setFinal($final) {
		$this->final = (bool)$final;
	}

	public function isAbstract() {
		return $this->abstract;
	}

	public function setAbstract($abstract) {
		$this->abstract = (bool)$abstract;
	}
	
	public function setReturnType($returnType) {
		$this->returnType = $returnType;
	}
	
	public function getReturnType() {
		return $this->returnType;
	}

	public function getMethodCode() {
		return $this->methodCode;
	}

	public function setMethodCode($methodCode) {
		$this->methodCode = $methodCode;
	}

	/**
	 * @return PhpParam []
	 */
	public function getParams() {
		return $this->params;
	}
	
	/**
	 * @return PhpParam
	 */
	public function getFirstParam() {
		if (count($this->params) === 0) return null;
		
		return reset($this->params);
	}

	public function setParams(array $params) {
		$this->params = $params;
	}

	public function addParam(PhpParam $param) {
		$this->params[$param->getName()] = $param;
	}

	public function determineParamTypeNames(PhpType $phpType) {
		foreach ($this->params as $param) {
			$typeName = $param->getTypeName();
			if (null === $typeName || PhprepUtils::isInRootNamespace($typeName) 
					|| null === PhprepUtils::extractNamespace($typeName)) continue;
			
			$param->setTypeName($phpType->extractUse($param->getTypeName()));
		}
	}

	public function __toString() {
		$string = $this->getPrependingString();
		if (!empty($string)) {
			$string = "\t" . $string;
		}
		$string .= "\t";
		if (null !== $this->classifier) {
			$string .= $this->classifier;
		}
		if ($this->static) {
			$string = $this->appendToString($string, PhpKeyword::KEYWORD_STATIC);
		}
		if ($this->final) {
			$string = $this->appendToString($string, PhpKeyword::KEYWORD_FINAL);
		}

		if ($this->abstract) {
			$string = $this->appendToString($string, PhpKeyword::KEYWORD_ABSTRACT);
		}

		$string = $this->appendToString($string, PhpKeyword::KEYWORD_FUNCTION)
				. ' ' . $this->name . '(' . implode(', ', $this->params) . ')'; 
		
		if (null !== $this->returnType) {
			$string .= PhpKeyword::RETURN_TYPE_INDICATOR . ' ' . $this->returnType;
		}
		
		if (!$this->abstract) {
			$string .= ' ' . PhpKeyword::GROUP_STATEMENT_OPEN;
		}

		if (strlen($this->methodCode) > 0) {
			$string .= PHP_EOL . "\t\t" . PhprepUtils::removeTrailingWhiteSpaces(
					PhprepUtils::removeLeadingWhiteSpaces($this->methodCode)) . PHP_EOL;
			
			$string .= "\t";	
		}
		
		return $string . (!$this->abstract ? PhpKeyword::GROUP_STATEMENT_CLOSE : ';') . PHP_EOL;
	}

	private function appendToString($string, $append) {
		if (!empty($string)) {
			$string .= ' ';
		}
		return $string . $append;
	}
}