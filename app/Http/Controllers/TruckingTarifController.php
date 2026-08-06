<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TruckingTarif;

class TruckingTarifController extends Controller
{
    public function index()
    {
        return response()->json(TruckingTarif::with('company')->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'trucking_company_id' => 'required|exists:trucking_companies,id',
            'nama_tarif' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:100',
            'rate' => 'nullable|numeric|min:0',
            'rate_unit' => 'nullable|string|max:50',
            'minimum_charge' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $tarif = TruckingTarif::create($data);

        return response()->json($tarif->load('company'), 201);
    }

    public function update(Request $request, $id)
    {
        $tarif = TruckingTarif::findOrFail($id);
        $tarif->update($request->all());

        return response()->json($tarif->load('company'));
    }

    public function destroy($id)
    {
        TruckingTarif::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}