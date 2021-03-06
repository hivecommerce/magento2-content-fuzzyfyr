<?php

/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace HiveCommerce\ContentFuzzyfyr\Handler;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class MediaFileHandler
 * @package HiveCommerce\ContentFuzzyfyr\Handler
 */
class MediaFileHandler
{
    /*
     * Path
     */
    const MEDIA_MODULE_BASE_PATH = 'hivecommerce/content/fuzzfyr';

    /**
     * @var Config
     */
    private $mediaConfig;
    /**
     * @var Filesystem
     */
    private $fileSystem;
    /**
     * @var File
     */
    private $ioFile;
    /**
     * @var WriteInterface
     */
    private $mediaDirectory = null;

    /**
     * MediaFileHandler constructor.
     * @param Config $mediaConfig
     * @param Filesystem $fileSystem
     * @param File $ioFile
     */
    public function __construct(Config $mediaConfig, Filesystem $fileSystem, File $ioFile)
    {
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->ioFile = $ioFile;
    }

    /**
     * @param string $filePath
     * @return string
     * @throws FileSystemException
     */
    public function getMediaCopyOfFile(string $filePath): string
    {
        $this->init();

        $mediaFilePath = $this->mediaDirectory->getAbsolutePath(
            sprintf(
                '%s/%s',
                static::MEDIA_MODULE_BASE_PATH,
                basename($filePath)
            )
        );

        // short exit if media file already exists
        if ($this->ioFile->fileExists($mediaFilePath, true)) {
            return $mediaFilePath;
        }

        /*
         * Check on requested file
         */
        $filePath = $this->ioFile->getCleanPath($filePath);
        if (!$this->ioFile->fileExists($filePath, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not resolve given image path: "%s"',
                    $filePath
                )
            );
        }

        /*
         * Create module media folder
         */
        if (!$this->mediaDirectory->create(static::MEDIA_MODULE_BASE_PATH)) {
            throw new \RuntimeException(
                sprintf(
                    'Could not create media folder: "%s"',
                    static::MEDIA_MODULE_BASE_PATH
                )
            );
        }

        /*
         * Copy file
         */
        if (!$this->ioFile->cp($filePath, $mediaFilePath)) {
            throw new \RuntimeException(
                sprintf(
                    'Could not copy media file to: "%s"',
                    $mediaFilePath
                )
            );
        }

        return $mediaFilePath;
    }

    /**
     * @return void
     * @throws FileSystemException
     */
    private function init(): void
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
    }
}
