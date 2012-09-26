<?php

namespace Alchemy\Phrasea\Border;

use Alchemy\Phrasea\Media\Type\Image;
use Alchemy\Phrasea\Media\Type\Flash;
use Alchemy\Phrasea\Media\Type\Audio;
use Alchemy\Phrasea\Media\Type\Video;
use Alchemy\Phrasea\Media\Type\Document;
use MediaVorus\Media\MediaInterface;
use PHPExiftool\Driver\Tag\MXF\ObjectName;
use PHPExiftool\Driver\Value\Mono;
use PHPExiftool\Driver\Tag\IPTC\Keywords;
use PHPExiftool\Driver\Value\Multi;
use PHPExiftool\Driver\Metadata\Metadata as PHPExiftoolMetadata;
use Alchemy\Phrasea\Border\Attribute\Metadata;

require_once __DIR__ . '/../../../PhraseanetPHPUnitAbstract.class.inc';

class FileTest extends \PhraseanetPHPUnitAbstract
{
    /**
     * @var File
     */
    protected $object;
    protected $filename;
    protected $media;

    /**
     * @covers Alchemy\Phrasea\Border\File::__construct
     */
    public function setUp()
    {
        parent::setUp();
        $this->filename = __DIR__ . '/../../../../tmp/iphone_pic.jpg';
        copy(__DIR__ . '/../../../testfiles/iphone_pic.jpg', $this->filename);

        $this->media = self::$DI['app']['mediavorus']->guess($this->filename);

        $this->object = new File($this->media, self::$DI['collection'], 'originalName.txt');
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::__destruct
     */
    public function tearDown()
    {
        $this->object = null;
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
        parent::tearDown();
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getUUID
     */
    public function testGetUuid()
    {
        $this->assertEquals('4d006e01-bc38-4aac-9a5b-2c90ffe3a8a2', $this->object->getUUID());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getUUID
     */
    public function testNewUuid()
    {
        $file = __DIR__ . '/../../../testfiles/temporay.jpg';

        if (file_exists($file)) {
            unlink($file);
        }

        copy(__DIR__ . '/../../../testfiles/p4logo.jpg', $file);

        $borderFile = new File(self::$DI['app']['mediavorus']->guess($file), self::$DI['collection']);
        $uuid = $borderFile->getUUID(true, false);

        $this->assertTrue(\uuid::is_valid($uuid));
        $this->assertEquals($uuid, $borderFile->getUUID());

        $borderFile = new File(self::$DI['app']['mediavorus']->guess($file), self::$DI['collection']);
        $newuuid = $borderFile->getUUID(true, true);

        $this->assertTrue(\uuid::is_valid($newuuid));
        $this->assertNotEquals($uuid, $newuuid);
        $this->assertEquals($newuuid, $borderFile->getUUID());

        $borderFile = new File(self::$DI['app']['mediavorus']->guess($file), self::$DI['collection']);
        $uuid = $borderFile->getUUID();

        $this->assertTrue(\uuid::is_valid($uuid));
        $this->assertEquals($uuid, $newuuid);
        $this->assertEquals($uuid, $borderFile->getUUID());

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getSha256
     */
    public function testGetSha256()
    {
        $this->assertEquals('a7f3ec01c4c5efcadc639d494d432006f13b28b9a576afaee4d3b7508c4be074', $this->object->getSha256());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getMD5
     */
    public function testGetMD5()
    {
        $this->assertEquals('db0d69df2fc9e5e82e42d174f2bbb62f', $this->object->getMD5());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getFile
     */
    public function testGetFile()
    {
        $this->assertInstanceOf('\\MediaVorus\\File', $this->object->getFile());
        $this->assertEquals(realpath(__DIR__ . '/../../../../tmp/iphone_pic.jpg'), $this->object->getFile()->getRealPath());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getMedia
     */
    public function testGetMedia()
    {
        $this->assertInstanceof('\\MediaVorus\\Media\\Image', $this->object->getMedia());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getCollection
     */
    public function testGetCollection()
    {
        $this->assertSame(self::$DI['collection'], $this->object->getCollection());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getOriginalName
     */
    public function testOriginalName()
    {
        $this->assertSame('originalName.txt', $this->object->getOriginalName());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getOriginalName
     */
    public function testOriginalNameAuto()
    {
        $object = new File(self::$DI['app']['mediavorus']->guess($this->filename), self::$DI['collection']);
        $this->assertSame('iphone_pic.jpg', $object->getOriginalName());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getAttributes
     */
    public function testGetAttributes()
    {
        $this->assertSame(array(), $this->object->getAttributes());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::addAttribute
     */
    public function testAddAttribute()
    {
        $tag = new ObjectName();
        $value = new Mono('Object name');

        $metadata1 = new PHPExiftoolMetadata($tag, $value);
        $attribute1 = new Metadata($metadata1);

        $this->object->addAttribute($attribute1);
        $this->assertSame(array($attribute1), $this->object->getAttributes());

        $tag = new Keywords();
        $value = new Multi(array('Later', 'Alligator'));

        $metadata2 = new PHPExiftoolMetadata($tag, $value);
        $attribute2 = new Metadata($metadata2);

        $this->object->addAttribute($attribute2);
        $this->assertSame(array($attribute1, $attribute2), $this->object->getAttributes());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::buildFromPathfile
     */
    public function testBuildFromPathfile()
    {
        $media = self::$DI['app']['mediavorus']->guess($this->filename);
        $file1 = new File($media, self::$DI['collection']);

        $file2 = File::buildFromPathfile($this->filename, self::$DI['collection'], self::$DI['app']['mediavorus']);

        $this->assertEquals($file1, $file2);

        $media = self::$DI['app']['mediavorus']->guess($this->filename);
        $file3 = new File($media, self::$DI['collection'], 'coco lapin');

        $file4 = File::buildFromPathfile($this->filename, self::$DI['collection'], self::$DI['app']['mediavorus'], 'coco lapin');

        $this->assertEquals($file3, $file4);
        $this->assertNotEquals($file1, $file4);
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::buildFromPathfile
     * @expectedException \InvalidArgumentException
     */
    public function testBuildFromWrongPathfile()
    {
        File::buildFromPathfile('unexistent.file', self::$DI['collection'], self::$DI['app']['mediavorus']);
    }

    protected function getMediaMock($type)
    {
        $mock = $this->getMockBuilder('\\MediaVorus\\Media\\Image')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($type));

        return $mock;
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getType
     */
    public function testGetTypeImage()
    {
        $image = $this->getMediaMock(MediaInterface::TYPE_IMAGE);

        $file = new File($image, self::$DI['collection'], 'hello');

        $this->assertEquals(new Image(), $file->getType());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getType
     */
    public function testGetTypeDocument()
    {
        $document = $this->getMediaMock(MediaInterface::TYPE_DOCUMENT);

        $file = new File($document, self::$DI['collection'], 'hello');

        $this->assertEquals(new Document(), $file->getType());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getType
     */
    public function testGetTypeAudio()
    {
        $audio = $this->getMediaMock(MediaInterface::TYPE_AUDIO);

        $file = new File($audio, self::$DI['collection'], 'hello');

        $this->assertEquals(new Audio(), $file->getType());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getType
     */
    public function testGetTypeVideo()
    {
        $video = $this->getMediaMock(MediaInterface::TYPE_VIDEO);

        $file = new File($video, self::$DI['collection'], 'hello');

        $this->assertEquals(new Video(), $file->getType());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getType
     */
    public function testGetTypeFlash()
    {
        $flash = $this->getMediaMock(MediaInterface::TYPE_FLASH);

        $file = new File($flash, self::$DI['collection'], 'hello');

        $this->assertEquals(new Flash(), $file->getType());
    }

    /**
     * @covers Alchemy\Phrasea\Border\File::getType
     */
    public function testGetTypeNoType()
    {
        $noType = $this->getMediaMock(null);

        $file = new File($noType, self::$DI['collection'], 'hello');

        $this->assertNull($file->getType());
    }

}
