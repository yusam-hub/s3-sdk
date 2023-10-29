<?php

namespace YusamHub\S3Sdk;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

//todo: https://min.io/docs/minio/linux/reference/minio-mc-admin.html
class ClientS3Sdk
{
    protected bool $isDebugging = false;
    protected string $bucketName;
    protected array $args = [];
    protected array $logs = [];
    protected S3Client $s3Client;

    public function __construct(array $config)
    {
        if (!isset($config['isDebugging'])) {
            throw new \RuntimeException("isDebugging not exists in config");
        }
        if (!isset($config['bucketName'])) {
            throw new \RuntimeException("bucketName not exists in config");
        }
        if (!isset($config['args'])) {
            throw new \RuntimeException("args not exists in config");
        }

        foreach($config as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
        $this->s3Client = new S3Client($this->args);
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getLogsAsString(): string
    {
        return implode(PHP_EOL, $this->logs);
    }

    public function logDebug(string $message, array $context = []): void
    {
        if (!$this->isDebugging) return;
        $this->logs[] = sprintf("%s%s", $message, (!empty($context) ? ' ' : '') . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @return string
     */
    public function getBucketName(): string
    {
        return $this->bucketName;
    }

    /**
     * @param string $bucketName
     */
    public function setBucketName(string $bucketName): void
    {
        $this->bucketName = $bucketName;
    }

    /**
     * @param string $path
     * @param string $body
     * @return bool
     */
    public function putObject(string $path, string $body): bool
    {
        try {
            $args = [
                'Bucket' => $this->getBucketName(),
                'Key' => $path,
                'Body' => $body,
            ];
            $this->logDebug('s3.putObject', $args);

            $awsResult = $this->s3Client->putObject($args);

            if (isset($awsResult["@metadata"]["statusCode"]) && ($awsResult["@metadata"]["statusCode"] == 200)) {

                $this->logDebug('s3.putObject return', [
                    'result' => true,
                ]);

                return true;
            }

        } catch (S3Exception $e) {

            $this->logDebug(sprintf("s3.putObject error: %s", $e->getMessage()));

        }

        $this->logDebug('s3.putObject return', [
            'result' => false,
        ]);

        return false;
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function getObject(string $path): ?string
    {
        try {
            $args = [
                'Bucket' => $this->getBucketName(),
                'Key' => $path,
            ];
            $this->logDebug('s3.getObject', $args);

            $awsResult = $this->s3Client->getObject($args);

            if (isset($awsResult["@metadata"]["statusCode"]) && ($awsResult["@metadata"]["statusCode"] == 200)) {
                $body = $awsResult->get('Body');
                if ($body instanceof \GuzzleHttp\Psr7\Stream) {
                    $content = $body->getContents();
                    $this->logDebug('s3.getObject return', [
                        'content' => $content,
                    ]);
                    return $content;
                }
            }
        } catch (S3Exception $e) {

            $this->logDebug(sprintf("s3.getObject error: %s", $e->getMessage()));

        }

        $this->logDebug('s3.getObject return', [
            'result' => null,
        ]);

        return null;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isObjectExist(string $path): bool
    {
        try {

            $this->logDebug('s3.isObjectExist', [
                'bucketName' => $this->getBucketName(),
                'key' => $path
            ]);

            $result = $this->s3Client->doesObjectExist($this->getBucketName(), $path);

            $this->logDebug('s3.isObjectExist return', [
                'result' => $result,
            ]);

            return $result;

        } catch (S3Exception $e) {

            $this->logDebug(sprintf("s3.isObjectExist error: %s", $e->getMessage()));

        }

        $this->logDebug('s3.isObjectExist return', [
            'result' => false,
        ]);

        return false;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function deleteObject(string $path): bool
    {
        try {
            $args = [
                'Bucket' => $this->getBucketName(),
                'Key' => $path,
            ];
            $this->logDebug('s3.deleteObject', $args);

            $awsResult = $this->s3Client->deleteObject($args);

            if (isset($awsResult["@metadata"]["statusCode"]) && ($awsResult["@metadata"]["statusCode"] == 204)) {

                $this->logDebug('s3.deleteObject return', [
                    'result' => true,
                ]);

                return true;
            }

        } catch (S3Exception $e) {

            $this->logDebug(sprintf("s3.deleteObject error: %s", $e->getMessage()));

        }

        $this->logDebug('s3.deleteObject return', [
            'result' => false,
        ]);

        return false;
    }

    public function check(string $key = 'check.txt'): bool
    {
        $this->logDebug(__METHOD__);

        $date = date("Y-m-d H:i:s");
        if ($this->putObject($key, $date)) {
            if ($this->isObjectExist($key)) {
                $content = $this->getObject($key);
                if ($content === $date && $this->deleteObject($key)) {
                    return true;
                }
            }
        }
        return false;
    }
}