<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConsigneeController extends Controller
{
    public function index()
    {
        return \App\Models\Consignee::all();
    }

    public function store(Request $request)
    {
        $consignee = \App\Models\Consignee::create($request->all());
        return response()->json($consignee, 201);
    }

    public function show(string $id)
    {
        $consignee = \App\Models\Consignee::findOrFail($id);
        return response()->json($consignee);
    }

    public function update(Request $request, string $id)
    {
        $consignee = \App\Models\Consignee::findOrFail($id);
        $consignee->update($request->all());
        return response()->json($consignee);
    }

    public function destroy(string $id)
    {
        $consignee = \App\Models\Consignee::findOrFail($id);
        $consignee->delete();
        return response()->json(null, 204);
    }
}
