<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryReorderLevel;

class InventoryReorderLevelController extends Controller
{
    public function index(Request $request)
    {
        $locationCode = $request->query('location_code');

        $locations = \App\Models\Location::orderBy('location_code')->where('active',1)->get();

        $groups = collect();

        if ($locationCode) {

            // ambil inventory by location
            $inventories = \App\Models\Inventory::query()
                ->where('location_code', $locationCode)
                ->get()
                ->map(function ($inv) {
                    // cutting_center dari custom_field / payload (sesuai real data kamu)
                    $inv->cutting_center = data_get($inv->custom_field, 'cutting_center', 'UNDEFINED');
                    return $inv;
                });

            // group by cutting center
            $groups = $inventories->groupBy('cutting_center');
        }

        return view('inventory_reorder.index', compact(
            'locations',
            'locationCode',
            'groups'
        ));
    }

    public function autosave(Request $request)
{
    $request->validate([
        'inventory_id' => ['required', 'exists:inventories,id'],
        'reorder_level' => ['nullable', 'integer', 'min:0'],
        'reorder_qty' => ['nullable', 'integer', 'min:0'],
        'unit_price' => ['nullable', 'numeric', 'min:0'],
    ]);

    $inventory = \App\Models\Inventory::findOrFail($request->inventory_id);

    $row = \App\Models\InventoryReorderLevel::updateOrCreate(
        ['inventory_id' => $inventory->id],
        [
            'code' => $inventory->code,
            'cutting_center' => data_get($inventory->custom_field, 'cutting_center'),
            'reorder_level' => $request->reorder_level ?? 0,
            'reorder_qty' => $request->reorder_qty ?? 0,
            'unit_price' => $request->unit_price ?? 0,
            'reorder_value' =>
                ($request->reorder_qty ?? 0) * ($request->unit_price ?? 0),
            'last_calculated_at' => now(),
            'is_active' => true,
        ]
    );

    return response()->json([
        'ok' => true,
        'id' => $row->id,
    ]);
}



    public function create()
    {
        $inventories = Inventory::orderBy('code')->get();

        return view('inventory_reorder.create', compact('inventories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'inventory_id'   => ['required', 'exists:inventories,id'],
            'reorder_level'  => ['required', 'integer', 'min:0'],
            'reorder_qty'    => ['required', 'integer', 'min:0'],
            'unit_price'     => ['required', 'numeric', 'min:0'],
        ]);

        $inv = Inventory::findOrFail($request->inventory_id);

        InventoryReorderLevel::updateOrCreate(
            ['inventory_id' => $inv->id],
            [
                'code' => $inv->code,
                'cutting_center' => data_get($inv->custom_field, 'cutting_center'),
                'reorder_level' => $request->reorder_level,
                'reorder_qty' => $request->reorder_qty,
                'unit_price' => $request->unit_price,
                'reorder_value' => $request->reorder_qty * $request->unit_price,
                'last_calculated_at' => now(),
                'is_active' => true,
            ]
        );

        return redirect()
            ->route('inventory-reorder.index')
            ->with('success', 'Re-order level saved.');
    }

    public function edit($id)
    {
        $row = InventoryReorderLevel::with('inventory')->findOrFail($id);

        return view('inventory_reorder.edit', compact('row'));
    }

    public function update(Request $request, $id)
    {
        $row = InventoryReorderLevel::findOrFail($id);

        $request->validate([
            'reorder_level' => ['required', 'integer', 'min:0'],
            'reorder_qty' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $row->update([
            'reorder_level' => $request->reorder_level,
            'reorder_qty' => $request->reorder_qty,
            'unit_price' => $request->unit_price,
            'reorder_value' => $request->reorder_qty * $request->unit_price,
            'last_calculated_at' => now(),
            'is_active' => $request->is_active,
        ]);

        return redirect()
            ->route('inventory-reorder.index')
            ->with('success', 'Re-order level updated.');
    }

    public function destroy($id)
    {
        InventoryReorderLevel::findOrFail($id)->delete();

        return back()->with('success', 'Re-order level deleted.');
    }
}
