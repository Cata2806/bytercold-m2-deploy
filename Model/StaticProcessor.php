<?php

namespace ByterCold\Deploy\Model;

use Magento\Deploy\Model\Filesystem as DeployFilesystem;
use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use ByterCold\Deploy\App\View\Asset\PublisherExtended;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;

/**
 * StaticProcessor class
 */
class StaticProcessor
{
    /**
     * @var DeployFilesystem
     */
    private $deployFilesystem;

    /**
     * @var \Magento\Framework\App\View\Deployment\Version\StorageInterface
     */
    private $versionStorage;

    /**
     * @var PackagePool
     */
    private $packagePool;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var PublisherExtended
     */
    private $assetPublisher;

    /**
     * @var FileNameResolver
     */
    private $fileNameResolver;

    /**
     * @param DeployFilesystem $deployFilesystem
     * @param StorageInterface $versionStorage
     * @param PackagePool $packagePool
     * @param AssetRepository $assetRepository
     * @param PublisherExtended $assetPublisher
     * @param FileNameResolver $fileNameResolver
     */
    public function __construct(
        DeployFilesystem $deployFilesystem,
        StorageInterface $versionStorage,
        PackagePool $packagePool,
        AssetRepository $assetRepository,
        PublisherExtended $assetPublisher,
        FileNameResolver $fileNameResolver
    ) {
        $this->deployFilesystem = $deployFilesystem;
        $this->versionStorage = $versionStorage;
        $this->packagePool = $packagePool;
        $this->assetRepository = $assetRepository;
        $this->assetPublisher = $assetPublisher;
        $this->fileNameResolver = $fileNameResolver;
    }

    /**
     * @param string $filePath
     * @param \Symfony\Component\Console\Output\OutputInterface;$output
     * @return void
     */
    public function processStaticFile($filePath, $reloadVersion, $output)
    {
        $output->writeln("<info>Initializing packages for deployment</info>");
        $packages = $this->packagePool->getPackagesForDeployment([
            Options::EXCLUDE_LANGUAGE => ['all'],
            Options::LANGUAGE => ['all'],
            Options::AREA => ['all'],
            Options::EXCLUDE_AREA => ['none'],
            Options::THEME => ['all'],
            Options::EXCLUDE_THEME => ['none']
        ]);
        $output->writeln("<info>Packages loaded successfully for all areas</info>");

        $fileUpdated = false;
        foreach ($packages as $package) {
            if ($package->isVirtual()) {
                continue;
            }
            $package->aggregate();
            $files = $package->getFiles();
            $output->writeln(sprintf("<info>Processing theme: %s</info>", $package->getTheme()));

            foreach ($files as $file) {
                if ($file->getFilePath() === $filePath) {
                    $this->deployFile(
                        $file->getFileName(),
                        [
                            'area' => $package->getArea(),
                            'theme' => $package->getTheme(),
                            'locale' => $package->getLocale(),
                            'module' => $file->getModule(),
                        ]
                    );
                    $fileUpdated = true;
                    $output->writeln(sprintf("<info>Asset processed: %s</info>", $file->getSourcePath()));
                }
            }
        }

        if (!$fileUpdated) {
            $output->writeln(sprintf("<error>Could not find the file %s</error>", $filePath));
            return;
        }
        
        if ($reloadVersion) {
            $this->resetStaticVersion();
            $output->writeln("<info>Static version changed successfully</info>");
        }

        $output->writeln("<info>File deployed successfully</info>");
    }

    /**
     * @param string $fileName
     * @param array $params ['area' =>, 'theme' =>, 'locale' =>, 'module' =>]
     * @return string
     */
    private function deployFile($fileName, array $params = [])
    {
        $params['publish'] = true;
        $asset = $this->assetRepository->createAsset($this->resolveFile($fileName), $params);
        
        $this->assetPublisher->forcePublish($asset);

        return $asset->getPath();
    }

    /**
     * Resolve filename
     *
     * @param string $fileName
     * @return string
     */
    private function resolveFile($fileName)
    {
        $compiledFile = str_replace(
            AssetRepository::FILE_ID_SEPARATOR,
            '/',
            $this->fileNameResolver->resolve($fileName)
        );

        return $compiledFile;
    }

    /**
     * Reset static version file
     * 
     * @return void
     */
    private function resetStaticVersion()
    {
        $result = $this->generateVersion();
        $this->versionStorage->save($result);
    }

    /**
     * Change permissions on static resources
     */
    private function lockStaticResources()
    {
        $this->deployFilesystem->lockStaticResources();    
    }
    
    /**
     * Generate version of static content
     *
     * @return int
     */
    private function generateVersion()
    {
        return time();
    }
}
