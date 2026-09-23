<?php

namespace App\Http\Controllers\Admin;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use Inertia\Inertia;

class PackageController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Packages/Index', [
            'packages' => Package::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Packages/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2024',     // 1MB max
            'thumbnail' => 'nullable|image|max:400',  // 400KB max
            'status' => 'required|boolean',
        ]);
    
        $manager = new ImageManager(new Driver());
    
        if ($request->hasFile('image')) {
            $image = $manager->read($request->file('image'))->toJpeg(80);
            $imagePath = 'packages/image_' . time() . '.jpg';
            Storage::put('public/' . $imagePath, $image);
            $validated['image'] = $imagePath;
        }
    
        if ($request->hasFile('thumbnail')) {
            $thumb = $manager->read($request->file('thumbnail'))
                ->scaleDown(width: 300) // optional scale down
                ->toJpeg(70);
            $thumbPath = 'packages/thumb_' . time() . '.jpg';
            Storage::put('public/' . $thumbPath, $thumb);
            $validated['thumbnail'] = $thumbPath;
        }
    
        $validated['added_by'] = auth()->id();
    
        Package::create($validated);
    
        return redirect()->route('admin.packages.index')->with('success', 'Package created.');
    }

    public function edit(Package $package)
    {
        return Inertia::render('Admin/Packages/Edit', [
            'package' => $package
        ]);
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'package_name' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:1024',
            'thumbnail' => 'nullable|image|max:400',
            'status' => 'required|boolean',
        ]);
    
        $manager = new ImageManager(new Driver());
    
        if ($request->hasFile('image')) {
            $image = $manager->read($request->file('image'))->toJpeg(80);
            $imagePath = 'packages/image_' . time() . '.jpg';
            Storage::put('public/' . $imagePath, $image);
            $validated['image'] = $imagePath;
        }
    
        if ($request->hasFile('thumbnail')) {
            $thumb = $manager->read($request->file('thumbnail'))
                ->scaleDown(width: 300)
                ->toJpeg(70);
            $thumbPath = 'packages/thumb_' . time() . '.jpg';
            Storage::put('public/' . $thumbPath, $thumb);
            $validated['thumbnail'] = $thumbPath;
        }
    
        $package->update($validated);
    
        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    
    public function destroy(Package $package)
    {
        $package->delete();
        return back()->with('success', 'Package deleted.');
    }
}
