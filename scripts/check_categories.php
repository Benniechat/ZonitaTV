<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use App\Models\Category;
use App\Models\Channel;
use Illuminate\Contracts\Console\Kernel;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$channels = Channel::with('categories')->get();

echo 'Total Channels: '.$channels->count()."\n";
echo "--------------------------------------------------------\n";

$byCategory = [];
$uncategorized = [];
$misaligned = [];

foreach ($channels as $channel) {
    $catNames = $channel->categories->pluck('name')->toArray();
    $catSlugs = $channel->categories->pluck('slug')->toArray();
    $name = $channel->name;
    $slug = $channel->slug;
    $url = $channel->official_destination_url;

    if (empty($catNames)) {
        $uncategorized[] = [
            'id' => $channel->id,
            'name' => $name,
            'slug' => $slug,
            'url' => $url,
        ];
    } else {
        foreach ($catNames as $cName) {
            $byCategory[$cName][] = $name;
        }
    }

    // Inspect content vs category rules
    $nameLower = strtolower($name);
    $slugLower = strtolower($slug);

    // Check sports
    if (str_contains($nameLower, 'sport') || str_contains($nameLower, 'football') || str_contains($nameLower, 'fifa') || str_contains($nameLower, 'matchday')) {
        if (! in_array('sports', $catSlugs)) {
            $misaligned[] = [
                'channel' => $name,
                'current' => implode(', ', $catNames),
                'expected' => 'Sports',
                'reason' => 'Channel name indicates Sports but missing sports category',
            ];
        }
    }

    // Check news (word boundaries to avoid false positives on 'documentary', 'montana', 'goodnews')
    if (preg_match('/\b(news|tvc|arise|nta)\b/i', $nameLower)) {
        if (! in_array('news', $catSlugs) && ! str_contains($nameLower, 'goodnews') && ! str_contains($nameLower, 'gospel') && ! str_contains($nameLower, 'comedy')) {
            $misaligned[] = [
                'channel' => $name,
                'current' => implode(', ', $catNames),
                'expected' => 'News',
                'reason' => 'Channel name indicates News but missing news category',
            ];
        }
    }

    // Check Christian / Gospel
    if (str_contains($nameLower, 'gospel') || str_contains($nameLower, 'church') || str_contains($nameLower, 'pastor') || str_contains($nameLower, 'faith') || str_contains($nameLower, 'worship') || str_contains($nameLower, 'bible') || str_contains($nameLower, 'christ') || str_contains($nameLower, 'emmanuel') || str_contains($nameLower, 'dove')) {
        if (in_array('islamic-tv', $catSlugs) || in_array('islamic', $catSlugs)) {
            $misaligned[] = [
                'channel' => $name,
                'current' => implode(', ', $catNames),
                'expected' => 'Christian',
                'reason' => 'Christian/Gospel content placed in Islamic category',
            ];
        }
    }

    // Check Islamic
    if (str_contains($nameLower, 'islam') || str_contains($nameLower, 'quran') || str_contains($nameLower, 'sunnah') || str_contains($nameLower, 'hausa islamic') || str_contains($nameLower, 'dawah') || str_contains($nameLower, 'sheikh') || str_contains($nameLower, 'africa tv3')) {
        if (in_array('christian-tv', $catSlugs) || in_array('gospel', $catSlugs)) {
            $misaligned[] = [
                'channel' => $name,
                'current' => implode(', ', $catNames),
                'expected' => 'Islamic',
                'reason' => 'Islamic content placed in Christian category',
            ];
        }
    }

    // Check Comedy
    if (str_contains($nameLower, 'comedy') || str_contains($nameLower, 'skit') || str_contains($nameLower, 'stand-up') || str_contains($nameLower, 'sitcom') || str_contains($nameLower, 'humor') || str_contains($nameLower, 'prank')) {
        if (! in_array('comedy', $catSlugs) && ! in_array('entertainment', $catSlugs)) {
            $misaligned[] = [
                'channel' => $name,
                'current' => implode(', ', $catNames),
                'expected' => 'Comedy / Entertainment',
                'reason' => 'Comedy content missing comedy/entertainment category',
            ];
        }
    }

    // Check Nollywood / Movies
    if (str_contains($nameLower, 'nollywood') || str_contains($nameLower, 'yoruba cinema') || str_contains($nameLower, 'igbo cinema') || str_contains($nameLower, 'epic movies')) {
        if (! in_array('nollywood', $catSlugs) && ! in_array('entertainment', $catSlugs) && ! in_array('movies', $catSlugs)) {
            $misaligned[] = [
                'channel' => $name,
                'current' => implode(', ', $catNames),
                'expected' => 'Nollywood / Movies',
                'reason' => 'Nollywood content missing nollywood/movies category',
            ];
        }
    }
}

echo "CATEGORY BREAKDOWN:\n";
foreach ($byCategory as $cat => $list) {
    echo " - {$cat}: ".count($list)." channels\n";
}

echo "\nUNCATEGORIZED CHANNELS (".count($uncategorized)."):\n";
foreach ($uncategorized as $u) {
    echo " - [{$u['id']}] {$u['name']} ({$u['slug']})\n";
}

echo "\nPOTENTIAL MISALIGNMENTS (".count($misaligned)."):\n";
foreach ($misaligned as $m) {
    echo " - {$m['channel']} | Current: [{$m['current']}] -> Expected: {$m['expected']} ({$m['reason']})\n";
}
