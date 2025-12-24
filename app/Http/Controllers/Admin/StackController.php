<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class StackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stacks = Stack::orderBy('order')->orderBy('name')->get();
        return view('admin.stacks.index', compact('stacks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.stacks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'color' => 'nullable|string|max:50',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('stacks', 'public');
        }

        Stack::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'image' => $imagePath,
            'color' => $request->color,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.stacks.index')
            ->with('success', 'Stack created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $stack = Stack::findOrFail($id);
        return view('admin.stacks.show', compact('stack'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $stack = Stack::findOrFail($id);
        return view('admin.stacks.edit', compact('stack'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $stack = Stack::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'color' => 'nullable|string|max:50',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $imagePath = $stack->image;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($stack->image && Storage::disk('public')->exists($stack->image)) {
                Storage::disk('public')->delete($stack->image);
            }
            // Store new image
            $imagePath = $request->file('image')->store('stacks', 'public');
        } elseif ($request->has('remove_image') && $request->remove_image == '1') {
            // Remove image if requested
            if ($stack->image && Storage::disk('public')->exists($stack->image)) {
                Storage::disk('public')->delete($stack->image);
            }
            $imagePath = null;
        }

        $stack->update([
            'name' => $request->name,
            'icon' => $request->icon,
            'image' => $imagePath,
            'color' => $request->color,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.stacks.index')
            ->with('success', 'Stack updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stack = Stack::findOrFail($id);

        // Delete associated image if exists
        if ($stack->image && Storage::disk('public')->exists($stack->image)) {
            Storage::disk('public')->delete($stack->image);
        }

        $stack->delete();

        return redirect()->route('admin.stacks.index')
            ->with('success', 'Stack deleted successfully.');
    }
}
