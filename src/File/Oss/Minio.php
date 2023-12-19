<?php

namespace Baogg\File\Oss;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\MultipartUploader;

class Minio
{


    /** @var \Aws\S3\S3Client　*/
    protected static $s3Client;

    /**
     * @return \Aws\S3\S3Client
     */
    protected static function initS3Client()
    {
        if (self::$s3Client == null) {
            self::$s3Client = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1',
                'endpoint' => self::getEndPoint(),
                'use_path_style_endpoint' => true,
                //'debug'   => true,
                'credentials' => [
                    'key'    => self::getKey(),
                    'secret' => self::getSecret(),
                ],
            ]);
        }
        return self::$s3Client;
    }



    public static function getBuckets()
    {
        $s3Client = self::initS3Client();
        $result = $s3Client->listBuckets();
        $names = $result->search('Buckets[].Name');
        return $names;
    }

    public static function createBucket($bucket)
    {
        $s3Client = self::initS3Client();
        $result = $s3Client->createBucket([
            'Bucket' => $bucket,
        ]);
    }

    public static function getBucketAcl($bucket)
    {
        $s3Client = self::initS3Client();
        $result = $s3Client->getBucketAcl([
            'Bucket' => $bucket
        ]);
        return $result;
    }

    /**
     * Upload File
     *
     * @param mixed $file source file path,such as '/path/to/large/file.zip'
     * @param string $object minio key path
     * @return void
     */
    public static function upLoadFile($file, $object, $is_public = false)
    {
        $s3Client = self::initS3Client();
        $bucket = $is_public ? self::getPubBucket():self::getPrivBucket();
        $uploader = new MultipartUploader($s3Client, $file, [
            'bucket' => $bucket,
            'key' => $object,
        ]);

        $result = $uploader->upload();
        return $result;
    }

    /**
     * 生成公开的url
     *
     * @param string $object bucket中的路径
     * @param boolean $is_public
     * @param int $seconds
     * 
     * @return string
     */
    public static function getURL($object, $is_public = false, $seconds = 20 * 60)
    {
        $s3Client = self::initS3Client();
        $bucket = $is_public ?  self::getPubBucket():self::getPrivBucket();

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $object
        ]);

        try {
            // error_log(__FILE__ . __LINE__ . "; seconds={$seconds};  is_public = " . var_export($is_public, true));
            $presignedUrl = $is_public ? $s3Client->getObjectUrl($bucket, $object) : $s3Client->createPresignedRequest($cmd, "+{$seconds} seconds")->getUri();


            // error_log(__FILE__ . __LINE__ . "; presignedUrl = " . var_export($presignedUrl, true));
        } catch (\Exception $e) {
            error_log(__FILE__ . __LINE__ . "; Exception = " . var_export($e, true));
        }

        return $presignedUrl;
    }
    public static function deleteBucket($bucket)
    {
        $s3Client = self::initS3Client();
        $result = $s3Client->deleteBucket([
            'Bucket' => $bucket,
        ]);
        return $result;
    }
    public static function deleteObject($object, $is_public = false)
    {
        $s3Client = self::initS3Client();
        $bucket = $is_public ?  self::getPubBucket():self::getPrivBucket();
        $result = $s3Client->deleteObject([
            'Bucket' => $bucket,
            'Key' => $object,
        ]);

        return $result;
    }

    public static function getEndPoint()
    {
        return \Baogg\File::getSetting('settings.minio.endpoint');
    }

    public static function getKey()
    {
        return \Baogg\File::getSetting('settings.minio.key');
    }

    public static function getSecret()
    {
        return \Baogg\File::getSetting('settings.minio.secret');
    }

    public static function getPubBucket()
    {
        return \Baogg\File::getSetting('settings.minio.pubBucket');
    }

    public static function getPrivBucket()
    {
        return \Baogg\File::getSetting('settings.minio.privBucket');
    }



    /**
     * 检测文件是否存在
     *
     * @param string $object 文件路径
     * @param boolean $is_public
     * @return boolean
     */
    public static function isExists($object, $is_public = false)
    {
        $s3Client = self::initS3Client();
        $bucket = $is_public ?  self::getPubBucket():self::getPrivBucket();

        return $s3Client->doesObjectExistV2($bucket, $object);
    }
}
