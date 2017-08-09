<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemRequirementsNotBootable;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Infrastructure\Command\DestinationPimCommandLauncher;
use Ds\Set;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Install Pim System Requirements through docker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerDestinationPimSystemRequirementsInstaller implements DestinationPimSystemRequirementsInstaller
{
    /** @var DestinationPimCommandLauncher */
    private $destinationPimCommandLauncher;

    public function __construct(DestinationPimCommandLauncher $destinationPimCommandLauncher)
    {
        $this->destinationPimCommandLauncher = $destinationPimCommandLauncher;
    }

    public function install(DestinationPim $destinationPim): void
    {
        $dockerComposeDistFilePath = sprintf(
            '%s%sdocker-compose.yml.dist',
            $destinationPim->getPath(),
            DIRECTORY_SEPARATOR
        );

        $dockerComposeDestinationPath = sprintf(
            '%s%sdocker-compose.yml',
            $destinationPim->getPath(),
            DIRECTORY_SEPARATOR
        );

        $fs = new Filesystem();

        $fs->copy($dockerComposeDistFilePath, $dockerComposeDestinationPath);

        $launchDockerComposeDaemon = new Process('docker-compose up -d', $destinationPim->getPath());

        $launchDockerComposeDaemon->run();

        if (!$this->dockerComposeInfrastructureIsUp($destinationPim->getPath())) {
            throw new DestinationPimSystemRequirementsNotBootable(
                'Docker cannot boot the install system, please check `docker-compose ps` in '.$destinationPim->getPath()
            );
        }

        $this->destinationPimCommandLauncher->runCommand(new ComposerUpdateCommand(), $destinationPim);
        $this->destinationPimCommandLauncher->runCommand(new PrepareRequiredDirectoriesCommand(), $destinationPim);
    }

    protected function dockerComposeInfrastructureIsUp(string $destinationPimPath): bool
    {
        $folderName = basename($destinationPimPath);
        $containerPrefix = str_replace(['-', '_'], '', $folderName);

        $services = [
            $containerPrefix.'_httpd_1',
            $containerPrefix.'_mysql_1',
            $containerPrefix.'_elasticsearch_1',
            $containerPrefix.'_fpm_1',
        ];

        $process = new Process('docker ps --format="{{.Names}}"', $destinationPimPath);
        $process->run();
        $output = $process->getOutput();

        $output = explode(PHP_EOL, $output);

        $servicesNames = new Set($output);

        $set = $servicesNames->filter(function (string $service) {
            return !empty(trim($service));
        });

        return $set->contains(...$services);
    }
}
