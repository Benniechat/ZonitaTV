<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Channel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ChannelCatalogueService
{
    protected RightsEligibilityService $rightsService;

    public function __construct(RightsEligibilityService $rightsService)
    {
        $this->rightsService = $rightsService;
    }

    /**
     * Get paginated channel catalogue filtered by category, search, language, etc.
     */
    public function getCatalogue(array $filters = [], int $perPage = 18, string $clientCountry = 'NGA'): LengthAwarePaginator
    {
        $query = Channel::query()
            ->with(['categories', 'latestRights', 'primaryStream'])
            ->where('is_active', true)
            ->where('is_published', true);

        // Filter by category slug
        if (! empty($filters['category'])) {
            $query->whereHas('categories', function (Builder $q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        // Sanitized search query with parameterized wildcard bindings to prevent SQL wildcard injection
        if (! empty($filters['q'])) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim((string) $filters['q']));
            $searchTerm = '%'.$escaped.'%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('broadcaster_name', 'like', $searchTerm);
            });
        }

        // Filter by country code
        if (! empty($filters['country'])) {
            $query->where('country_code', strtoupper($filters['country']));
        }

        // Filter by language
        if (! empty($filters['language'])) {
            $query->where('language_code', strtolower($filters['language']));
        }

        // Featured filter
        if (! empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        // Default ordering
        $query->orderBy('is_featured', 'desc')
            ->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc');

        $paginator = $query->paginate($perPage);

        // Filter items that satisfy rights eligibility
        $paginator->getCollection()->transform(function ($channel) use ($clientCountry) {
            $channel->can_list = $this->rightsService->canList($channel, $clientCountry);

            return $channel;
        });

        return $paginator;
    }

    /**
     * Get featured collections for the homepage sections
     */
    public function getHomepageCollections(string $clientCountry = 'NGA'): array
    {
        $baseQuery = fn () => Channel::with(['categories', 'latestRights'])
            ->where('is_active', true)
            ->where('is_published', true);

        $featured = $baseQuery()->where('is_featured', true)->take(8)->get()
            ->filter(fn ($c) => $this->rightsService->canList($c, $clientCountry));

        $sports = $baseQuery()->whereHas('categories', fn ($q) => $q->where('slug', 'sports'))->take(8)->get()
            ->filter(fn ($c) => $this->rightsService->canList($c, $clientCountry));

        $news = $baseQuery()->whereHas('categories', fn ($q) => $q->where('slug', 'news'))->take(8)->get()
            ->filter(fn ($c) => $this->rightsService->canList($c, $clientCountry));

        $christian = $baseQuery()->whereHas('categories', fn ($q) => $q->where('slug', 'christian-tv'))->take(8)->get()
            ->filter(fn ($c) => $this->rightsService->canList($c, $clientCountry));

        $education = $baseQuery()->whereHas('categories', fn ($q) => $q->where('slug', 'education'))->take(8)->get()
            ->filter(fn ($c) => $this->rightsService->canList($c, $clientCountry));

        return [
            'featured' => $featured,
            'sports' => $sports,
            'news' => $news,
            'christian' => $christian,
            'education' => $education,
        ];
    }
}
