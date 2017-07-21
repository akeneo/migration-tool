<?php

namespace spec\Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\ComposerJson;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\ParametersYml;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimParameters;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfiguration;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurator;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Spec for SourcePimConfigurator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimConfiguratorSpec extends ObjectBehavior
{
    public function let(FileFetcher $fetcher)
    {
        $this->beConstructedWith($fetcher);

        $fs = new Filesystem();
        $fs->copy(
            ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath(),
            ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath()
        );
        $fs->copy(
            ResourcesFileLocator::getStepOneAbsoluteParametersYamlLocalPath(),
            ResourcesFileLocator::getAbsoluteParametersYamlDestinationPath()
        );
        $fs->copy(
            ResourcesFileLocator::getStepOneAbsolutePimParametersLocalPath(),
            ResourcesFileLocator::getAbsolutePimParametersDestinationPath()
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SourcePimConfigurator::class);
    }

    public function it_returns_the_good_configuration($fetcher)
    {
        $localComposerJsonPath = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();
        $localParameterYmlPath = ResourcesFileLocator::getStepOneAbsoluteParametersYamlLocalPath();
        $localPimParametersPath = ResourcesFileLocator::getStepOneAbsolutePimParametersLocalPath();

        $pimServerInfo = new PimServerInformation($localComposerJsonPath, 'nanou-migration');

        $destinationComposerJsonPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();
        $destinationParametersYmlPath = ResourcesFileLocator::getAbsoluteParametersYamlDestinationPath();
        $destinationPimParametersPath = ResourcesFileLocator::getAbsolutePimParametersDestinationPath();

        $fetcher->fetch($localComposerJsonPath)->willReturn($destinationComposerJsonPath);
        $fetcher->fetch($localParameterYmlPath)->willReturn($destinationParametersYmlPath);
        $fetcher->fetch($localPimParametersPath)->willReturn($destinationPimParametersPath);

        $sourcePimConfiguration = new SourcePimConfiguration(
            new ComposerJson($destinationComposerJsonPath),
            new ParametersYml($destinationParametersYmlPath),
            new PimParameters($destinationPimParametersPath),
            null,
            'nanou-migration'
        );

        $this->configure($pimServerInfo)->shouldBeASourcePimConfigurationLike($sourcePimConfiguration);
    }

    public function getMatchers()
    {
        return [
            'beASourcePimConfigurationLike' => function (SourcePimConfiguration $result, SourcePimConfiguration $expected) {
                return (
                    $result->getComposerJson()->getPath() === $expected->getComposerJson()->getPath() &&
                    $result->getParametersYml()->getPath() === $expected->getParametersYml()->getPath() &&
                    $result->getPimParameters()->getPath() === $expected->getPimParameters()->getPath()
                );
            }
        ];
    }

    public function letGo()
    {
        $fs = new Filesystem();
        $fs->remove(ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath());
        $fs->remove(ResourcesFileLocator::getAbsoluteParametersYamlDestinationPath());
        $fs->remove(ResourcesFileLocator::getAbsolutePimParametersDestinationPath());
    }
}
