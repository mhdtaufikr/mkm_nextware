<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // dropdown: only active locations
        $locations = DB::table('locations')
            ->select('id', 'external_id', 'display_name', 'location_code', 'is_default')
            ->where('active', 1)
            ->orderBy('display_name')
            ->get();

        if ($locations->isEmpty()) {
            return view('home.index', [
                'locations' => $locations,
            ])->with('failed', 'Tidak ada Location yang active.');
        }

        // selected location (internal id)
        $selectedId = $request->get('location_id');

        if (!$selectedId) {
            $defaultLoc = $locations->firstWhere('is_default', 1);
            $selectedId = $defaultLoc?->id ?? $locations->first()->id;
        }

        $selected = $locations->firstWhere('id', (int) $selectedId);
        if (!$selected) {
            $selected = $locations->first();
            $selectedId = $selected->id;
        }


        return view('home.index', compact('locations','selected', 'selectedId'));
    }
}
