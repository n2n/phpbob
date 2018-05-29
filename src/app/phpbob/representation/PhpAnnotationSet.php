<?php
namespace phpbob\representation;

use phpbob\PhpKeyword;
use phpbob\PhprepUtils;

class PhpAnnotationSet {
	use PrependingCodeTrait;
	
	const DEFAULT_ANNO_INIT_VARIABLE_NAME = '$ai';
	const ANNO_METHOD_SIGNATURE = 'private static function _annos(AnnoInit ';
	
	private $phpClass;
	private $aiVariableName = '$ai';
	/**
	 * @var PhpPropertyAnno[]
	 */
	private $propertyAnnos = array();
	/**
	 * @var PhpMethodAnno[]
	 */
	private $methodAnnos = array();
	private $classAnno;
	
	public function __construct(PhpClass $phpClass) {
		$this->phpClass = $phpClass;
	}
	
	public function getAiVariableName() {
		return $this->aiVariableName;
	}

	public function setAiVariableName($aiVariableName) {
		$this->aiVariableName = $aiVariableName;
	}

	public function getPropertyAnnos() {
		return $this->propertyAnnos;
	}
	
	public function getPropertyAnnoForProperty(PhpProperty $property) {
		if (!$this->hasPropertyAnnoForProperty($property)) return null;
		return $this->propertyAnnos[$property->getName()];
	}
	
	public function hasPropertyAnnoForProperty(PhpProperty $property) {
		return isset($this->propertyAnnos[$property->getName()]);
	}
	
	public function hasPropertyAnnoParam(string $propertyName, string $typeName) {
		if (!isset($this->propertyAnnos[$propertyName])) return false;
		
		return $this->propertyAnnos[$propertyName]->hasParam($typeName);
	}
	
	public function removePropertyAnno(string $propertyName) {
		if (!isset($this->propertyAnnos[$propertyName])) return;
		unset($this->propertyAnnos[$propertyName]);
	}
	
	public function addPropertyAnno(PhpPropertyAnno $propertyAnno) {
		$propertyName = $propertyAnno->getPropertyName();
		if (isset($this->propertyAnnos[$propertyName])) {
			$this->propertyAnnos[$propertyName]->mergeWith($propertyAnno); 
		} else {
			$this->propertyAnnos[$propertyName] = $propertyAnno;
		}
		return $this;
	}

	public function getMethodAnnos() {
		return $this->methodAnnos;
	}

	public function setMethodAnnos(array $methodAnnos) {
		$this->methodAnnos = $methodAnnos;
	}
	
	public function removeMethodAnno(string $methodName) {
		if (!isset($this->methodAnnos[$methodName])) return;
		unset($this->methodAnnos[$methodName]);
	}
	
	public function addMethodAnno(PhpMethodAnno $methodAnno) {
		$methodName = $methodAnno->getMethodName();
		if (isset($this->methodAnnos[$methodName])) {
			$this->methodAnnos[$methodName]->mergeWith($methodAnno);
		} else {
			$this->methodAnnos[$methodName] = $methodAnno;
		}
		return $this;
	}
	/**
	 * @return PhpClassAnno
	 */
	public function getClassAnno() {
		return $this->classAnno;
	}
	/**
	 * @return PhpClassAnno
	 */
	public function getOrCreateClassAnno() {
		if (null === $this->classAnno) {
			$this->classAnno = new PhpClassAnno();
		}
		
		return $this->classAnno;
	}

	public function setClassAnno(PhpClassAnno $classAnno = null) {
		$this->classAnno = $classAnno;
	}
	
	public function isEmpty() {
		return null === $this->classAnno && empty($this->propertyAnnos) && empty($this->methodAnnos); 
	}
	
	public function applyTypeNames() {
		foreach ($this->propertyAnnos as $anno) {
			$this->checkTypeNames($anno);
		}
		foreach ($this->methodAnnos as $anno) {
			$this->checkTypeNames($anno);
		}
		if (null !== $this->classAnno) {
			$this->checkTypeNames($this->classAnno);
		}
	}
	
	public function __toString() {
		if ($this->isEmpty()) return $this->getPrependingString();
		$string = "\t" . self::ANNO_METHOD_SIGNATURE . $this->aiVariableName . ') ' 
				. PhpKeyword::GROUP_STATEMENT_OPEN . PHP_EOL;
		if (null !== $this->classAnno) {
			$string .= "\t\t" . $this->aiVariableName . '->c(' . $this->classAnno->getAnnotationString() . ')' 
					. PhpKeyword::SINGLE_STATEMENT_STOP . PHP_EOL; 
		}
		
		foreach ($this->methodAnnos as $methodAnno) {
			$string .= "\t\t" . $this->aiVariableName . '->m(\'' . $methodAnno->getMethodName() . '\', ' 
					. $methodAnno->getAnnotationString() . ')' 
					. PhpKeyword::SINGLE_STATEMENT_STOP . PHP_EOL; 
		}
		
		foreach ($this->propertyAnnos as $propertyAnno) {
			$string .= "\t\t" . $this->aiVariableName . '->p(\'' . $propertyAnno->getPropertyName() . '\', ' 
					. $propertyAnno->getAnnotationString() . ')' 
					. PhpKeyword::SINGLE_STATEMENT_STOP . PHP_EOL; 
		}
		
		return $string . "\t" . PhpKeyword::GROUP_STATEMENT_CLOSE . PHP_EOL;
	}
	
	public static function createAnnoInitUse() {
		return new PhpUse('n2n\reflection\annotation\AnnoInit');
	}
	
	private function checkTypeNames(PhpAnno $phpAnno) {
		foreach ($phpAnno->getParams() as $annoParam) {
			$typeName = $annoParam->getTypeName();
			if (null === PhprepUtils::extractNamespace($typeName)) return;
			$this->phpClass->addUse(new PhpUse($typeName));
			$annoParam->setTypeName(PhprepUtils::extractClassName($typeName));
		}
	} 
}