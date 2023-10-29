<?php

namespace YusamHub\S3Sdk\Tests;

use YusamHub\S3Sdk\ClientS3Sdk;

class ClientS3SdkTest extends \PHPUnit\Framework\TestCase
{
    public function testCheck()
    {
        $clientS3Sdk = new ClientS3Sdk(Config::getConfig('s3-sdk'));
        $this->assertTrue($clientS3Sdk->check());
        var_dump($clientS3Sdk->getLogsAsString());
    }
}