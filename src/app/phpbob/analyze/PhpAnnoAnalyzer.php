<?php
namespace phpbob\analyze;

use phpbob\PhpStatement;
use phpbob\PhprepUtils;
use phpbob\StatementGroup;
use phpbob\SingleStatement;
use phpbob\Phpbob;
use phpbob\representation\PhpMethodAnno;
use phpbob\representation\PhpClassAnno;
use phpbob\representation\PhpPropertyAnno;
use phpbob\representation\PhpClass;
use n2n\reflection\annotation\AnnotationSet;
use n2n\reflection\ArgUtils;
use n2n\reflection\CastUtils;

class PhpAnnoAnalyzer {
	
	private $variableDefinitions = array();
	private $paramAnalyzer;
	private $phpClass;
	
	public function __construct() {
		$this->paramAnalyzer = new PhpAnnoParamAnalyzer();
	}
	
	public function analyze(PhpStatement $phpStatement, PhpClass $phpClass, 
			AnnotationSet $as = null) {
		if (!($phpStatement instanceof StatementGroup && PhprepUtils::isAnnotationStatement($phpStatement))) {
			throw new PhpAnnotationSourceAnalyzingException('invalid annotation-statement:' . 
					$phpStatement);
		}
		$this->initialize($phpClass);
		$this->determineVarialbeDefinitions($phpStatement);
		
		$phpAnnotationSet = $phpClass->getAnnotationSet();
		$aiVariableName = null;
		$matches = array();
		if (preg_match('/private\s+static\s+function\s+_annos\s*\(\s*(n2n\\reflection\\annotation)?AnnoInit\s+(\$\S+)\)/',
				implode(' ', $phpStatement->getCodeLines()), $matches) && (count($matches) === 2 || count($matches) === 3)) {
			$aiVariableName = end($matches);
			$phpAnnotationSet->setAiVariableName($aiVariableName);
		} else {
			throw new \InvalidArgumentException('Invalid Annotation Mehtod signature');
		}
		
		$prependingCode = '';
		foreach ($phpStatement->getPhpStatements() as $childPhpStatement) {
			if ($this->isClassAnnotation($childPhpStatement, $aiVariableName)) {
				$newClassAnno = $this->createPhpClassAnno($childPhpStatement, $aiVariableName, $prependingCode);
				if (null !== ($classAnno = $phpAnnotationSet->getClassAnno())) {
					$classAnno->mergeWith($newClassAnno);
				} else {
					$phpAnnotationSet->setClassAnno($newClassAnno);
				}
				$prependingCode = '';
				continue;
			} elseif ($this->isMethodAnnotation($childPhpStatement, $aiVariableName)) {
				$phpAnnotationSet->addMethodAnno(
						$this->createPhpMethodAnno($childPhpStatement, $aiVariableName, $prependingCode));
				$prependingCode = '';
				continue;
			} elseif ($this->isPropertyAnnotation($childPhpStatement, $aiVariableName)) {
				$phpAnnotationSet->addPropertyAnno($this->createPhpPropertyAnno($childPhpStatement, 
						$aiVariableName, $prependingCode));
				$prependingCode = '';
				continue;
			}
			
			$prependingCode .= $childPhpStatement;
		}
		if ($as !== null) {
			$this->processPropertyAnnos($as, $phpAnnotationSet->getPropertyAnnos());
			$this->processMethodAnnos($as, $phpAnnotationSet->getMethodAnnos());
			$this->processClassAnno($as, $phpAnnotationSet->getClassAnno());
		}
		
		return $phpAnnotationSet;
	}
	
	private function processPropertyAnnos(AnnotationSet $as, array $propertyAnnos) {
		ArgUtils::valArray($propertyAnnos, PhpPropertyAnno::class);
		$numPropertyAnnoParams = 0;
		foreach ($propertyAnnos as $propertyAnno) {
			CastUtils::assertTrue($propertyAnno instanceof PhpPropertyAnno);
			
			foreach ($propertyAnno->getParams() as $param) {
				$annotation = $as->getPropertyAnnotation($propertyAnno->getPropertyName(), $param->getTypeName());
				if (null === $annotation) {
					throw new PhpAnnotationSourceAnalyzingException('Invalid Annotation Set: Annotation ' .
							$param->getTypeName() . ' for Property ' . $propertyAnno->getPropertyName() . ' missing');
				}
				$param->setAnnotation($annotation);
				$numPropertyAnnoParams++;
			}
		}
		
		if ($numPropertyAnnoParams === count($as->getAllPropertyAnnotations())) return;
		
		throw new PhpAnnotationSourceAnalyzingException('Structure of Annotation statement invalid:
				number of property annotations does not match.');
	}
	
	private function processMethodAnnos(AnnotationSet $as, array $methodAnnos) {
		ArgUtils::valArray($methodAnnos, PhpMethodAnno::class);
		$numMethodAnnoParams = 0;
		foreach ($methodAnnos as $methodAnno) {
			foreach ($methodAnno->getParams() as $param) {
				$annotation = $as->getMethodAnnotation($methodAnno->getMethodName(), $param->getTypeName());
				if (null === $annotation) {
					throw new PhpAnnotationSourceAnalyzingException('Invalid Annotation Set: Annotation ' .
							$param->getTypeName() . ' for Method ' . $methodAnno->getMethodName() . ' missing');
				}
				$param->setAnnotation($annotation);
				$numMethodAnnoParams++;
			}
		}
		
		if ($numMethodAnnoParams === count($as->getAllMethodAnnotations())) return;
		
		throw new PhpAnnotationSourceAnalyzingException('Structure of Annotation statement invalid:
				number of method annotations does not match.');
	}
	
	private function processClassAnno(AnnotationSet $as, PhpClassAnno $classAnno = null) {
		if (null === $classAnno) {
			if (count($as->getClassAnnotations()) === 0) return;
		} else {
			$numClassAnnoParams = 0;
			foreach ($classAnno->getParams() as $param) {
				$annotation = $as->getClassAnnotation($param->getTypeName());
				if (null === $annotation) {
					throw new PhpAnnotationSourceAnalyzingException('Invalid Annotation Set: Annotation ' .
							$param->getTypeName() . ' for Class missing');
				}
				$param->setAnnotation($annotation);
				$numClassAnnoParams++;
			}
			
			if ($numClassAnnoParams === count($as->getClassAnnotations())) return;
		}
		
		throw new PhpAnnotationSourceAnalyzingException('Structure of Annotation statement invalid:
				number of class annotations does not match.');
	}
	
	private function initialize(PhpClass $phpClass) {
		$this->variableDefinitions = array();
		$this->phpClass = $phpClass;
	}
	
	private function determineVarialbeDefinitions(StatementGroup $statementGroup) {
		foreach ($statementGroup->getPhpStatements() as $phpStatement) {
			if (!$phpStatement instanceof SingleStatement) {
				throw new PhpSourceAnalyzingException('only single statements are allowed in annotation statements. Given statement: '
						. $phpStatement->__toString());
			}
			
			$matches = array();
			if (!preg_match('/\s*(\$\S+)\s*=\s*(\s*' . preg_quote(Phpbob::KEYWORD_NEW). '\s+.*);/', 
					$phpStatement->getCode(), $matches) || count($matches) !== 3) continue;

			$this->variableDefinitions[$matches[1]] = $this->paramAnalyzer->createNewClassAnnoParam($matches[2]);
		}
	}

	private function isClassAnnotation(PhpStatement $phpStatement, $aiVariableName) {
		return $phpStatement instanceof SingleStatement
				&& preg_match('/' . preg_quote($aiVariableName) .'-\>c/', (string) $phpStatement);
	}
	
	private function isMethodAnnotation(PhpStatement $phpStatement, $aiVariableName) {
		return $phpStatement instanceof SingleStatement
				&& preg_match('/' . preg_quote($aiVariableName) .'-\>m/', (string) $phpStatement);
	}
	
	private function isPropertyAnnotation(PhpStatement $phpStatement, $aiVariableName) {
		return $phpStatement instanceof SingleStatement
				&& preg_match('/' . preg_quote($aiVariableName) .'-\>p/', (string) $phpStatement);
	}

	private function createPhpClassAnno(PhpStatement $phpStatement, $aiVariableName, $prependingCode = null) {
		$matches = array();
		if (!preg_match('/' . preg_quote($aiVariableName) . '->c\s*\(\s*(.*)\s*\)\s*;/',
			implode(' ', $phpStatement->getCodeLines()), $matches) || count($matches) !== 2) {
			throw new PhpAnnotationSourceAnalyzingException('Invalid Class Annotation statement' . $phpStatement);
		}
		return new PhpClassAnno($this->createAnnoParamsFromString($matches[1]),
				$this->createPrependingCode($phpStatement, $prependingCode));
	}
	
	private function createPhpMethodAnno(PhpStatement $phpStatement, $aiVariableName, $prependingCode = null) {
		$matches = array();
		if (!preg_match('/' . preg_quote($aiVariableName) . '->m\s*\(\s*\'([^\']*)\'\s*,\s*(.*)\s*\)\s*;/',
				implode(' ', $phpStatement->getCodeLines()), $matches) || count($matches) !== 3) {
			throw new PhpAnnotationSourceAnalyzingException('Invalid Method Annotation statement: ' . $phpStatement);
		}
		return new PhpMethodAnno($matches[1], $this->createAnnoParamsFromString($matches[2]),
				$this->createPrependingCode($phpStatement, $prependingCode));
	}
	
	private function createPhpPropertyAnno(PhpStatement $phpStatement, $aiVariableName, $prependingCode = null) {
		$matches = array();
		if (!preg_match('/' . preg_quote($aiVariableName) . '->p\s*\(\s*\'([^\']*)\'\s*,\s*(.*)\s*\)\s*;/',
				implode(' ', $phpStatement->getCodeLines()), $matches) || count($matches) !== 3) {
			throw new PhpAnnotationSourceAnalyzingException('Invalid Property Annotation statement: ' . $phpStatement);
		}

		return new PhpPropertyAnno($matches[1], $this->createAnnoParamsFromString($matches[2]),
				$this->createPrependingCode($phpStatement, $prependingCode));
	}
	
	private function createPrependingCode(PhpStatement $phpStatement, $additonalPrependingCode = null) {
		return $additonalPrependingCode . implode(PHP_EOL, $phpStatement->getNonCodeLines());
	}
	
	private function createAnnoParamsFromString($paramString) {
		$typedParams = array();
		foreach ($this->paramAnalyzer->analyze($paramString, $this->variableDefinitions) as $annoParam) {
			$typeName = $this->phpClass->determineTypeName($annoParam->getTypeName());
			$annoParam->setTypeName($typeName);
			$typedParams[$typeName] = $annoParam;
		}

		return $typedParams;
	}
}