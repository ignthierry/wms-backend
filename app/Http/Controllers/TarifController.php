<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarif;

class TarifController extends Controller
{
    public function index()
    {
        return response()->json(Tarif::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tarif' => 'required|string|max:100',
            'storage_masa_1' => 'nullable|numeric',
            'storage_masa_2' => 'nullable|numeric',
            'storage_masa_3' => 'nullable|numeric',
            'storage_masa_4' => 'nullable|numeric',
            'administrasi' => 'nullable|numeric',
            'minimum_tarif' => 'nullable|numeric',
            'mekanis' => 'nullable|numeric',
            'service' => 'nullable|numeric',
            'surveyor_fee' => 'nullable|numeric',
            'behandle' => 'nullable|numeric',
            'stiker' => 'nullable|numeric',
        ]);

        $tarif = Tarif::create($validated);
        return response()->json($tarif, 201);
    }

    public function show($id)
    {
        $tarif = Tarif::findOrFail($id);
        return response()->json($tarif);
    }

    public function update(Request $request, $id)
    {
        $tarif = Tarif::findOrFail($id);
        
        $validated = $request->validate([
            'nama_tarif' => 'sometimes|required|string|max:100',
            'storage_masa_1' => 'nullable|numeric',
            'storage_masa_2' => 'nullable|numeric',
            'storage_masa_3' => 'nullable|numeric',
            'storage_masa_4' => 'nullable|numeric',
            'administrasi' => 'nullable|numeric',
            'minimum_tarif' => 'nullable|numeric',
            'mekanis' => 'nullable|numeric',
            'service' => 'nullable|numeric',
            'surveyor_fee' => 'nullable|numeric',
            'behandle' => 'nullable|numeric',
            'stiker' => 'nullable|numeric',
        ]);

        $tarif->update($validated);
        return response()->json($tarif);
    }

    public function destroy($id)
    {
        $tarif = Tarif::findOrFail($id);
        $tarif->delete();
        return response()->json(null, 204);
    }
}
