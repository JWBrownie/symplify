<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Nette\Rules\ForbiddenNetteInjectOverrideRule\Fixture;

use Symplify\PHPStanRules\Tests\Nette\Rules\ForbiddenNetteInjectOverrideRule\Source\AbstractInjectParentClass;

final class OverrideParentInjectAttribute extends AbstractInjectParentClass
{
    public function __construct(AnotherType $anotherType)
    {
        $this->someAttributeType = $anotherType;
    }
}
