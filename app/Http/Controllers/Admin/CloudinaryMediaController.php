<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Video;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CloudinaryMediaController extends Controller
{
    public function __construct(
        protected CloudinaryService $cloudinary
    ) {}

    /**
     * Display the Media Storage and Cloudinary assets manager.
     */
    public function index(Request $request): View
    {
        $channels = Channel::select('id', 'name', 'slug', 'logo_path', 'banner_path', 'updated_at')
            ->orderBy('name')
            ->get();

        $videos = Video::select('id', 'youtube_id', 'title', 'thumbnail_url', 'media_url', 'cloudinary_public_id', 'updated_at')
            ->latest('id')
            ->take(30)
            ->get();

        $config = [
            'cloud_name' => $this->cloudinary->getCloudName(),
            'api_key' => $this->cloudinary->getApiKey(),
            'upload_preset' => $this->cloudinary->getUploadPreset(),
            'is_configured' => $this->cloudinary->isConfigured(),
            'has_secret' => $this->cloudinary->hasApiSecret(),
        ];

        return view('admin.media.index', compact('channels', 'videos', 'config'));
    }

    /**
     * Generate signed upload parameters for Cloudinary Frontend Upload Widget.
     */
    public function signature(Request $request): JsonResponse
    {
        $folder = $request->input('folder', 'zonita');
        $tags = $request->input('tags');

        $params = [
            'folder' => $folder,
        ];

        if ($tags) {
            $params['tags'] = $tags;
        }

        $signatureData = $this->cloudinary->generateUploadSignature($params);

        return response()->json($signatureData);
    }

    /**
     * Direct file upload from the browser or forms to Cloudinary.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB limit
            'folder' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:logo,banner,thumbnail,media',
        ]);

        $folder = $request->input('folder') ?? match ($request->input('type')) {
            'logo' => 'zonita/logos',
            'banner' => 'zonita/banners',
            'thumbnail' => 'zonita/thumbnails',
            default => 'zonita/media',
        };

        $result = $this->cloudinary->upload($request->file('file'), $folder);

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Upload failed.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
            'format' => $result['format'],
            'width' => $result['width'],
            'height' => $result['height'],
        ]);
    }
}
