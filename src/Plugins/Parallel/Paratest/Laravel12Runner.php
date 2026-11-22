<?php

declare(strict_types=1);

namespace Pest\Plugins\Parallel\Paratest;

use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Testing\ParallelRunner;
use LogicException;
use ParaTest\Options;
use ParaTest\RunnerInterface;
use PHPUnit\Event\Facade;
use PHPUnit\TextUI\Configuration\PhpHandler;

final class Laravel12Runner extends ParallelRunner
{
    public function execute(): int
    {
        $options = $this->options;
        $runner = $this->runner;

        if (! $options instanceof Options || ! $runner instanceof RunnerInterface) {
            throw new LogicException('Laravel 12 parallel testing requires the current ParaTest runner.');
        }

        (new PhpHandler(Facade::emitter()))->handle($options->configuration->php());

        $this->forEachProcess(static function (): void {
            ParallelTesting::callSetUpProcessCallbacks();
        });

        try {
            $exitCode = $runner->run();
        } finally {
            $this->forEachProcess(static function (): void {
                ParallelTesting::callTearDownProcessCallbacks();
            });
        }

        return $exitCode;
    }
}
