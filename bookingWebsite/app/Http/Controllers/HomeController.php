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
                    'question' => 'How do I cancel a booking?',
                    'answer' => 'Open the booking from the Calendar in My Bookings and select "Cancel Reservation". '
                        .'Whether you are refunded depends on the fare or rate rules for that booking.',
                ],
                [
                    'question' => 'When do I get a refund?',
                    'answer' => 'Cancel within 24 hours of purchase for a full refund. After that the refund is '
                        .'partial, based on the fare type you booked.',
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
                    'answer' => 'Free cancellation up to 48 hours before check-in. Cancelling later than that is '
                        .'charged one night.',
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
