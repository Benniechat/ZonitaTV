<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Channel;
use App\Models\Programme;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = Category::all()->keyBy('slug');

        $sportsCat = $categories['sports'] ?? null;
        $newsCat = $categories['news'] ?? null;
        $christianCat = $categories['christian-tv'] ?? null;
        $educationCat = $categories['education'] ?? null;
        $entertainmentCat = $categories['entertainment'] ?? null;
        $kidsCat = $categories['kids'] ?? null;
        $islamicCat = $categories['islamic'] ?? null;
        $moviesCat = $categories['movies'] ?? null;
        $comedyCat = $categories['comedy'] ?? null;

        // 1. Remediate Africa TV3 (ID 11) - Critical misclassification from Christian to Islamic
        $africaTv3 = Channel::where('slug', 'africa-tv3-720p-not-247')->first();
        if ($africaTv3) {
            if ($christianCat) {
                $africaTv3->categories()->detach($christianCat->id);
            }
            if ($islamicCat) {
                $africaTv3->categories()->syncWithoutDetaching([$islamicCat->id]);
            }

            $africaTv3->update([
                'tagline' => '24/7 African Islamic Television Broadcast',
                'description' => 'Official satellite and online streaming feed for Africa TV3, broadcasting Islamic knowledge, Holy Qur\'an recitations, Tafsir, and cultural family programming across Africa.',
            ]);

            // Update associated Christian prayer programme titles to authentic Islamic broadcasts
            $islamicTitles = [
                'Morning Devotion & Gospel Prayer Hour' => 'Morning Qur\'anic Recitation & Tafsir',
                'Word of Faith & Biblical Exposition' => 'Islamic Faith & Scholarly Exposition',
                'Worship Unplugged: Live Praise Encounters' => 'Spiritual Reflections & Nasheed Harmonies',
                'Evening Revival Sermon & Intercession' => 'Evening Seerah Lecture & Hadith Studies',
                'Night Watch Prayer & Quiet Reflections' => 'Nocturnal Reflections & Peaceful Duas',
            ];

            if ($islamicCat) {
                $progs = Programme::where('channel_id', $africaTv3->id)->get();
                foreach ($progs as $prog) {
                    $newTitle = $prog->title;
                    foreach ($islamicTitles as $oldFragment => $newFragment) {
                        if (str_contains($newTitle, $oldFragment)) {
                            $newTitle = str_replace($oldFragment, $newFragment, $newTitle);
                            break;
                        }
                    }
                    $prog->update([
                        'title' => $newTitle,
                        'category_id' => $islamicCat->id,
                        'description' => 'Verified 24/7 Islamic religious programming, scholarly teaching, and Holy Qur\'an reflections on Africa TV3.',
                    ]);
                }
            }
        }

        // 2. The Winlos (Christian & Comedy)
        if ($comedyCat) {
            $winlos = Channel::where('slug', 'the-winlos')->first();
            if ($winlos) {
                $winlos->categories()->syncWithoutDetaching([$comedyCat->id]);
            }
        }

        // 3. Movies channels missing 'movies' category
        if ($moviesCat) {
            $movieChannelSlugs = [
                'nollywood-gold',
                'bollywood-k-wave',
                'zonita-classics',
                'zonita-african-cinema',
                'itage-tv-480p',
                'zonita-arewa-movies',
                'zonita-islamic-cinema',
                'zonita-yoruba-islamic',
                'arewa24',
                'saira-movies',
                'fkd-productions',
                'kannywood-tv',
                'ali-nuhu-official-media',
                'adam-a-zango-official-media',
                'al-rahuz-tv',
                'yoruba-islamic-film-producers-syndicate',
            ];

            foreach ($movieChannelSlugs as $slug) {
                $ch = Channel::where('slug', $slug)->first();
                if ($ch) {
                    $ch->categories()->syncWithoutDetaching([$moviesCat->id]);
                }
            }
        }

        // 4. Entertainment category additions
        if ($entertainmentCat) {
            $entSlugs = ['arewa24'];
            foreach ($entSlugs as $slug) {
                $ch = Channel::where('slug', $slug)->first();
                if ($ch) {
                    $ch->categories()->syncWithoutDetaching([$entertainmentCat->id]);
                }
            }
        }

        // 5. Kids & Family additions
        if ($kidsCat) {
            $kidsSlugs = ['kids-wonder-tv', 'ado-tv-720p'];
            foreach ($kidsSlugs as $slug) {
                $ch = Channel::where('slug', $slug)->first();
                if ($ch) {
                    $ch->categories()->syncWithoutDetaching([$kidsCat->id]);
                }
            }
        }

        // 6. Christian TV additions
        if ($christianCat) {
            $ch = Channel::where('slug', 'christian-cartoons-bible-tv')->first();
            if ($ch) {
                $ch->categories()->syncWithoutDetaching([$christianCat->id]);
            }
        }

        // 7. Comedy category additions for major comedy-producing stars/studios
        if ($comedyCat) {
            $comedySlugs = ['faan-tv-funke-akindele', 'bimbo-ademoye-tv'];
            foreach ($comedySlugs as $slug) {
                $ch = Channel::where('slug', $slug)->first();
                if ($ch) {
                    $ch->categories()->syncWithoutDetaching([$comedyCat->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible logic
    }
};
