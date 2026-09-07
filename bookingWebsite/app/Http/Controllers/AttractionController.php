<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Services\Gallery;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AttractionController extends Controller
{
    protected const PER_PAGE = 10;

    protected const CATEGORIES = ['Museum', 'Tour', 'Food & Drink', 'Adventure'];

    public function __construct(protected Gallery $gallery = new Gallery) {}

    public function index(Request $request)
    {
        $attractions = $this->loadAttractions();

        $attractions = $this->applyFilters($attractions, $request);
        $attractions = $this->applySort($attractions, $request->string('sort', 'recommended')->toString());

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paged = new LengthAwarePaginator(
            $attractions->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $attractions->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('attractions.index', [
            'attractions' => $paged,
            'categories' => self::CATEGORIES,
        ]);
    }

    protected function loadAttractions(): Collection
    {
        return Attraction::with('timeSlots')
            ->get()
            ->map(fn (Attraction $attraction) => $this->mapAttraction($attraction));
    }

    protected function mapAttraction(Attraction $attraction): object
    {
        $data = $attraction->attributesToArray();
        $data['time_slots'] = $this->buildTimeSlots($attraction);
        // null when image_path is empty or points at a missing file, so
        // the view shows the gradient placeholder instead.
        $data['image'] = $this->gallery->resolve($attraction->image_path);
        // Keyed off the id, not the loop position, so an attraction keeps
        // the same placeholder shade whichever page or filter it's on.
        $data['placeholder_shade'] = ($attraction->id % 6) + 1;

        return (object) $data;
    }

    protected function buildTimeSlots(Attraction $attraction): array
    {
        $labels = [
            'morning' => '8:00 AM',
            'afternoon' => '12:00 PM',
            'evening' => '4:00 PM',
        ];

        $existing = $attraction->timeSlots->keyBy('slot_key');

        return collect($labels)->map(function (string $label, string $key) use ($existing) {
            $slot = $existing->get($key);

            return [
                'key' => $key,
                'label' => $label,
                'available' => (bool) $slot,
                'price' => $slot ? (int) round((float) $slot->price) : null,
            ];
        })->values()->all();
    }

    protected function applyFilters(Collection $attractions, Request $request): Collection
    {
        $location = trim((string) $request->query('location'));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $categories = (array) $request->query('categories', self::CATEGORIES);
        $durations = (array) $request->query('duration', []);

        return $attractions
            ->when($location !== '', fn ($c) => $c->filter(fn ($a) => str_contains(strtolower($a->city), strtolower($location))
                || str_contains(strtolower($a->country), strtolower($location))
                || str_contains(strtolower($a->title), strtolower($location))))
            ->when(is_numeric($minPrice), fn ($c) => $c->filter(fn ($a) => $a->price >= (float) $minPrice))
            ->when(is_numeric($maxPrice), fn ($c) => $c->filter(fn ($a) => $a->price <= (float) $maxPrice))
            ->when(count($categories) > 0, fn ($c) => $c->filter(fn ($a) => in_array($a->category, $categories, true)))
            ->when(count($durations) > 0, fn ($c) => $c->filter(function ($a) use ($durations) {
                $bucket = match (true) {
                    $a->duration_hours < 2 => 'under-2',
                    $a->duration_hours <= 4 => '2-4',
                    $a->duration_hours <= 8 => '4-8',
                    default => 'full-day',
                };

                return in_array($bucket, $durations, true);
            }))
            ->values();
    }

    protected function applySort(Collection $attractions, string $sort): Collection
    {
        return match ($sort) {
            'price_asc' => $attractions->sortBy('price')->values(),
            'price_desc' => $attractions->sortByDesc('price')->values(),
            default => $attractions->sortBy('id')->values(),
        };
    }
}
