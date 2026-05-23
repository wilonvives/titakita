<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Sitemap;

use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Repository\Interfaces\OrganizerRepositoryInterface;
use TitaKita\Services\Domain\Sitemap\SitemapGeneratorService;
use Illuminate\Support\Facades\Cache;

class GetSitemapOrganizersHandler
{
    private const CACHE_KEY_PREFIX = 'sitemap:organizers:';
    private const MIN_PAGE = 1;

    public function __construct(
        private readonly OrganizerRepositoryInterface $organizerRepository,
        private readonly SitemapGeneratorService $sitemapGenerator,
    ) {
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function handle(int $page): string
    {
        if ($page < self::MIN_PAGE) {
            throw new ResourceNotFoundException(__('Page must be a positive integer'));
        }

        $organizersPerPage = (int) config('sitemap.organizers_per_page');
        $totalOrganizers = $this->organizerRepository->getSitemapOrganizerCount();
        $totalPages = $this->calculateTotalPages($totalOrganizers, $organizersPerPage);

        if ($page > $totalPages) {
            throw new ResourceNotFoundException(__('Page not found'));
        }

        $cacheTtl = (int) config('sitemap.cache_ttl');
        $cacheKey = self::CACHE_KEY_PREFIX . $page;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($page, $organizersPerPage): string {
            $organizers = $this->organizerRepository->getSitemapOrganizers($page, $organizersPerPage);
            $baseUrl = rtrim((string) config('app.frontend_url'), '/');

            return $this->sitemapGenerator->generateOrganizersSitemap(
                $organizers->getCollection(),
                $baseUrl
            );
        });
    }

    private function calculateTotalPages(int $totalOrganizers, int $organizersPerPage): int
    {
        return max(self::MIN_PAGE, (int) ceil($totalOrganizers / $organizersPerPage));
    }
}
