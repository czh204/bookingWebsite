<?php
 
namespace App\Http\Controllers;
 
class HomeController extends Controller
{
    public function index()
    {
        // In a real app this would come from a Destination model / query.
        $destinations = [
            ['city' => 'Paris',     'country' => 'France',    'price' => 649, 'badge' => 'Most Popular', 'image' => 'paris.jpg'],
            ['city' => 'Bali',      'country' => 'Indonesia', 'price' => 520, 'badge' => 'Trending',     'image' => 'bali.jpg'],
            ['city' => 'Tokyo',     'country' => 'Japan',     'price' => 890, 'badge' => "Editor's Pick",'image' => 'tokyo.jpg'],
            ['city' => 'Santorini', 'country' => 'Greece',    'price' => 710, 'badge' => 'Romantic',      'image' => 'santorini.jpg'],
            ['city' => 'New York',  'country' => 'USA',       'price' => 430, 'badge' => 'City Break',    'image' => 'new-york.jpg'],
            ['city' => 'Maldives',  'country' => 'Maldives',  'price' => 1250,'badge' => 'Luxury',        'image' => 'maldives.jpg'],
        ];
 
        return view('layouts.homepage', compact('destinations'));
    }
}