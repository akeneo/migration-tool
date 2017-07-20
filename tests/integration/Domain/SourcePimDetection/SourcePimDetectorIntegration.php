<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\SourcePimDetection;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\ComposerJson;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\ParametersYml;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimParameters;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfiguration;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePimDetector;
use PHPUnit\Framework\TestCase;
use resources\Akeneo\PimMigration\ResourcesFileLocator;

/**
 * Source Pim Detector Integration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimDetectorIntegration extends TestCase
{
    public function testSimpleCommunityStandardEdition()
    {
        $sourcePimDetector = new SourcePimDetector();

        $stepTwoFolder = ResourcesFileLocator::getStepFolder('step_two_source_pim_detection') . DIRECTORY_SEPARATOR;
        $standardComposerJson = $stepTwoFolder . 'community_standard_composer.json';
        $parametersYaml = $stepTwoFolder . 'parameters.yml';
        $pimParameters = $stepTwoFolder . 'community_pim_parameters.yml';

        $sourcePimConfiguration = new SourcePimConfiguration(
            new ComposerJson($standardComposerJson),
            new ParametersYml($parametersYaml),
            new PimParameters($pimParameters),
            'plop'
        );

        $sourcePim = $sourcePimDetector->detect($sourcePimConfiguration);

        $this->assertEquals($sourcePim->getDatabaseName(), 'akeneo_pim_database_name');
        $this->assertEquals($sourcePim->getMysqlHost(), 'localhost');
        $this->assertEquals($sourcePim->getMysqlPort(), 3306);
        $this->assertEquals($sourcePim->getDatabaseUser(), 'akeneo_pim_user');
        $this->assertEquals($sourcePim->getDatabasePassword(), 'akeneo_pim_password');
        $this->assertEquals($sourcePim->isEnterpriseEdition(), false);
        $this->assertEquals($sourcePim->hasIvb(), false);
        $this->assertEquals($sourcePim->getMongoDbInformation(), null);
        $this->assertEquals($sourcePim->getMongoDatabase(), null);
    }

    public function testEnterpriseStandardEditionMongoIvb()
    {
        $sourcePimDetector = new SourcePimDetector();

        $stepTwoFolder = ResourcesFileLocator::getStepFolder('step_two_source_pim_detection') . DIRECTORY_SEPARATOR;
        $standardComposerJson = $stepTwoFolder . 'enterprise_standard_mongo_ivb_composer.json';
        $parametersYaml = $stepTwoFolder . 'parameters.yml';
        $pimParameters = $stepTwoFolder . 'enterprise_mongo_pim_parameters.yml';

        $sourcePimConfiguration = new SourcePimConfiguration(
            new ComposerJson($standardComposerJson),
            new ParametersYml($parametersYaml),
            new PimParameters($pimParameters),
            'plop'
        );

        $sourcePim = $sourcePimDetector->detect($sourcePimConfiguration);

        $this->assertEquals($sourcePim->getDatabaseName(), 'akeneo_pim_database_name');
        $this->assertEquals($sourcePim->getMysqlHost(), 'localhost');
        $this->assertEquals($sourcePim->getMysqlPort(), 3306);
        $this->assertEquals($sourcePim->getDatabaseUser(), 'akeneo_pim_user');
        $this->assertEquals($sourcePim->getDatabasePassword(), 'akeneo_pim_password');
        $this->assertEquals($sourcePim->isEnterpriseEdition(), true);
        $this->assertEquals($sourcePim->hasIvb(), true);
        $this->assertEquals($sourcePim->getMongoDbInformation(), 'mongodb://localhost:27017');
        $this->assertEquals($sourcePim->getMongoDatabase(), 'your_mongo_database');
    }
}