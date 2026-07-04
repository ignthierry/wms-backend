<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemLog;

class SystemLogController extends Controller
{
    public function index()
    {
        return SystemLog::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Add validation rules here
        ]);
        // Bypass strict validation for quick scaffold
        $item = SystemLog::create($request->all());
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = SystemLog::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = SystemLog::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = SystemLog::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
