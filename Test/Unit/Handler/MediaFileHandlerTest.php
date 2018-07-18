<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Test\Unit\Handler;

use AllInData\ContentFuzzyfyr\Handler\MediaFileHandler;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class BackupHandlerTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Handler
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
        $mediaDirectory->expects($this->once())
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
        $fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects($this->at(0))
            ->method('fileExists')
            ->with($expectedMediaFilePath)
            ->willReturn(true);


        $mediaFileHandler = new MediaFileHandler(
            $config,
            $fileSystem,
            $ioFile
        );

        $return = $mediaFileHandler->getMediaCopyOfFile($inputFilePath);
        $this->assertEquals($expectedMediaFilePath, $return);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not resolve given image path: "foo/bar/baz.png"
     */
    public function runFailsDueToMissingImageAsset()
    {
        $inputFilePath = 'foo/bar/baz.png';
        $expectedMediaFilePath = 'media/baz.png';

        $config = $this->getConfig();

        $mediaDirectory = $this->getMediaDirectory();
        $mediaDirectory->expects($this->once())
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
        $fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects($this->at(0))
            ->method('fileExists')
            ->with($expectedMediaFilePath)
            ->willReturn(false);
        $ioFile->expects($this->at(1))
            ->method('getCleanPath')
            ->with($inputFilePath)
            ->willReturn($inputFilePath);
        $ioFile->expects($this->at(2))
            ->method('fileExists')
            ->with($inputFilePath)
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
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not create media folder: "allindata/content/fuzzfyr"
     */
    public function runFailsDueToFailingToCreateMediaSubFolder()
    {
        $inputFilePath = 'foo/bar/baz.png';
        $expectedMediaFilePath = 'media/baz.png';

        $config = $this->getConfig();

        $mediaDirectory = $this->getMediaDirectory();
        $mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with(
                sprintf(
                    '%s/%s',
                    MediaFileHandler::MEDIA_MODULE_BASE_PATH,
                    basename($inputFilePath)
                )
            )
            ->willReturn($expectedMediaFilePath);
        $mediaDirectory->expects($this->once())
            ->method('create')
            ->with(MediaFileHandler::MEDIA_MODULE_BASE_PATH)
            ->willReturn(false);

        $fileSystem = $this->getFilesystem();
        $fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects($this->at(0))
            ->method('fileExists')
            ->with($expectedMediaFilePath)
            ->willReturn(false);
        $ioFile->expects($this->at(1))
            ->method('getCleanPath')
            ->with($inputFilePath)
            ->willReturn($inputFilePath);
        $ioFile->expects($this->at(2))
            ->method('fileExists')
            ->with($inputFilePath)
            ->willReturn(true);


        $mediaFileHandler = new MediaFileHandler(
            $config,
            $fileSystem,
            $ioFile
        );

        $mediaFileHandler->getMediaCopyOfFile($inputFilePath);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not copy media file to: "media/baz.png"
     */
    public function runFailsDueToFailingToCopyFiles()
    {
        $inputFilePath = 'foo/bar/baz.png';
        $expectedMediaFilePath = 'media/baz.png';

        $config = $this->getConfig();

        $mediaDirectory = $this->getMediaDirectory();
        $mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with(
                sprintf(
                    '%s/%s',
                    MediaFileHandler::MEDIA_MODULE_BASE_PATH,
                    basename($inputFilePath)
                )
            )
            ->willReturn($expectedMediaFilePath);
        $mediaDirectory->expects($this->once())
            ->method('create')
            ->with(MediaFileHandler::MEDIA_MODULE_BASE_PATH)
            ->willReturn(true);

        $fileSystem = $this->getFilesystem();
        $fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects($this->at(0))
            ->method('fileExists')
            ->with($expectedMediaFilePath)
            ->willReturn(false);
        $ioFile->expects($this->at(1))
            ->method('getCleanPath')
            ->with($inputFilePath)
            ->willReturn($inputFilePath);
        $ioFile->expects($this->at(2))
            ->method('fileExists')
            ->with($inputFilePath)
            ->willReturn(true);
        $ioFile->expects($this->at(3))
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
        $mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with(
                sprintf(
                    '%s/%s',
                    MediaFileHandler::MEDIA_MODULE_BASE_PATH,
                    basename($inputFilePath)
                )
            )
            ->willReturn($expectedMediaFilePath);
        $mediaDirectory->expects($this->once())
            ->method('create')
            ->with(MediaFileHandler::MEDIA_MODULE_BASE_PATH)
            ->willReturn(true);

        $fileSystem = $this->getFilesystem();
        $fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectory);

        $ioFile = $this->getFile();
        $ioFile->expects($this->at(0))
            ->method('fileExists')
            ->with($expectedMediaFilePath)
            ->willReturn(false);
        $ioFile->expects($this->at(1))
            ->method('getCleanPath')
            ->with($inputFilePath)
            ->willReturn($inputFilePath);
        $ioFile->expects($this->at(2))
            ->method('fileExists')
            ->with($inputFilePath)
            ->willReturn(true);
        $ioFile->expects($this->at(3))
            ->method('cp')
            ->with($inputFilePath, $expectedMediaFilePath)
            ->willReturn(true);


        $mediaFileHandler = new MediaFileHandler(
            $config,
            $fileSystem,
            $ioFile
        );

        $return = $mediaFileHandler->getMediaCopyOfFile($inputFilePath);
        $this->assertEquals($expectedMediaFilePath, $return);
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