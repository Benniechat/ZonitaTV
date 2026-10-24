<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected string $cloudName;
    protected string $apiKey;
    protected ?string $apiSecret;
    protected string $uploadPreset;

    public function __construct()
    {
        $this->cloudName = (string) config('services.cloudinary.cloud_name', 'jcjugta9');
        $this->apiKey = (string) config('services.cloudinary.api_key', '269289579262165');
        $this->apiSecret = config('services.cloudinary.api_secret') ? (string) config('services.cloudinary.api_secret') : null;
        $this->uploadPreset = (string) config('services.cloudinary.upload_preset', 'zonita_uploads');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->cloudName) && ! empty($this->apiKey);
    }

    public function hasApiSecret(): bool
    {
        return ! empty($this->apiSecret);
    }

    public function getCloudName(): string
    {
        return $this->cloudName;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getUploadPreset(): string
    {
        return $this->uploadPreset;
    }

    /**
     * Generate signed upload parameters for Cloudinary Frontend Upload Widget.
     *
     * @param array<string, mixed> $customParams
     * @return array<string, mixed>
     */
    public function generateUploadSignature(array $customParams = []): array
    {
        $timestamp = time();

        $paramsToSign = array_merge([
            'timestamp' => $timestamp,
        ], $customParams);

        // Exclude parameters not included in signature
        unset($paramsToSign['file'], $paramsToSign['cloud_name'], $paramsToSign['resource_type'], $paramsToSign['api_key']);

        ksort($paramsToSign);

        $paramPairs = [];
        foreach ($paramsToSign as $key => $val) {
            if ($val !== null && $val !== '') {
                $paramPairs[] = "{$key}={$val}";
            }
        }
        $stringToSign = implode('&', $paramPairs);

        $signature = $this->apiSecret ? sha1($stringToSign . $this->apiSecret) : null;

        return [
            'signature' => $signature,
            'timestamp' => $timestamp,
            'api_key' => $this->apiKey,
            'cloud_name' => $this->cloudName,
            'upload_preset' => $this->uploadPreset,
            'is_signed' => (bool) $signature,
        ];
    }

    /**
     * Direct upload from server (e.g. from Controller or CLI).
     *
     * @param string|UploadedFile $file
     * @param string $folder
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function upload(string|UploadedFile $file, string $folder = 'zonita', array $options = []): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Cloudinary is not configured with cloud_name and api_key.',
            ];
        }

        $apiUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/auto/upload";

        $postData = array_merge([
            'folder' => $folder,
        ], $options);

        if ($this->hasApiSecret()) {
            $postData['timestamp'] = time();
            $paramsToSign = $postData;
            ksort($paramsToSign);
            $pairs = [];
            foreach ($paramsToSign as $k => $v) {
                if ($v !== null && $v !== '') {
                    $pairs[] = "{$k}={$v}";
                }
            }
            $postData['signature'] = sha1(implode('&', $pairs) . $this->apiSecret);
            $postData['api_key'] = $this->apiKey;
        } else {
            $postData['upload_preset'] = $this->uploadPreset;
        }

        try {
            $request = Http::timeout(60);

            if ($file instanceof UploadedFile) {
                $response = $request->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )->post($apiUrl, $postData);
            } else {
                $postData['file'] = $file;
                $response = $request->post($apiUrl, $postData);
            }

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'secure_url' => $data['secure_url'] ?? null,
                    'public_id' => $data['public_id'] ?? null,
                    'format' => $data['format'] ?? null,
                    'width' => $data['width'] ?? null,
                    'height' => $data['height'] ?? null,
                    'bytes' => $data['bytes'] ?? null,
                    'raw' => $data,
                ];
            }

            Log::error('Cloudinary upload failure', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Cloudinary upload failed with HTTP ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('Cloudinary upload exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate an optimized deliver URL for an asset.
     *
     * @param string $publicId
     * @param array<string, mixed> $transformations
     * @return string
     */
    public function url(string $publicId, array $transformations = []): string
    {
        $transString = $this->buildTransformationString(array_merge([
            'fetch_format' => 'auto',
            'quality' => 'auto',
        ], $transformations));

        return "https://res.cloudinary.com/{$this->cloudName}/image/upload/{$transString}/{$publicId}";
    }

    /**
     * Generate a Cloudinary fetch URL for external media (e.g. YouTube thumbnails).
     *
     * @param string $externalUrl
     * @param array<string, mixed> $transformations
     * @return string
     */
    public function fetchUrl(string $externalUrl, array $transformations = []): string
    {
        $transString = $this->buildTransformationString(array_merge([
            'fetch_format' => 'auto',
            'quality' => 'auto',
        ], $transformations));

        return "https://res.cloudinary.com/{$this->cloudName}/image/fetch/{$transString}/" . urlencode($externalUrl);
    }

    protected function buildTransformationString(array $trans): string
    {
        $parts = [];

        if (! empty($trans['width'])) {
            $parts[] = 'w_' . $trans['width'];
        }
        if (! empty($trans['height'])) {
            $parts[] = 'h_' . $trans['height'];
        }
        if (! empty($trans['crop'])) {
            $parts[] = 'c_' . $trans['crop'];
        }
        if (! empty($trans['gravity'])) {
            $parts[] = 'g_' . $trans['gravity'];
        }
        if (! empty($trans['fetch_format'])) {
            $parts[] = 'f_' . $trans['fetch_format'];
        }
        if (! empty($trans['quality'])) {
            $parts[] = 'q_' . $trans['quality'];
        }

        return implode(',', $parts);
    }
}
