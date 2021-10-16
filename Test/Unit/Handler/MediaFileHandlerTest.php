<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Handler;

use HiveCommerce\ContentFuzzyfyr\Handler\MediaFileHandler;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class BackupHandlerTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Handler
 */
class MediaFileHandlerTest extends AbstractTest
{
    /**
     * @test
     */
    public function runSuccessfullyWithAlreadyExistingMediaFile()
    {
        $inputFilePath = 'foo/bar/baz.png';
        $expectedMediaFilePath = 'media/baz.png';

        $config = $this->getConfig();

        $mediaDirectory = $this->getMediaDirectory();
        $mediaDirectory->expects(self::once())
            ->method('getAbsolutePath')
            ->with(
                sprintf(
                    '%s/%s',
                    MediaFileHandler::MEDIA_MODULE_BASE_PATH,
                    basename($inputFilePath)
                )
            )
            ->willReturn($expectedMediaFilePath);

        $fileSystem = $this->getFilesystem();
        $fileSystem->expects(self::once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects(self::once())
            ->method('fileExists')
            ->with($expectedMediaFilePath)
            ->willReturn(true);


        $mediaFileHandler = new MediaFileHandler(
            $config,
            $fileSystem,
            $ioFile
        );

        $return = $mediaFileHandler->getMediaCopyOfFile($inputFilePath);
        self::assertEquals($expectedMediaFilePath, $return);
    }

    /**
     * @test
     */
    public function runFailsDueToMissingImageAsset()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not resolve given image path: "foo/bar/baz.png"');

        $inputFilePath = 'foo/bar/baz.png';
        $expectedMediaFilePath = 'media/baz.png';

        $config = $this->getConfig();

        $mediaDirectory = $this->getMediaDirectory();
        $mediaDirectory->expects(self::once())
            ->method('getAbsolutePath')
            ->with(
                sprintf(
                    '%s/%s',
                    MediaFileHandler::MEDIA_MODULE_BASE_PATH,
                    basename($inputFilePath)
                )
            )
            ->willReturn($expectedMediaFilePath);

        $fileSystem = $this->getFilesystem();
        $fileSystem->expects(self::once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects(self::any())
            ->method('fileExists')
            ->willReturnCallback(function ($file) use($expectedMediaFilePath, $inputFilePath): ?bool {
                if ($file === $expectedMediaFilePath) {
                    return false;
                } else if ($file === $inputFilePath) {
                    return false;
                }
                return null;
            });
        $ioFile->expects(self::once())
            ->method('getCleanPath')
            ->with($inputFilePath)
            ->willReturn($inputFilePath);


        $mediaFileHandler = new MediaFileHandler(
            $config,
            $fileSystem,
            $ioFile
        );

        $mediaFileHandler->getMediaCopyOfFile($inputFilePath);
    }

    /**
     * @test
     */
    public function runFailsDueToFailingToCreateMediaSubFolder()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not create media folder: "allindata/content/fuzzfyr"');

        $inputFilePath = 'foo/bar/baz.png';
        $expectedMediaFilePath = 'media/baz.png';

        $config = $this->getConfig();

        $mediaDirectory = $this->getMediaDirectory();
        $mediaDirectory->expects(self::once())
            ->method('getAbsolutePath')
            ->with(
                sprintf(
                    '%s/%s',
                    MediaFileHandler::MEDIA_MODULE_BASE_PATH,
                    basename($inputFilePath)
                )
            )
            ->willReturn($expectedMediaFilePath);
        $mediaDirectory->expects(self::once())
            ->method('create')
            ->with(MediaFileHandler::MEDIA_MODULE_BASE_PATH)
            ->willReturn(false);

        $fileSystem = $this->getFilesystem();
        $fileSystem->expects(self::once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects(self::any())
            ->method('fileExists')
            ->willReturnCallback(function ($file) use($expectedMediaFilePath, $inputFilePath): ?bool {
                if ($file === $expectedMediaFilePath) {
                    return false;
                } else if ($file === $inputFilePath) {
                    return true;
                }
                return null;
            });
        $ioFile->expects(self::once())
            ->method('getCleanPath')
            ->with($inputFilePath)
            ->willReturn($inputFilePath);


        $mediaFileHandler = new MediaFileHandler(
            $config,
            $fileSystem,
            $ioFile
        );

        $mediaFileHandler->getMediaCopyOfFile($inputFilePath);
    }

    /**
     * @test
     */
    public function runFailsDueToFailingToCopyFiles()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not copy media file to: "media/baz.png"');

        $inputFilePath = 'foo/bar/baz.png';
        $expectedMediaFilePath = 'media/baz.png';

        $config = $this->getConfig();

        $mediaDirectory = $this->getMediaDirectory();
        $mediaDirectory->expects(self::once())
            ->method('getAbsolutePath')
            ->with(
                sprintf(
                    '%s/%s',
                    MediaFileHandler::MEDIA_MODULE_BASE_PATH,
                    basename($inputFilePath)
                )
            )
            ->willReturn($expectedMediaFilePath);
        $mediaDirectory->expects(self::once())
            ->method('create')
            ->with(MediaFileHandler::MEDIA_MODULE_BASE_PATH)
            ->willReturn(true);

        $fileSystem = $this->getFilesystem();
        $fileSystem->expects(self::once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects(self::any())
            ->method('fileExists')
            ->willReturnCallback(function ($file) use($expectedMediaFilePath, $inputFilePath): ?bool {
                if ($file === $expectedMediaFilePath) {
                    return false;
                } else if ($file === $inputFilePath) {
                    return true;
                }
                return null;
            });
        $ioFile->expects(self::once())
            ->method('getCleanPath')
            ->with($inputFilePath)
            ->willReturn($inputFilePath);
        $ioFile->expects(self::once())
            ->method('cp')
            ->with($inputFilePath, $expectedMediaFilePath)
            ->willReturn(false);

        $mediaFileHandler = new MediaFileHandler(
            $config,
            $fileSystem,
            $ioFile
        );

        $mediaFileHandler->getMediaCopyOfFile($inputFilePath);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $inputFilePath = 'foo/bar/baz.png';
        $expectedMediaFilePath = 'media/baz.png';

        $config = $this->getConfig();

        $mediaDirectory = $this->getMediaDirectory();
        $mediaDirectory->expects(self::once())
            ->method('getAbsolutePath')
            ->with(
                sprintf(
                    '%s/%s',
                    MediaFileHandler::MEDIA_MODULE_BASE_PATH,
                    basename($inputFilePath)
                )
            )
            ->willReturn($expectedMediaFilePath);
        $mediaDirectory->expects(self::once())
            ->method('create')
            ->with(MediaFileHandler::MEDIA_MODULE_BASE_PATH)
            ->willReturn(true);

        $fileSystem = $this->getFilesystem();
        $fileSystem->expects(self::once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects(self::any())
            ->method('fileExists')
            ->willReturnCallback(function ($file) use($expectedMediaFilePath, $inputFilePath): ?bool {
                if ($file === $expectedMediaFilePath) {
                    return false;
                } else if ($file === $inputFilePath) {
                    return true;
                }
                return null;
            });
        $ioFile->expects(self::once())
            ->method('getCleanPath')
            ->with($inputFilePath)
            ->willReturn($inputFilePath);
        $ioFile->expects(self::once())
            ->method('cp')
            ->with($inputFilePath, $expectedMediaFilePath)
            ->willReturn(true);


        $mediaFileHandler = new MediaFileHandler(
            $config,
            $fileSystem,
            $ioFile
        );

        $return = $mediaFileHandler->getMediaCopyOfFile($inputFilePath);
        self::assertEquals($expectedMediaFilePath, $return);
    }

    /**
     * @return MockObject|Config
     */
    private function getConfig()
    {
        return $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|Filesystem
     */
    private function getFilesystem()
    {
        return $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|File
     */
    private function getFile()
    {
        return $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|WriteInterface
     */
    private function getMediaDirectory()
    {
        return $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
