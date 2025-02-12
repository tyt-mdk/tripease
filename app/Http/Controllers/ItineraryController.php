<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Itinerary;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    public function index(Trip $trip)
    {
        $itineraries = $trip->itineraries;
        $wishes = $trip->wishes;  // 要望一覧も取得

        return view('trips.itinerary', compact('trip', 'itineraries', 'wishes'));
    }

    public function store(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'day_label' => 'required|string|max:255',
            'memo' => 'required|string',
            'order' => 'integer'
        ]);

        $itinerary = $trip->itineraries()->create([
            'day_label' => $validated['day_label'],
            'memo' => $validated['memo'],
            'order' => $validated['order'] ?? $trip->itineraries()->count(),
            'created_by' => auth()->id()
        ]);

        return response()->json($itinerary);
    }

    public function update(Request $request, Trip $trip, Itinerary $itinerary)
    {
        $validated = $request->validate([
            'day_label' => 'sometimes|required|string|max:255',
            'memo' => 'sometimes|required|string',
            'order' => 'sometimes|integer'
        ]);

        $itinerary->update($validated);

        return response()->json($itinerary);
    }

    public function destroy(Trip $trip, Itinerary $itinerary)
    {
        $itinerary->delete();
        return response()->json(['success' => true]);
    }

    public function updateOrder(Request $request, Trip $trip)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:itineraries,id',
            'orders.*.order' => 'required|integer'
        ]);

        foreach ($request->orders as $item) {
            Itinerary::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}