<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsnItemPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AsnItemPhotoController extends Controller
{
    public function destroy(string $id)
    {
        $photo = AsnItemPhoto::findOrFail($id);

        try {
            if ($photo->photo_proof) {
                $path = $photo->photo_proof;
                if (Storage::disk('sftp')->exists($path)) {
                    Storage::disk('sftp')->delete($path);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not delete photo file from SFTP storage: ' . $e->getMessage());
        }

        $photo->delete();

        return response()->json(['message' => 'Foto berhasil dihapus']);
    }
}
