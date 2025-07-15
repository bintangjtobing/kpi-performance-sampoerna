<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Target;

class TargetController extends Controller
{
    public function getTarget()
    {
        $target = Target::where('name', 'default')->first();
        
        if (!$target) {
            $target = Target::create([
                'name' => 'default',
                'target_value' => 42,
                'is_active' => true
            ]);
        }

        return response()->json([
            'target' => $target
        ]);
    }

    public function updateTarget(Request $request)
    {
        $request->validate([
            'target_value' => 'required|integer|min:1|max:100'
        ]);

        $target = Target::where('name', 'default')->first();
        
        if (!$target) {
            $target = Target::create([
                'name' => 'default',
                'target_value' => $request->target_value,
                'is_active' => true
            ]);
        } else {
            $target->update([
                'target_value' => $request->target_value
            ]);
        }

        return response()->json([
            'success' => true,
            'target' => $target,
            'message' => 'Target berhasil diupdate'
        ]);
    }
}
