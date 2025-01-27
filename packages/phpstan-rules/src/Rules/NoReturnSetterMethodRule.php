<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\NoReturnSetterMethodRule\NoReturnSetterMethodRuleTest
 */
final class NoReturnSetterMethodRule implements Rule, DocumentedRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Setter method cannot return anything, only set value';

    /**
     * @var string
     * @see https://regex101.com/r/IIvg8L/1
     */
    private const SETTER_START_REGEX = '#^set[A-Z]#';

    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private NodeFinder $nodeFinder
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return [];
        }

        if (! $classReflection->isClass()) {
            return [];
        }

        $classMethodName = $this->simpleNameResolver->getName($node);
        if ($classMethodName === null) {
            return [];
        }

        if ($classMethodName === 'setUp') {
            return [];
        }

        if (! Strings::match($classMethodName, self::SETTER_START_REGEX)) {
            return [];
        }

        if (! $this->hasReturnReturnFunctionLike($node)) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private $name;

    public function setName(string $name): int
    {
        return 1000;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private $name;

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function hasReturnReturnFunctionLike(ClassMethod $classMethod): bool
    {
        /** @var Return_[] $returns */
        $returns = $this->nodeFinder->findInstanceOf($classMethod, Return_::class);
        foreach ($returns as $return) {
            if ($return->expr !== null) {
                return true;
            }
        }

        $yield = $this->nodeFinder->findFirstInstanceOf($classMethod, Yield_::class);
        return $yield instanceof Yield_;
    }
}
