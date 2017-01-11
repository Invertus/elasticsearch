<?php

use Invertus\Brad\Service\Elasticsearch\Builder\ClientBuilder;

class ClientBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testBuildClientReturnsInstanceOfClient()
    {
        $clientBuilder = new ClientBuilder();

        $client = $clientBuilder->buildClient();

        $this->assertInstanceOf('\Elasticsearch\Client', $client);
    }
}
