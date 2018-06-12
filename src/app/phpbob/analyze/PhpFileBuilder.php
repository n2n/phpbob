<?php
namespace phpbob\analyze;

use phpbob\representation\PhpFile;
use phpbob\PhpStatement;
use phpbob\PhprepUtils;
use n2n\reflection\ArgUtils;
use phpbob\representation\PhpNamespaceElementCreator;
use phpbob\Phpbob;
use phpbob\representation\PhpUse;
use phpbob\StatementGroup;
use phpbob\representation\PhpTypeDef;
use phpbob\representation\PhpClass;
use n2n\util\StringUtils;

class PhpFileBuilder {
	private $phpFile;
	private $currentNamespace;
	private $unprocessedStatements = [];
	
	public function __construct() {
		$this->phpFile = new PhpFile();
	}
	
	public function getPhpFile() {
		return $this->phpFile;
	}
	
	public function processPhpStatement(PhpStatement $phpStatement) {
		if (!PhprepUtils::isTypeStatement($phpStatement)) {
			if (PhprepUtils::isNamespaceStatement($phpStatement)) {
				$this->createPhpNamespace($phpStatement);
			} elseif(PhprepUtils::isUseStatement($phpStatement)) {
				$this->createPhpUse($phpStatement);
			} else {
				$this->unprocessedStatements[] = $phpStatement;
			}
			continue;
		}
		
		if (PhprepUtils::isClassStatement($phpStatement)) {
			$phpType = PhprepUtils::createPhpClass($phpStatement, $namespaceStatement,
					$useStatements, $statementsBefore, $as);
		} elseif (PhprepUtils::isInterfaceStatement($phpStatement)) {
			$phpType = PhprepUtils::createPhpInterface($phpStatement, $namespaceStatement,
					$useStatements, $statementsBefore);
		} else {
			$phpType = PhprepUtils::createPhpTrait($phpStatement, $namespaceStatement,
					$useStatements, $statementsBefore);
		}
		
		$statementsBefore = array();
	}
	
	private function createPhpClass(PhpStatement $phpStatement) {
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup
				&& self::isClassStatement($phpStatement));
		
		$codeParts = self::determineCodeParts($phpStatement);
		$abstract = false;
		$final = false;
		
		while (true) {
			$codePart = strtolower(array_shift($codeParts));
			if ($codePart == Phpbob::KEYWORD_CLASS) break;
			
			switch ($codePart) {
				case Phpbob::KEYWORD_FINAL:
					$final = true;
					break;
				case Phpbob::KEYWORD_ABSTRACT:
					$abstract = true;
					break;
				case false:
					throw new \InvalidArgumentException('missing class Keyword');
			}
		}
		
		$phpClass = $this->determinePhpNamespaceElementCreator()->createPhpClass(array_shift($codeParts));
		
		$phpClass->setAbstract($abstract);
		$phpClass->setFinal($final);
		
		$phpClass->setPrependingCode($this->determinePrependingCode($phpStatement));
		
		$inExtendsClause = false;
		$inImplementsClause = false;
		
		foreach ($codeParts as $codePart) {
			if ($inImplementsClause) {
				$codePart = str_replace(',', '', $codePart);
				$phpClass->addInterfacePhpTypeDef($this->buildTypeDef($codePart));
				continue;
			}
			
			if ($inExtendsClause) {
				$phpClass->setSuperClassTypeDef($this->buildTypeDef($codePart));
				$inExtendsClause = false;
				continue;
			}
			
			switch (strtolower($codePart)) {
				case Phpbob::KEYWORD_EXTENDS:
					$inExtendsClause = true;
					break;
				case Phpbob::KEYWORD_IMPLEMENTS:
					$inImplementsClause = true;
					break;
				default:
					throw new PhpSourceAnalyzingException('Invalid part in class statement: ' . $codePart);
			}
		}
		
		foreach ($phpStatement->getPhpStatements() as $childPhpStatement) {
			if (PhprepUtils::isConstStatement($childPhpStatement)) {
				$this->applyPhpConst($phpClass, $childPhpStatement);
				continue;
			} 
			
			if (PhprepUtils::isPropertyStatement($childPhpStatement)) {
				$this->applyPhpProperty($phpClass, $childPhpStatement);
				continue;
			} 
			
			if (PhprepUtils::isMethodStatement($childPhpStatement)) {
				if (PhprepUtils::isAnnotationStatement($childPhpStatement)) {
					$phpClass->setAnnotationSet($this->applyAnnotationSet($phpClass, $childPhpStatement));
				} else {
					$phpClass->addMethod(self::createPhpMethod($childPhpStatement));
				}
				continue;
			} 
			
			if (self::isTraitUseStatement($childPhpStatement)) {
				$phpClass->appendTraitNames(self::extractTraitNames($childPhpStatement));
				continue;
			}
			
			throw new PhpSourceAnalyzingException('Invalid PHP Statement: ' . $childPhpStatement);
		}
		
		return $phpClass;
	}
	
	private function determinePrependingCode(PhpStatement $phpStatement) {
		$prependingCode = implode('', $this->unprocessedStatements) . PhprepUtils::createPrependingCode($phpStatement);
		$this->unprocessedStatements = [];
		if (empty($prependingCode)) return null;
		
		return $prependingCode;
	}
	
	private function createPhpNamespace(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhprepUtils::isNamespaceStatement($phpStatement));
		$codeParts = PhprepUtils::determineCodeParts($phpStatement);
		
		$this->currentNamespace = $this->phpFile->createPhpNamespace($codeParts[1])
				->setPrependingCode($this->determinePrependingCode($phpStatement));
	}
	
	private function createPhpUse(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhprepUtils::isUseStatement($phpStatement));
		$codeParts = PhprepUtils::determineCodeParts($phpStatement);
		
		$typeName = $codeParts[1];
		$type = null;
		$alias = null;
		if (count($codeParts) > 2) {
			switch ($codeParts[2]) {
				case PhpUse::TYPE_CONST:
				case PhpUse::TYPE_FUNCTION:
					$type = $codeParts[2];
					if (count($codeParts) > 4) {
						$alias = $codeParts[4];
					}
					break;
				case Phpbob::KEYWORD_AS:
					if (count($codeParts) > 3) {
						$alias = $codeParts[3];
					}
			}
		}
		
		$this->determinePhpNamespaceElementCreator()->createPhpUse($typeName, $alias, $type);
	}
	
	/**
	 * @return PhpNamespaceElementCreator
	 */
	private function determinePhpNamespaceElementCreator() {
		if (null !== $this->currentNamespace) return $this->currentNamespace;
		
		return $this->phpFile;
	}
	
	private function buildTypeDef(string $localName) {
		$nec = $this->determinePhpNamespaceElementCreator();
		try {
			return new PhpTypeDef($localName, $nec->determineTypeName($localName));
		} catch (\phpbob\representation\ex\DuplicateElementException $e) {
			throw new PhpSourceAnalyzingException('Invalid local name: ' . $localName, null, $e);	
		}
	}
	
	private function applyPhpConst(PhpClass $phpClass, PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhprepUtils::isConstStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement, true);
		// due to the isPropertyStatement method it s ensured that there are at least 2 Parts
		$phpConst = $phpClass->createPhpConst($codeParts[1]);
		if (count($codeParts) > 2) {
			$phpConst->setValue($codeParts[2]);
		}
		
		$phpConst->setPrependingCode(PhprepUtils::createPrependingCode($phpStatement));
	}
	
	
	private function applyPhpProperty(PhpClass $phpClass, PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhprepUtils::isPropertyStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement, true);
		
		$classifier = null;
		$name = null;
		$value = null;
		$static = false;
		
		foreach ($codeParts as $codePart) {
			if (null === $classifier) {
				$classifier = $codePart;
				continue;
			}
			
			if (null === $name) {
				if (strtolower($codePart) == Phpbob::KEYWORD_STATIC) {
					$static = true;
				} else {
					$name = PhprepUtils::purifyPropertyName($codePart);
				}
				continue;
			}
			
			if (null === $value) {
				$value = $codePart;
				continue;
			}
			
			$value .= ' ' . $codePart;
		}
		
		$phpClass->createPhpProperty($classifier, $name)->setValue($value)
				->setStatic($static)->setPrependingCode(self::createPrependingCode($phpStatement));
	}
	
	private function applyAnnotationSet(PhpClass $phpClass, PhpStatement $phpStatement) {
		$annoAnalyzer = new PhpAnnoAnalyzer();
		$annoAnalyzer->analyze($phpStatement, $phpClass);
	}
	
	
	private static function determineCodeParts(PhpStatement $phpStatement, bool $replaceAssignment = false) {
		$str = trim(str_replace(Phpbob::SINGLE_STATEMENT_STOP, '', implode(' ', $phpStatement->getCodeLines())));
		if ($replaceAssignment) {
			$str = str_replace(Phpbob::ASSIGNMENT, '', $str);
		}
		
		return self::determineCodePartsForString($str);
	}
	
	private static function determineCodePartsForString($string) {
		if (StringUtils::isEmpty($string)) return array();
		
		$codeParts = array();
		$currentCodePart = null;
		$stringDelimiter = null;
		
		foreach (str_split($string) as $token) {
			if (null !== $stringDelimiter) {
				$currentCodePart .= $token;
				if ($token == $stringDelimiter) {
					$stringDelimiter = null;
				}
				continue;
			}
			
			if ($token == '"' || $token == "'") {
				$currentCodePart .= $token;
				$stringDelimiter = $token;
				continue;
			}
			
			if (StringUtils::isEmpty($token)) {
				if (null !== $currentCodePart) {
					$codeParts[] = $currentCodePart;
					$currentCodePart = null;
				}
				continue;
			}
			
			$currentCodePart .= $token;
		}
		if (null !== $currentCodePart) {
			$codeParts[] = $currentCodePart;
		}
		
		return $codeParts;
	}
}