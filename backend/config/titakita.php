<?php

use Ramsey\Uuid\Uuid;

/*
|--------------------------------------------------------------------------
| TitaKita Node / Directory Configuration
|--------------------------------------------------------------------------
|
| TitaKita is a decentralised event-discovery layer. Every self-hosted node
| exposes a small, stable set of interfaces so a future directory (and AI
| agents) can discover, read and link back to it. These settings drive those
| interfaces (.well-known descriptor, public events feed, directory webhook).
|
| A node runs perfectly fine with all of these left at their defaults; the
| directory is purely opt-in.
|
*/

return [

    /*
     | Stable, unique identifier for this node. Set TITAKITA_NODE_ID to a fixed
     | value in production (setup.sh generates one). If left empty, a stable
     | UUIDv5 is derived from the public frontend URL, so the same domain always
     | yields the same id without requiring persistence.
     */
    'node_id' => env('TITAKITA_NODE_ID')
        ?: Uuid::uuid5(Uuid::NAMESPACE_URL, (string) env('APP_FRONTEND_URL', 'http://localhost'))->toString(),

    /*
     | Public, canonical URL of this node. Used as the backlink in all outbound
     | data so the directory can drive users back to the source. Falls back to
     | the app frontend URL.
     */
    'public_url' => rtrim((string) env('APP_FRONTEND_URL', 'http://localhost'), '/'),

    /*
     | Human-friendly node name. Defaults to the application name.
     */
    'name' => env('APP_NAME', 'TitaKita'),

    /*
     | Whether this node wishes to be included in the public TitaKita directory.
     | The directory must honour this flag. Defaults to false (private) so a node
     | is never listed without the operator's explicit consent.
     */
    'opt_in_directory' => filter_var(env('TITAKITA_OPT_IN_DIRECTORY', false), FILTER_VALIDATE_BOOLEAN),

    /*
     | Directory endpoint that should receive change webhooks (event published /
     | updated / cancelled). Leave empty to disable directory reporting entirely.
     */
    'directory_url' => env('TITAKITA_DIRECTORY_URL'),

    /*
     | Optional geographic region hint for the directory (e.g. "TW", "SEA",
     | "Global"). Free-form; helps the directory group nodes by locality.
     */
    'region' => env('TITAKITA_REGION'),

    /*
     | Optional public contact (email or URL) surfaced in the node descriptor so
     | the directory / users can reach the operator.
     */
    'contact' => env('TITAKITA_CONTACT'),

    /*
     | Optional comma-separated list of the kinds of events this node hosts
     | (e.g. "music,tech,community"). Surfaced in the descriptor for discovery.
     */
    'categories' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TITAKITA_CATEGORIES', ''))
    ))),

    /*
     | Version of the node<->directory contract this node implements. Bumped when
     | the descriptor / feed shape changes, so the directory can adapt.
     */
    'contract_version' => 1,
];
