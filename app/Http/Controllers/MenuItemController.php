<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MenuItemController extends Controller
{
    public function __construct()
    {
        // Apply admin middleware to all methods except index
        $this->middleware('admin')->except(['index']);
    }

    public function index()
    {
        $menuItems = MenuItem::all();
        return response()->json($menuItems);
    }

    public function store(Request $request)
    {
        // Verify if user is admin
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'available' => 'required|boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('menu-items', 'public');
            }

            $menuItem = MenuItem::create([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'available' => $validated['available'],
                'image' => $imagePath
            ]);

            return response()->json([
                'message' => 'Article créé avec succès',
                'menuItem' => $menuItem
            ], 201);
        } catch (\Exception $e) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            return response()->json(['message' => 'Erreur lors de la création'], 500);
        }
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        // Verify if user is admin
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'available' => 'required|boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        try {
            $imagePath = $menuItem->image;
            if ($request->hasFile('image')) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file('image')->store('menu-items', 'public');
            }

            $menuItem->update([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'available' => $validated['available'],
                'image' => $imagePath
            ]);

            return response()->json([
                'message' => 'Article mis à jour avec succès',
                'menuItem' => $menuItem
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour'], 500);
        }
    }

    public function destroy(MenuItem $menuItem)
    {
        // Verify if user is admin
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        try {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            
            $menuItem->delete();
            
            return response()->json([
                'message' => 'Article supprimé avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression'], 500);
        }
    }
}