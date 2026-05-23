<?php

namespace Tests\Unit\Services\Domain\TitaKita;

use TitaKita\Services\Domain\TitaKita\NodeDescriptorService;
use Tests\TestCase;

class NodeDescriptorServiceTest extends TestCase
{
    private function makeService(): NodeDescriptorService
    {
        return new NodeDescriptorService(app('config'));
    }

    public function test_builds_descriptor_from_config(): void
    {
        config()->set('titakita.node_id', 'test-node-id');
        config()->set('titakita.public_url', 'https://node.example.com');
        config()->set('titakita.name', 'My TitaKita');
        config()->set('titakita.region', 'TW');
        config()->set('titakita.categories', ['music', 'tech']);
        config()->set('titakita.contact', 'hello@example.com');
        config()->set('titakita.opt_in_directory', true);
        config()->set('titakita.contract_version', 1);

        $descriptor = $this->makeService()->getDescriptor();

        $this->assertSame('test-node-id', $descriptor['node']['id']);
        $this->assertSame('My TitaKita', $descriptor['node']['name']);
        $this->assertSame('https://node.example.com', $descriptor['node']['url']);
        $this->assertSame('TW', $descriptor['node']['region']);
        $this->assertSame(['music', 'tech'], $descriptor['node']['categories']);
        $this->assertSame('hello@example.com', $descriptor['node']['contact']);
        $this->assertTrue($descriptor['node']['opt_in_directory']);

        $this->assertSame('TitaKita', $descriptor['software']['name']);
        $this->assertSame('Hi.Events', $descriptor['software']['based_on']);

        $this->assertSame(1, $descriptor['contract_version']);
        $this->assertSame('https://node.example.com/api/public/events', $descriptor['endpoints']['events_feed']);
        $this->assertSame('https://node.example.com/sitemap.xml', $descriptor['endpoints']['sitemap']);
        $this->assertSame('https://node.example.com/llms.txt', $descriptor['endpoints']['llms']);
    }

    public function test_public_url_trailing_slash_is_normalised(): void
    {
        config()->set('titakita.public_url', 'https://node.example.com/');

        $descriptor = $this->makeService()->getDescriptor();

        $this->assertSame('https://node.example.com', $descriptor['node']['url']);
        $this->assertSame('https://node.example.com/api/public/events', $descriptor['endpoints']['events_feed']);
    }

    public function test_opt_in_directory_is_cast_to_bool(): void
    {
        config()->set('titakita.opt_in_directory', false);

        $this->assertFalse($this->makeService()->getDescriptor()['node']['opt_in_directory']);
    }
}
