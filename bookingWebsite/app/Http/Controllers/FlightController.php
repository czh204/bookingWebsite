<?php

namespace App\Http\Controllers;

use App\Services\FlightSearch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * The /flights page.
 *
 * All filtering, sorting and fare mapping lives in App\Services\FlightSearch
 * so the AI SearchFlights tool runs the identical query — the chatbot can't
 * quote a flight or price this page wouldn't show. Mirrors HotelController.
 */
class FlightController extends Controller
{
    protected const PER_PAGE = 10;

    public function index(Request $request, FlightSearch $search)
    {
        // The airline datalist offers every airline, not just the ones
        // surviving the current filters, so it stays a discovery aid.
        $airlines = $search->loadFlights()->pluck('airline_name')->unique()->sort()->values();

        $flights = $search->search([
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'stops' => (array) $request->query('stops', []),
            'airline' => $request->query('airline'),
            'departure_time' => (array) $request->query('departure_time', []),
            'sort' => $request->string('sort', 'price_asc')->toString(),
        ]);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paged = new LengthAwarePaginator(
            $flights->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $flights->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('flights.index', [
            'flights' => $paged,
            'airlines' => $airlines,
        ]);
    }
}
