<?php

declare(strict_types=1);

namespace Symplify\SymfonyStaticDumper\Application;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SymfonyStaticDumper\Controller\ControllerDumper;
use Symplify\SymfonyStaticDumper\FileSystem\AssetsCopier;

/**
 * @see \Symplify\SymfonyStaticDumper\Tests\Application\SymfonyStaticDumperApplicationTest
 */
final class SymfonyStaticDumperApplication
{
    public function __construct(
        private ControllerDumper $controllerDumper,
        private SymfonyStyle $symfonyStyle,
        private AssetsCopier $assetsCopier
    ) {
    }

    public function run(string $publicDirectory, string $outputDirectory, bool $wdpOnly = false): void
    {
        $this->controllerDumper->dump($outputDirectory, $wdpOnly);

        $message = sprintf('Files generated to "%s"', $outputDirectory);
        $this->symfonyStyle->success($message);

        $this->assetsCopier->copyAssets($publicDirectory, $outputDirectory . '/digicard');
        $this->symfonyStyle->success('Assets copied');
    }
}
