<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RoomImageController extends Controller
{
    public function index(Room $room): View
    {
        $this->authorize('update', $room);
        $images = $room->images()->ordered()->get();
        return view('admin.vendor.rooms.images.index', compact('room', 'images'));
    }

    public function store(Request $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);
        
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
            'alt_texts.*' => 'nullable|string|max:255',
            'banner_image' => 'nullable|integer',
        ]);

        $uploadedImages = [];
        
        if ($request->hasFile('images')) {
            $bannerImageId = $request->input('banner_image');
            
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('rooms/' . $room->id, 'public');
                
                $roomImage = RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $path,
                    'alt_text' => $request->input("alt_texts.{$index}"),
                    'is_banner' => $bannerImageId == $index,
                    'sort_order' => $room->images()->max('sort_order') + 1,
                ]);
                
                $uploadedImages[] = [
                    'id' => $roomImage->id,
                    'url' => $roomImage->image_url,
                    'alt_text' => $roomImage->alt_text,
                    'is_banner' => $roomImage->is_banner,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages,
        ]);
    }

    public function update(Request $request, Room $room, RoomImage $image): JsonResponse
    {
        $this->authorize('update', $room);
        
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'is_banner' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // If setting as banner, unset other banners
        if ($request->boolean('is_banner')) {
            $room->images()->where('id', '!=', $image->id)->update(['is_banner' => false]);
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

    public function destroy(Room $room, RoomImage $image): JsonResponse
    {
        $this->authorize('update', $room);
        
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

    public function reorder(Request $request, Room $room): JsonResponse
    {
        $this->authorize('update', $room);
        
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:room_images,id',
            'images.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->input('images') as $imageData) {
            RoomImage::where('id', $imageData['id'])
                ->where('room_id', $room->id)
                ->update(['sort_order' => $imageData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Images reordered successfully',
        ]);
    }
}
