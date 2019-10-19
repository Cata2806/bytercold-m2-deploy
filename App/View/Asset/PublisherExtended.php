<?php

namespace ByterCold\Deploy\App\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\View\Asset;
use Magento\Framework\App\View\Asset\LocalInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\View\Asset\MaterializationStrategy\Factory as MaterializationStrategyFactory;

/**
 * PublisherExtended class
 */
class PublisherExtended extends \Magento\Framework\App\View\Asset\Publisher
{
    /**
     * @var MaterializationStrategy\Factory
     */
    private $materializationStrategyFactory;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * @param Filesystem $filesystem
     * @param MaterializationStrategyFactory $materializationStrategyFactory
     * @param WriteFactory $writeFactory
     */
    public function __construct(
        Filesystem $filesystem,
        MaterializationStrategyFactory $materializationStrategyFactory,
        WriteFactory $writeFactory
    ) {
        $this->materializationStrategyFactory = $materializationStrategyFactory;
        $this->writeFactory = $writeFactory;
        parent::__construct($filesystem, $materializationStrategyFactory, $writeFactory);
    }

    /**
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function forcePublish(Asset\LocalInterface $asset)
    {
        return $this->publishAsset($asset);
    }

    /**
     * Publish the asset
     *
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    private function publishAsset(Asset\LocalInterface $asset)
    {
        $targetDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $fullSource = $asset->getSourceFile();
        $source = basename($fullSource);
        $sourceDir = $this->writeFactory->create(dirname($fullSource));
        $destination = $asset->getPath();
        $strategy = $this->materializationStrategyFactory->create($asset);
        return $strategy->publishFile($sourceDir, $targetDir, $source, $destination);
    }
}
