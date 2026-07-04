<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        return Client::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Add validation rules here
        ]);
        // Bypass strict validation for quick scaffold
        $item = Client::create($request->all());
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = Client::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = Client::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = Client::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
