<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('layouts.homepage');
    }

    /**
     * The FAQ the footer links to.
     *
     * The answers are the same booking policies the support assistant is
     * instructed with (see App\Ai\Agents\SupportAgent), so the page and
     * the chatbot say the same thing.
     */
    public function faq()
    {
        return view('faq', [
            'faqs' => [
                [
                    'question' => 'Where do I view my bookings?',
                    'answer' => 'Everything you have booked is on the My Bookings page in your account.',
                ],
                [
                    'question' => 'How do I cancel a booking?',
                    'answer' => 'Open the booking from My Bookings and select "Cancel Reservation". Whether a '
                        .'cancellation is allowed is set by the airline or hotel, not by us.',
                ],
                [
                    'question' => 'When do I get a refund?',
                    'answer' => 'Refund eligibility is decided by the airline or hotel you booked with. Once a '
                        .'refund is approved it is processed within 48 hours.',
                ],
                [
                    'question' => 'How long does a refund take?',
                    'answer' => 'Once Voyagr approves the refund it is processed within 48 hours. How long it then '
                        .'takes to appear is up to the airline, hotel or attraction operator and your bank, so the '
                        .'money may land a few days after that.',
                ],
                [
                    'question' => 'Can I change a booking after paying?',
                    'answer' => 'Not through the website. Changes are subject to the airline or hotel\'s own policies, '
                        .'so call the phone number shown on the booking to ask them directly.',
                ],
                [
                    'question' => 'What payment methods can I use?',
                    'answer' => 'All major credit and debit cards, plus the TnG eWallet, Boost and DuitNow e-wallets.',
                ],
                [
                    'question' => 'What is the hotel cancellation policy?',
                    'answer' => 'Each hotel sets its own. Check that hotel\'s policies before booking, or call '
                        .'them directly if you are unsure.',
                ],
                [
                    'question' => 'Are pets allowed?',
                    'answer' => 'It depends on the hotel. Check that hotel\'s policies before booking, or call them '
                        .'directly if it is not listed.',
                ],
                [
                    'question' => 'What are the check-in and check-out times?',
                    'answer' => 'They vary by hotel and are shown on each hotel before you book, so check them there.',
                ],
            ],
        ]);
    }
}
