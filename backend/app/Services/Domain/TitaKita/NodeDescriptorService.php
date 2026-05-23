<?php

namespace TitaKita\Services\Domain\TitaKita;

use Illuminate\Contracts\Config\Repository as ConfigRepository;

/**
 * Builds the TitaKita node descriptor served at /.well-known/titakita.json.
 *
 * The descriptor is the "glue" of the decentralised contract: it lets a future
 * directory (and AI agents) discover a node, learn where its public events feed
 * lives, and link back to the source. It is intentionally lightweight and built
 * entirely from configuration — no database access required.
 */
class NodeDescriptorService
{
    public function __construct(private readonly ConfigRepository $config)
    {
    }

    public function getDescriptor(): array
    {
        $publicUrl = rtrim((string)$this->config->get('titakita.public_url'), '/');

        return [
            'node' => [
                'id' => $this->config->get('titakita.node_id'),
                'name' => $this->config->get('titakita.name'),
                'url' => $publicUrl,
                'region' => $this->config->get('titakita.region'),
                'categories' => $this->config->get('titakita.categories', []),
                'contact' => $this->config->get('titakita.contact'),
                'opt_in_directory' => (bool)$this->config->get('titakita.opt_in_directory', false),
            ],
            'software' => [
                'name' => 'TitaKita',
                'based_on' => 'Hi.Events',
                'version' => $this->resolveVersion(),
            ],
            'contract_version' => (int)$this->config->get('titakita.contract_version', 1),
            'endpoints' => [
                'events_feed' => $publicUrl . '/api/public/events',
                'sitemap' => $publicUrl . '/sitemap.xml',
                'llms' => $publicUrl . '/llms.txt',
            ],
        ];
    }

    private function resolveVersion(): ?string
    {
        $versionFile = base_path('VERSION');

        if (is_readable($versionFile)) {
            $version = trim((string)file_get_contents($versionFile));

            return $version !== '' ? $version : null;
        }

        return null;
    }
}
