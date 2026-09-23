<?php

namespace App\Http\Controllers\Admin;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use Inertia\Inertia;

class PageController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Pages/Index', [
            'pages' => Page::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pages/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:1024',     // 1MB max
            'thumbnail' => 'nullable|image|max:400',  // 400KB max
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
    
        Page::create($validated);
    
        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page)
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => $page
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:1024',
            'thumbnail' => 'nullable|image|max:400',
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
    
        $page->update($validated);
    
        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    
    public function destroy(Page $page)
    {
        $page->delete();
        return back()->with('success', 'Page deleted.');
    }
}
