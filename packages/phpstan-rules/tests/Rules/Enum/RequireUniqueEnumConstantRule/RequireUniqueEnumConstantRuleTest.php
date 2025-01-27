<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\Enum\RequireUniqueEnumConstantRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Symplify\PHPStanRules\Rules\Enum\RequireUniqueEnumConstantRule;

/**
 * @extends RuleTestCase<RequireUniqueEnumConstantRule>
 */
final class RequireUniqueEnumConstantRuleTest extends RuleTestCase
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
        $expectedErrorMessage = sprintf(RequireUniqueEnumConstantRule::ERROR_MESSAGE, 'yes');
        yield [__DIR__ . '/Fixture/InvalidEnum.php', [[$expectedErrorMessage, 8]]];
        yield [__DIR__ . '/Fixture/InvalidAnnotationEnum.php', [[$expectedErrorMessage, 8]]];

        yield [__DIR__ . '/Fixture/SkipValidEnum.php', []];
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
        return self::getContainer()->getByType(RequireUniqueEnumConstantRule::class);
    }
}
