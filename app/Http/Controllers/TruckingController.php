<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TruckingCompany;

class TruckingController extends Controller
{
    public function index()
    {
        return response()->json(TruckingCompany::with('tarifs')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company_code' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'is_ours' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $company = TruckingCompany::create($data);

        return response()->json($company->load('tarifs'), 201);
    }

    public function show($id)
    {
        return response()->json(TruckingCompany::with('tarifs')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $company = TruckingCompany::findOrFail($id);
        $company->update($request->all());

        return response()->json($company->load('tarifs'));
    }

    public function destroy($id)
    {
        TruckingCompany::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}