<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Show the landing page with events
     *
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function showLanding(Request $request)
    {
        // Fetch live events with their images and organiser
        $events = Event::with(['images', 'organiser', 'currency'])
            ->where('is_live', 1)
            ->where('end_date', '>=', now()) // Only future/current events
            ->orderBy('start_date', 'asc')
            ->limit(8) // Limit to 8 events for the grid
            ->get();

        return view('Public.Landing.Main', [
            'events' => $events
        ]);
    }

    /**
     * redirect index page
     * @param  Request $request http request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showIndex(Request $request)
    {
        return redirect()->route('showSelectOrganiser');
    }
}