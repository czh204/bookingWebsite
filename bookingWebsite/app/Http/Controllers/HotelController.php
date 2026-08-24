<?php

namespace App\Http\Controllers;

use App\Services\HotelSearch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class HotelController extends Controller
{
    protected const PER_PAGE = 5;

    public function index(Request $request, HotelSearch $search)
    {
        $hotels = $search->search([
            'destination' => $request->query('destination'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'star_rating' => (array) $request->query('star_rating', []),
            'amenities' => (array) $request->query('amenities', []),
            'sort' => $request->string('sort', 'recommended')->toString(),
        ]);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paged = new LengthAwarePaginator(
            $hotels->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $hotels->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('hotels.index', [
            'hotels' => $paged,
        ]);
    }
}
