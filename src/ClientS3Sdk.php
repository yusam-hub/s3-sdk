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

    public function logDebug(string $message): void
    {
        if (!$this->isDebugging) return;
        $this->logs[] = $message;
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
            $this->logDebug(sprintf("s3.putObject: %s", json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));

            $awsResult = $this->s3Client->putObject($args);

            if (isset($awsResult["@metadata"]["statusCode"]) && ($awsResult["@metadata"]["statusCode"] == 200)) {

                $this->logDebug(
                    sprintf("s3.putObject return: %s", json_encode([
                        'result' => true,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                );

                return true;
            }

        } catch (S3Exception $e) {

            $this->logDebug(
                sprintf("s3.putObject error: %s", $e->getMessage())
            );

        }

        $this->logDebug(
            sprintf("s3.putObject return: %s", json_encode([
                'result' => false,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        );

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
            $this->logDebug(sprintf("s3.getObject: %s", json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));

            $awsResult = $this->s3Client->getObject($args);

            if (isset($awsResult["@metadata"]["statusCode"]) && ($awsResult["@metadata"]["statusCode"] == 200)) {
                $body = $awsResult->get('Body');
                if ($body instanceof \GuzzleHttp\Psr7\Stream) {
                    $content = $body->getContents();
                    $this->logDebug(
                        sprintf("s3.getObject return: %s", json_encode([
                            'content' => $content,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                    );
                    return $content;
                }
            }
        } catch (S3Exception $e) {

            $this->logDebug(
                sprintf("s3.getObject error: %s", $e->getMessage())
            );

        }

        $this->logDebug(
            sprintf("s3.getObject return: %s", json_encode([
                'result' => null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        );

        return null;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isObjectExist(string $path): bool
    {
        try {

            $this->logDebug(sprintf("s3.isObjectExist: %s", json_encode([
                'bucketName' => $this->getBucketName(),
                'key' => $path
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));

            $result = $this->s3Client->doesObjectExist($this->getBucketName(), $path);

            $this->logDebug(sprintf("s3.isObjectExist return: %s", json_encode([
                'result' => $result,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));

            return $result;

        } catch (S3Exception $e) {

            $this->logDebug(
                sprintf("s3.isObjectExist error: %s", $e->getMessage())
            );

        }

        $this->logDebug(sprintf("s3.isObjectExist return: %s", json_encode([
            'result' => false,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));

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
            $this->logDebug(sprintf("s3.deleteObject: %s", json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));

            $awsResult = $this->s3Client->deleteObject($args);

            if (isset($awsResult["@metadata"]["statusCode"]) && ($awsResult["@metadata"]["statusCode"] == 204)) {

                $this->logDebug(
                    sprintf("s3.deleteObject return: %s", json_encode([
                        'result' => true,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                );

                return true;
            }

        } catch (S3Exception $e) {

            $this->logDebug(
                sprintf("s3.deleteObject error: %s", $e->getMessage())
            );

        }

        $this->logDebug(
            sprintf("s3.deleteObject return: %s", json_encode([
                'result' => false,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        );

        return false;
    }

    public function check(string $key = 'check.txt'): bool
    {
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