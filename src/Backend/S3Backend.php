<?php

namespace Tomaj\Image\Backend;

use Aws\Common\Aws;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;

/**
 * Class S3Backend
 *
 * Backedn pre image service ktory uklada obrazky do Amazon S3
 *
 * @package Tomaj\Image\Backend
 */
class S3Backend implements BackendInterface
{
    /** @var \Aws\Common\Aws */
    private $aws;

    /** @var \Aws\S3\S3Client */
    private $s3;

    /** @var  string */
    private $bucket;

    /** @var  string */
    private $host;

    /** @var  string */
    private $accesKeyId;

    public function __construct($accessKeyId, $secretAccessKey, $bucket, $region, $host)
    {
        $this->bucket = $bucket;
        $this->host = $host;
        $this->accesKeyId = $accessKeyId;

        $this->aws = Aws::factory(array(
            'key' => $accessKeyId,
            'secret' => $secretAccessKey,
            'region' => $region,
        ));
        $this->s3 = $this->aws->get('s3');
    }

    public function save($sourceFile, $originalName, $path, $thumbs = array())
    {
        $targetFile = $path . DIRECTORY_SEPARATOR . $originalName;

        $this->s3->putObject(array(
            'Bucket' => $this->bucket,
            'Key'    => $targetFile,
            'Body'   => fopen($sourceFile, 'r'),
            'ACL'    => 'public-read',
        ));

        foreach ($thumbs as $thumb => $thumbPath) {
            if (!file_exists($thumbPath)) {
                throw new \Nette\IOException("Thumb doesn't exists '$thumbPath'");
            }

            $targetPath = $path . DIRECTORY_SEPARATOR . $thumb . '_' . $originalName;
            $this->s3->putObject(array(
                'Bucket' => $this->bucket,
                'Key'    => $targetPath,
                'Body'   => fopen($thumbPath, 'r'),
                'ACL'    => 'public-read',
            ));
        }

        return $path . DIRECTORY_SEPARATOR . $originalName;
    }

    public function url($identifier, $thumb = null)
    {
        if ($thumb == null) {
            return 'https://' . $this->host . '/' . $identifier;
        } else {
            return 'https://' . $this->host . '/' . $this->getThumbPath($identifier, $thumb);
        }
    }

    public function localFile($identifier)
    {
        $filePath = tempnam(sys_get_temp_dir(), Random::generate(12));
        $this->s3->getObject(array(
            'Bucket'  => $this->bucket,
            'Key'     => $identifier,
            'SaveAs'  => $filePath
        ));
        return $filePath;
    }

    public function saveThumb($thumbPath, $identifier, $thumb)
    {
        $result = $this->s3->putObject(array(
            'Bucket' => $this->bucket,
            'Key'    => $this->getThumbPath($identifier, $thumb),
            'Body'   => fopen($thumbPath, 'r'),
            'ACL'    => 'public-read',
        ));
        FileSystem::delete($thumbPath);
        return $result;
    }

    private function getThumbPath($identifier, $type)
    {
        $parts = explode('/', $identifier);
        $last = array_pop($parts);
        $last = $type . '_' . $last;
        array_push($parts, $last);
        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}
