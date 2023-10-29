<?php

namespace YusamHub\S3Sdk\Tests;

class Config
{
    /**
     * @return string
     */
    public static function getConfigFile(): string
    {
        return realpath(__DIR__ . '/../config') . DIRECTORY_SEPARATOR . 'config.php';
    }

    /**
     * @return bool
     */
    public static function isConfigFileExists(): bool
    {
        return file_exists(static::getConfigFile());
    }

    /**
     * @param string $key
     * @return array
     */
    public static function getConfig(string $key): array
    {
        if (static::isConfigFileExists()) {
            $out = include(static::getConfigFile());
            if (isset($out[$key])) {
                return (array) $out[$key];
            }
        }
        return [];
    }
}