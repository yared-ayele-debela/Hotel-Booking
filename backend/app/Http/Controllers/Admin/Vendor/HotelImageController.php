<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HotelImageController extends Controller
{
    public function index(Hotel $hotel): View
    {
        $this->authorize('update', $hotel);
        $images = $hotel->images()->ordered()->get();
        return view('admin.vendor.hotels.images.index', compact('hotel', 'images'));
    }

    public function store(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);
        
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
            'alt_texts.*' => 'nullable|string|max:255',
            'banner_image' => 'nullable|integer',
        ]);

        $uploadedImages = [];
        
        if ($request->hasFile('images')) {
            $bannerImageId = $request->input('banner_image');
            
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('hotels/' . $hotel->id, 'public');
                
                $hotelImage = HotelImage::create([
                    'hotel_id' => $hotel->id,
                    'image_path' => $path,
                    'alt_text' => $request->input("alt_texts.{$index}"),
                    'is_banner' => $bannerImageId == $index,
                    'sort_order' => $hotel->images()->max('sort_order') + 1,
                ]);
                
                $uploadedImages[] = [
                    'id' => $hotelImage->id,
                    'url' => $hotelImage->image_url,
                    'alt_text' => $hotelImage->alt_text,
                    'is_banner' => $hotelImage->is_banner,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages,
        ]);
    }

    public function update(Request $request, Hotel $hotel, HotelImage $image): JsonResponse
    {
        $this->authorize('update', $hotel);
        
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'is_banner' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // If setting as banner, unset other banners
        if ($request->boolean('is_banner')) {
            $hotel->images()->where('id', '!=', $image->id)->update(['is_banner' => false]);
        }

        $image->update($request->only(['alt_text', 'is_banner', 'sort_order']));

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
            'image' => [
                'id' => $image->id,
                'url' => $image->image_url,
                'alt_text' => $image->alt_text,
                'is_banner' => $image->is_banner,
                'sort_order' => $image->sort_order,
            ],
        ]);
    }

    public function destroy(Hotel $hotel, HotelImage $image): JsonResponse
    {
        $this->authorize('update', $hotel);
        
        // Delete file from storage
        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    public function reorder(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);
        
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:hotel_images,id',
            'images.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->input('images') as $imageData) {
            HotelImage::where('id', $imageData['id'])
                ->where('hotel_id', $hotel->id)
                ->update(['sort_order' => $imageData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Images reordered successfully',
        ]);
    }
}
