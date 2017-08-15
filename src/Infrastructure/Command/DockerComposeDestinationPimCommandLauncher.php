<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * Launch command through docker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerComposeDestinationPimCommandLauncher extends AbstractDestinationPimCommandLauncher implements DestinationPimCommandLauncher
{
    /** @var string */
    private $container;

    public function __construct(ProcessLauncher $processLauncher, string $container)
    {
        parent::__construct($processLauncher);

        $this->container = trim($container);
    }

    protected function getStringCommand(Command $command): string
    {
        return escapeshellcmd(sprintf(
            'docker-compose exec %s %s',
            $this->container,
            trim($command->getCommand())
        ));
    }
}
