<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use SplFileInfo;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\PreferredClassRule\PreferredClassRuleTest
 */
final class PreferredClassRule extends AbstractSymplifyRule implements ConfigurableRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Instead of "%s" class/interface use "%s"';

    /**
     * @param string[] $oldToPreferredClasses
     */
    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private array $oldToPreferredClasses
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class, Name::class, Class_::class, StaticCall::class, Instanceof_::class];
    }

    /**
     * @param New_|Name|Class_|StaticCall|Instanceof_ $node
     * @return string[]
     */
    public function process(Node $node, Scope $scope): array
    {
        if ($node instanceof New_) {
            return $this->processNew($node);
        }

        if ($node instanceof Class_) {
            return $this->processClass($node);
        }

        if ($node instanceof StaticCall || $node instanceof Instanceof_) {
            return $this->processExprWithClass($node);
        }

        return $this->processClassName($node->toString());
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return new SplFileInfo('...');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symplify\SmartFileSystem\SmartFileInfo;

class SomeClass
{
    public function run()
    {
        return new SmartFileInfo('...');
    }
}
CODE_SAMPLE
                ,
                [
                    'oldToPreferredClasses' => [
                        SplFileInfo::class => SmartFileInfo::class,
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    private function processNew(New_ $new): array
    {
        $className = $this->simpleNameResolver->getName($new->class);
        if ($className === null) {
            return [];
        }

        return $this->processClassName($className);
    }

    /**
     * @return string[]
     */
    private function processClass(Class_ $class): array
    {
        if ($class->extends === null) {
            return [];
        }

        $className = $this->simpleNameResolver->getName($class);

        $parentClass = $class->extends->toString();
        foreach ($this->oldToPreferredClasses as $oldClass => $prefferedClass) {
            if ($parentClass !== $oldClass) {
                continue;
            }

            // check special case, when new class is actually the one we use
            if ($prefferedClass === $className) {
                return [];
            }

            $errorMessage = sprintf(self::ERROR_MESSAGE, $oldClass, $prefferedClass);
            return [$errorMessage];
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function processClassName(string $className): array
    {
        foreach ($this->oldToPreferredClasses as $oldClass => $prefferedClass) {
            if ($className !== $oldClass) {
                continue;
            }

            $errorMessage = sprintf(self::ERROR_MESSAGE, $oldClass, $prefferedClass);
            return [$errorMessage];
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function processExprWithClass(StaticCall|Instanceof_ $node): array
    {
        if ($node->class instanceof Expr) {
            return [];
        }

        $className = (string) $node->class;
        return $this->processClassName($className);
    }
}
