import { v2 as cloudinary } from 'cloudinary';
import process from 'node:process';
import fs from 'node:fs';
import path from 'node:path';

// Attempt to load .env variables if not present
const envPath = path.resolve(process.cwd(), '.env');
if (fs.existsSync(envPath)) {
    const envContent = fs.readFileSync(envPath, 'utf-8');
    for (const line of envContent.split('\n')) {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) continue;
        const [k, ...v] = trimmed.split('=');
        if (k && !process.env[k.trim()]) {
            process.env[k.trim()] = v.join('=').trim().replace(/^["']|["']$/g, '');
        }
    }
}

const cloudName = process.env.CLOUDINARY_CLOUD_NAME || 'jcjugta9';
const apiKey = process.env.CLOUDINARY_API_KEY || '269289579262165';
const apiSecret = process.env.CLOUDINARY_API_SECRET || process.argv[2];

if (!apiSecret || apiSecret === '<your_api_secret>') {
    console.error('\x1b[31m%s\x1b[0m', 'Error: Cloudinary API Secret is required.');
    console.log('\nPlease supply your API secret either by:');
    console.log('1. Setting CLOUDINARY_API_SECRET in your .env file');
    console.log('2. Running: node scripts/cloudinary-test.mjs <your_api_secret>\n');
    process.exit(1);
}

cloudinary.config({
    cloud_name: cloudName,
    api_key: apiKey,
    api_secret: apiSecret,
    secure: true,
});

(async function () {
    try {
        console.log(`Connecting to Cloudinary (${cloudName})...`);

        // Upload an image
        const uploadResult = await cloudinary.uploader.upload(
            'https://res.cloudinary.com/demo/image/upload/getting-started/shoes.jpg',
            {
                public_id: 'shoes',
                overwrite: true,
            }
        );

        console.log('\n[SUCCESS] Image Uploaded:');
        console.log(uploadResult);

        // Optimize delivery by resizing and applying auto-format and auto-quality
        const optimizeUrl = cloudinary.url('shoes', {
            fetch_format: 'auto',
            quality: 'auto',
        });
        console.log('\n[Optimized Delivery URL]:', optimizeUrl);

        // Transform the image: auto-crop to square aspect_ratio
        const autoCropUrl = cloudinary.url('shoes', {
            crop: 'auto',
            gravity: 'auto',
            width: 500,
            height: 500,
        });
        console.log('\n[Auto-Cropped URL (500x500)]:', autoCropUrl);
    } catch (error) {
        console.error('\x1b[31m[Cloudinary Error]\x1b[0m', error);
    }
})();
