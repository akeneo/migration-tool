<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\UnixCommand;
use Akeneo\PimMigration\Domain\Command\UnixCommandResult;
use Akeneo\PimMigration\Domain\Command\UnsuccessfulCommandException;
use Akeneo\PimMigration\Domain\Pim\AbstractPim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Run a SymfonyProcess locally.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalConsole extends AbstractConsole implements Console
{
    public function execute(UnixCommand $command, AbstractPim $pim, PimConnection $connection): UnixCommandResult
    {
        $commandToLaunch = $this->getProcessedCommand($command, $pim);
        $process = new Process($commandToLaunch, '');

        $process->enableOutput();
        $process->setTimeout(2 * 3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $authorizedExitCodes = [
                129, // Hangup
                130, // Interrupt
            ];
            if (!in_array($e->getProcess()->getExitCode(), $authorizedExitCodes)) {
                throw new UnsuccessfulCommandException($process->getErrorOutput(), $e->getCode(), $e);
            }
        }

        return new UnixCommandResult($process->getExitCode(), $process->getOutput());
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof Localhost;
    }
}