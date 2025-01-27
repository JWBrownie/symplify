<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Nette\Rules\NoTemplateMagicAssignInControlRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Symplify\PHPStanRules\Nette\Rules\NoTemplateMagicAssignInControlRule;

/**
 * @extends RuleTestCase<NoTemplateMagicAssignInControlRule>
 */
final class NoTemplateMagicAssignInControlRuleTest extends RuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param mixed[] $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/MagicTemplateAssign.php', [
            [NoTemplateMagicAssignInControlRule::ERROR_MESSAGE, 13],
        ]];

        yield [__DIR__ . '/Fixture/SkipPresenterTemplateAssign.php', []];
        yield [__DIR__ . '/Fixture/SkipControlApply.php', []];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/config/configured_rule.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(NoTemplateMagicAssignInControlRule::class);
    }
}
