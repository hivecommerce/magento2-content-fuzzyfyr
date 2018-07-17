<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Handler;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class MediaFileHandler
 * @package AllInData\ContentFuzzyfyr\Handler
 */
class MediaFileHandler
{
    /*
     * Path
     */
    const MEDIA_MODULE_BASE_PATH = 'allindata/content/fuzzfyr';

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
    private $mediaDirectory;

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
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function init()
    {
        if (!$this->mediaDirectory) {
            $this->mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
    }

    /**
     * @param $filePath
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getMediaCopyOfFile($filePath)
    {
        $this->init();

        $mediaFilePath = $this->mediaDirectory->getAbsolutePath(
            sprintf(
                '%s/%s',
                self::MEDIA_MODULE_BASE_PATH,
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
        $this->ioFile->getCleanPath($filePath);
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
        $this->mediaDirectory->create(self::MEDIA_MODULE_BASE_PATH);
        if (!$this->mediaDirectory->create(self::MEDIA_MODULE_BASE_PATH)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not create media folder: "%s"',
                    self::MEDIA_MODULE_BASE_PATH
                )
            );
        }

        /*
         * Copy file
         */
        if (!$this->ioFile->cp($filePath, $mediaFilePath)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not copy media file to: "%s"',
                    $mediaFilePath
                )
            );
        }

        return $mediaFilePath;
    }
}
