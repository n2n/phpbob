<?php
namespace phpbob;

use n2n\util\StringUtils;
use n2n\reflection\ArgUtils;
use phpbob\representation\PhpProperty;
use phpbob\representation\PhpConst;
use phpbob\representation\PhpMethod;
use phpbob\representation\PhpParam;
use phpbob\representation\PhpNamespace;
use phpbob\representation\PhpUse;
use phpbob\representation\PhpClass;
use n2n\reflection\annotation\AnnotationSet;
use phpbob\analyze\PhpAnnoAnalyzer;
use phpbob\representation\PhpInterface;
use phpbob\representation\PhpTrait;
use n2n\util\ex\NotYetImplementedException;
use phpbob\analyze\PhpSourceAnalyzingException;

class PhprepUtils {
	const NAMESPACE_SEPERATOR = '\\';
	const PHP_FILE_EXTENSION = '.php';
	
	public static function typeNameToPath($typeName) {
		return str_replace(self::NAMESPACE_SEPERATOR, DIRECTORY_SEPARATOR, trim($typeName, self::NAMESPACE_SEPERATOR)) 
				. self::PHP_FILE_EXTENSION;
	}
	
	public static function extractClassName($typeName) {
		if (false === $pos = mb_strrpos($typeName, self::NAMESPACE_SEPERATOR)) {
			return $typeName;
		}
		
		return mb_substr($typeName, $pos + 1); 
	}
	
	public static function isInRootNamespace($typeName) {
		return mb_strrpos($typeName, self::NAMESPACE_SEPERATOR) === 0;
	}
	
	public static function extractNamespace($typeName) {
		$lastPos = strrpos($typeName, '\\');
		if (false === $lastPos) return null;
		
		return mb_substr($typeName, 0, $lastPos);
	}
	
	public static function extractTypeNames($string) {
		$typeNames = array();
		foreach (preg_split('/(\s+|\||,|::)/', $string, null, PREG_SPLIT_NO_EMPTY) as $possibleTypeName) {
			if (false === mb_strpos($possibleTypeName, self::NAMESPACE_SEPERATOR)) continue;
			$typeNames[$possibleTypeName] = preg_replace('/^\\\\/', '', $possibleTypeName);
		}
		
		return $typeNames;
	}
	
	public static function isClassStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return preg_match('/^(' . preg_quote(PhpKeyword::KEYWORD_FINAL) .  '\s+)?(' 
				. preg_quote(PhpKeyword::KEYWORD_ABSTRACT) . '\s+)?'
				. preg_quote(PhpKeyword::KEYWORD_CLASS). '/i', ltrim($phpStatement->getCode()));
	}
	
	public static function isTypeStatement(PhpStatement $phpStatement) {
		return self::isClassStatement($phpStatement) || self::isInterfaceStatement($phpStatement) 
				|| self::isTraitStatement($phpStatement);
	}
	
	public static function isInterfaceStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return StringUtils::startsWith(PhpKeyword::KEYWORD_INTERFACE, 
				ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isTraitStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return StringUtils::startsWith(PhpKeyword::KEYWORD_TRAIT, 
				ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isPropertyStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement 
				&& preg_match('/(' . preg_quote(PhpKeyword::CLASSIFIER_PRIVATE) . 
						'|' . preg_quote(PhpKeyword::CLASSIFIER_PROTECTED) .
						'|' . preg_quote(PhpKeyword::CLASSIFIER_PUBLIC) . ')\s+(' 
						. preg_quote(PhpKeyword::KEYWORD_STATIC) . '\s+)?' . 
						preg_quote(PhpKeyword::VARIABLE_PREFIX) . '/i', $phpStatement->getCode());
	}
	
	public static function isConstStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement 
				&& StringUtils::startsWith(PhpKeyword::KEYWORD_CONST, ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isAnnotationStatement(PhpStatement $phpStatement) {
		return self::isMethodStatement($phpStatement) 
				&& preg_match('/private\s+static\s+function\s+_annos.*\(.*\)/i', 
						$phpStatement->getCode());
	}
	
	public static function isMethodStatement(PhpStatement $phpStatement) {
		return !!preg_match('/' . preg_quote(PhpKeyword::KEYWORD_FUNCTION) 
				. '.*\(.*\)/i', $phpStatement->getCode());
	}
	
	public static function isNamespaceStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement 
				&& preg_match('/^\s*' . preg_quote(PhpKeyword::KEYWORD_NAMESPACE) . '\s+/i', $phpStatement->getCode());
	}
	
	public static function isUseStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement 
				&& StringUtils::startsWith(PhpKeyword::KEYWORD_USE, ltrim(strtolower(implode(' ', $phpStatement->getCodeLines()))));
	}
	
	public static function isTraitUseStatement(PhpStatement $phpStatement) {
		return StringUtils::startsWith(PhpKeyword::KEYWORD_USE, ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isString($value) {
		return StringUtils::startsWith(PhpKeyword::STRING_LITERAL_SEPERATOR, $value) 
				|| StringUtils::startsWith(PhpKeyword::STRING_LITERAL_ALTERNATIVE_SEPERATOR, $value);
	}
	
	public static function createPhpClass(PhpStatement $phpStatement, 
			PhpStatement $namespaceStatement = null, array $useStatements = null, 
					array $statmentsBefore = null, AnnotationSet $as = null) {
		
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup 
				&& self::isClassStatement($phpStatement));
		
		
		$codeParts = self::determineCodeParts($phpStatement);
		$isAbstract = false;
		$isFinal = false;
		
		while (true) {
			$codePart = strtolower(array_shift($codeParts));
			if ($codePart == PhpKeyword::KEYWORD_CLASS) break;
			
			switch ($codePart) {
				case PhpKeyword::KEYWORD_FINAL:
					$isFinal = true;
					break;
				case PhpKeyword::KEYWORD_ABSTRACT:
					$isAbstract = true;
					break;
				case false:
					throw new \InvalidArgumentException('missing class Keyword');
			}
		}
		
		$phpClass = new PhpClass(array_shift($codeParts));
		$phpClass->setAbstract($isAbstract);
		$phpClass->setFinal($isFinal);

		$phpClass->setPrependingCode(implode('', (array) $statmentsBefore) . 
		    (string) self::createPrependingCode($phpStatement));
		
		if (null !== $namespaceStatement) {
			$phpClass->setNamespace(self::createPhpNamespace($namespaceStatement));
		}
		
		$inExtendsClause = false;
		$inImplementsClause = false;
		
		foreach ($codeParts as $codePart) {
			if ($inImplementsClause) {
				$codePart = str_replace(',', '', $codePart);
				$phpClass->addInterfaceName($codePart);
				continue;
			}
			
			if ($inExtendsClause) {
				$phpClass->setSuperClassName($codePart);
				$inExtendsClause = false;
				continue;
			}
			
			switch (strtolower($codePart)) {
				case PhpKeyword::KEYWORD_EXTENDS:
					$inExtendsClause = true;
					break;
				case PhpKeyword::KEYWORD_IMPLEMENTS:
					$inImplementsClause = true;
					break;
			}
		}

		foreach ($useStatements as $useStatement) {
			$phpClass->addUse(self::createPhpUse($useStatement));
		}

		foreach ($phpStatement->getPhpStatements() as $childPhpStatement) {
			if (self::isConstStatement($childPhpStatement)) {
				$phpClass->addConstant(self::createPhpConst($childPhpStatement));
				continue;
			} elseif (self::isPropertyStatement($childPhpStatement)) {
				$phpClass->addProperty(self::createPhpProperty($childPhpStatement));
				continue;
			} elseif (self::isMethodStatement($childPhpStatement)) {
				if (self::isAnnotationStatement($childPhpStatement) && null !== $as) {
					$phpClass->setAnnotationSet(self::applyAnnotationSet($phpClass, $childPhpStatement, $as));
				} else {
					$phpClass->addMethod(self::createPhpMethod($childPhpStatement));
				}
				continue;
			} elseif (self::isTraitUseStatement($childPhpStatement)) {
				$phpClass->appendTraitNames(self::extractTraitNames($childPhpStatement));
				continue;
			}
			
			throw new PhpSourceAnalyzingException('Invalid PHP Statement: ' . $childPhpStatement);
		}
		
		return $phpClass;
	}
	
	public static function createPhpInterface(PhpStatement $phpStatement,
			PhpStatement $namespaceStatement = null, array $useStatements = null,
			array $statmentsBefore = null) {
	
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup 
				&& self::isInterfaceStatement($phpStatement));
	
		
		$codeParts = self::determineCodeParts($phpStatement);
		//shift "interface"
		array_shift($codeParts);
		
		$interfaceName = array_shift($codeParts);
		
		$phpInterface = new PhpInterface($interfaceName);
		$phpInterface->setPrependingCode(implode('', (array) $statmentsBefore) . 
		    self::createPrependingCode($phpStatement));


		if (null !== $namespaceStatement) {
			$phpInterface->setNamespace(self::createPhpNamespace($namespaceStatement));
		}
	
		$inExtendsClause = false;
	
		foreach ($codeParts as $codePart) {
			
			if ($inExtendsClause) {
				$phpInterface->addInterfaceName($codePart);
				continue;
			}
				
			switch (strtolower($codePart)) {
				case PhpKeyword::KEYWORD_EXTENDS:
					$inExtendsClause = true;
					continue;
			}
		}
	
		foreach ($useStatements as $useStatement) {
			$phpInterface->addUse(self::createPhpUse($useStatement));
		}
	
		foreach ($phpStatement->getPhpStatements() as $childPhpStatement) {
			if (self::isConstStatement($childPhpStatement)) {
				$phpInterface->addConstant(self::createPhpConst($childPhpStatement));
				continue;
			} elseif (self::isMethodStatement($childPhpStatement)) {
				$phpInterface->addMethod(self::createPhpMethod($childPhpStatement, true));
				continue;
			}
	
			throw new NotYetImplementedException();
		}
	
		return $phpInterface;
	}
	

	public static function createPhpTrait(PhpStatement $phpStatement,
			PhpStatement $namespaceStatement = null, array $useStatements = null,
			array $statmentsBefore = null) {
	
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup && 
				self::isTraitStatement($phpStatement));
	
		$codeParts = self::determineCodeParts($phpStatement);
		//shift "trait"
		array_shift($codeParts);
	
		$traitName = array_shift($codeParts);
		$phpTrait = new PhpTrait($traitName);

		$phpTrait->setPrependingCode(implode('', (array) $statmentsBefore) . 
		    (string) self::createPrependingCode($phpStatement));
		
		if (null !== $namespaceStatement) {
			$phpTrait->setNamespace(self::createPhpNamespace($namespaceStatement));
		}
	
		foreach ($useStatements as $useStatement) {
			$phpTrait->addUse(self::createPhpUse($useStatement));
		}
	
		foreach ($phpStatement->getPhpStatements() as $childPhpStatement) {
			
			if (self::isConstStatement($childPhpStatement)) {
				$phpTrait->addConstant(self::createPhpConst($childPhpStatement));
				continue;
			} elseif (self::isPropertyStatement($childPhpStatement)) {
				$phpTrait->addProperty(self::createPhpProperty($childPhpStatement));
				continue;
			} elseif (self::isMethodStatement($childPhpStatement)) {
				$phpTrait->addMethod(self::createPhpMethod($childPhpStatement));
				continue;
			} elseif (self::isTraitUseStatement($childPhpStatement)) {
				$phpTrait->appendTraitNames(self::extractTraitNames($childPhpStatement));
				continue;
			}

			throw new NotYetImplementedException();
		}
		return $phpTrait;
	}
	
	public static function extractTraitNames(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(self::isTraitUseStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement);
		//shift use 
		array_shift($codeParts);
		
		$traitNames = array();
		
		foreach ($codeParts as $codePart) {
			foreach (array_filter(explode(',', $codePart)) as $traitName) {
				$traitNames[$traitName] = $traitName;
			}
		}
		
		return $traitNames;
	}
	
	public static function createPhpProperty(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(self::isPropertyStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement, true);
		
		$classifier = null;
		$name = null;
		$value = null;
		$isStatic = false;
		
		foreach ($codeParts as $codePart) {
			if (null === $classifier) {
				$classifier = $codePart;
				continue;
			}
			
			if (null === $name) {
				if (strtolower($codePart) == PhpKeyword::KEYWORD_STATIC) {
					$isStatic = true;
				} else {
					$name = self::purifyPropertyName($codePart);
				}
				
				continue;
			}
			
			if (null === $value) {
				$value = $codePart;
				continue;
			} else {
				$value .= ' ' . $codePart;
			}
		}
		
		$phpProperty = new PhpProperty($classifier, $name, $value, 
				self::createPrependingCode($phpStatement));
		$phpProperty->setStatic($isStatic);
		
		return $phpProperty;
	}
	
	public static function createPhpConst(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(self::isConstStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement, true);
		// due to the isPropertyStatement method it s ensured that there are at least 2 Parts
		$const = new PhpConst($codeParts[1], (count($codeParts) > 2) ? $codeParts[2] : null, 
				self::createPrependingCode($phpStatement));
		return $const;
	}
	
	public static function createPhpMethod(PhpStatement $phpStatement, $abstract = false) {
		ArgUtils::assertTrue(self::isMethodStatement($phpStatement));
		$parts = preg_split('/[\(:]/', trim($phpStatement->getCode()), 3);
		if (count($parts) > 3) {
			throw new \InvalidArgumentException();
		}

		$signaturePart = $parts[0];
		
		$parameterSignaturPart = preg_replace('/\)((?!\)).)*$/', '', $parts[1]);
		
		$returnType = null;
		if (count($parts) > 2) {
			$returnType = preg_replace('/;$/', '', trim($parts[2]));
		}
		
		$classifier = null;
		$final = false;
		$static = false;
		$methodName = null;
		
		foreach (self::explodeByWhiteSpaces($signaturePart) as $part) {
			if (null !== $methodName) break;
			switch (strtolower($part)) {
				case PhpKeyword::KEYWORD_FUNCTION:
					break;
				case PhpKeyword::CLASSIFIER_PRIVATE:
				case PhpKeyword::CLASSIFIER_PROTECTED:
				case PhpKeyword::CLASSIFIER_PUBLIC:
					$classifier = $part;
					break;
				case PhpKeyword::KEYWORD_ABSTRACT:
					$abstract = true;
					break;
				case PhpKeyword::KEYWORD_FINAL:
					$final = true;
					break;
				case PhpKeyword::KEYWORD_STATIC:
					$static = true;
					break;
				default:
					$methodName = $part;
			}
		}

		$params = array();
		
		foreach (preg_split('/\s*,\s*/', $parameterSignaturPart) as $parameter) {
			$parameterParts = self::determineCodePartsForString(str_replace('=', '', $parameter));
			
			if (count($parameterParts) > 3) {
				throw new \InvalidArgumentException('Invalid Number of Parameter Parts in Parameter: ' . $parameter . ' in Method :' . $phpStatement );
			}
			
			$parameterName = null;
			$typeName = null;
			$value = null;
			$splat = false;
			
			foreach ($parameterParts as $parameterPart) {
				if (StringUtils::startsWith(PhpKeyword::SPLAT_INDICATOR, $parameterPart)) {
					$splat = true;
					if (StringUtils::endsWith(PhpKeyword::SPLAT_INDICATOR, $parameterPart)) continue;
					
					$parameterPart = substr($parameterPart, strlen(PhpKeyword::SPLAT_INDICATOR));
				}
				if (StringUtils::startsWith(PhpKeyword::VARIABLE_PREFIX, $parameterPart)) {
					$parameterName = $parameterPart;
					continue;
				}
				if (null === $parameterName) {
					$typeName = $parameterPart;
				} else {
					$value = $parameterPart;
				}
			}
			
			if (null === $parameterName) continue;
			$params[] = new PhpParam(self::purifyPropertyName($parameterName), $value, $typeName, $splat);
		}
		
		$phpMethod = new PhpMethod($methodName, $params, $classifier);
		$phpMethod->setAbstract($abstract);
		
		if ($abstract) {
			ArgUtils::assertTrue($phpStatement instanceof SingleStatement);
		} else {
			ArgUtils::assertTrue($phpStatement instanceof StatementGroup);
			$phpMethod->setMethodCode($phpStatement->getStatementsString());
		}
		$phpMethod->setFinal($final);
		$phpMethod->setPrependingCode(self::createPrependingCode($phpStatement));
		$phpMethod->setStatic($static);
		$phpMethod->setReturnType($returnType);
		
		return $phpMethod;
	}
	
	public static function applyAnnotationSet(PhpClass $phpClass, PhpStatement $phpStatement, AnnotationSet $as) {
		ArgUtils::assertTrue(self::isAnnotationStatement($phpStatement));
		$annoAnalyzer = new PhpAnnoAnalyzer();
		return $annoAnalyzer->analyze($phpStatement, $phpClass, $as);
	}
	
	public static function createPhpNamespace(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(self::isNamespaceStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement);
		
		return new PhpNamespace($codeParts[1], self::createPrependingCode($phpStatement));
	}
	
	public static function createPhpUse(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(self::isUseStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement);
		
		return new PhpUse($codeParts[1], self::createPrependingCode($phpStatement));
	}
	
	public static function determinSetterMethodName($propertyName) {
		return 'set' . ucfirst((string) $propertyName);
	}

	public static function determineGetterMethodName($propertyName, $boolean = false) {
		return (($boolean) ? 'is' : 'get') . ucfirst((string) $propertyName);
	}
	
	private static function determineCodeParts(PhpStatement $phpStatement, bool $replaceAssignment = false) {
		$str = trim(str_replace(PhpKeyword::SINGLE_STATEMENT_STOP, '', implode(' ', $phpStatement->getCodeLines())));
		if ($replaceAssignment) {
			$str = str_replace(PhpKeyword::ASSIGNMENT, '', $str);
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
	
	public static function purifyPropertyName($propertyName) {
		return str_replace(PhpKeyword::VARIABLE_PREFIX, '', $propertyName);
	}
	
	public static function removeLeadingWhiteSpaces($string) {
		return preg_replace('/^\s*/', '', (string) $string);
	}
	
	public static function removeTrailingWhiteSpaces($string) {
		return preg_replace('/\s*$/', '', (string) $string);
	}
	
	private static function explodeByWhiteSpaces($string) {
		return preg_split('/\s+/', $string, null, PREG_SPLIT_NO_EMPTY);
	}
	
	private static function createPrependingCode(PhpStatement $phpStatement) {
		return implode(PHP_EOL, $phpStatement->getNonCodeLines());
	}
	
	private static function isClassifier($s) {
		return preg_match('/^(private|protected|public)$/i');
	}
}