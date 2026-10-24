<?php

namespace Tests\Unit;

use App\Models\BroadcastSession;
use App\Models\FixtureBroadcast;
use App\Models\SportsCompetition;
use App\Models\SportsFixture;
use App\Models\SportsTeam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportsBroadcastingSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_sports_entities_and_relationships_operate_correctly(): void
    {
        $competition = SportsCompetition::create([
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'code' => 'PL',
            'country' => 'England',
            'emblem_url' => 'https://example.com/pl.png',
        ]);

        $homeTeam = SportsTeam::create([
            'name' => 'Arsenal FC',
            'short_name' => 'Arsenal',
            'tla' => 'ARS',
            'slug' => 'arsenal-fc',
            'logo_url' => 'https://example.com/arsenal.png',
            'venue_name' => 'Emirates Stadium',
        ]);

        $awayTeam = SportsTeam::create([
            'name' => 'Chelsea FC',
            'short_name' => 'Chelsea',
            'tla' => 'CHE',
            'slug' => 'chelsea-fc',
            'logo_url' => 'https://example.com/chelsea.png',
            'venue_name' => 'Stamford Bridge',
        ]);

        $kickoffUtc = now()->addHours(2);
        $fixture = SportsFixture::create([
            'competition_id' => $competition->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'utc_kickoff' => $kickoffUtc,
            'lagos_kickoff' => $kickoffUtc->copy()->setTimezone('Africa/Lagos'),
            'status' => 'scheduled',
            'matchday' => 10,
            'venue' => 'Emirates Stadium',
        ]);

        $this->assertEquals('Arsenal FC vs Chelsea FC', $fixture->title);
        $this->assertFalse($fixture->is_live_now);
        $this->assertGreaterThan(0, $fixture->seconds_until_kickoff);
        $this->assertStringContainsString('WAT', $fixture->lagos_formatted);

        $broadcast = FixtureBroadcast::create([
            'fixture_id' => $fixture->id,
            'broadcaster_name' => 'SportyTV',
            'source_url' => 'https://sportybet.com/tv/live/12345',
            'operating_mode' => 'mode_a_link',
            'verification_status' => 'confirmed',
            'territory' => 'NGA',
        ]);

        $this->assertTrue($broadcast->isConfirmed());
        $this->assertFalse($broadcast->isAuthorisedForRebroadcasting());
        $this->assertEquals($broadcast->id, $fixture->sportyBroadcast->id);

        // Test Scopes
        $this->assertCount(1, SportsFixture::upcoming()->get());
        $this->assertCount(1, SportsFixture::withSportyTV()->get());
        $this->assertCount(0, SportsFixture::live()->get());
    }

    public function test_broadcast_session_records_state_transitions(): void
    {
        $session = BroadcastSession::create([
            'title' => 'Premier League Matchday 10',
            'operating_mode' => 'mode_a_link',
            'backend' => 'mock',
            'current_state' => 'idle',
        ]);

        $session->transitionTo('monitoring', 'ScheduledMonitoringJob', ['info' => 'Checking feeds']);
        $session->transitionTo('broadcast_verified', 'BroadcastVerificationJob', ['broadcaster' => 'SportyTV']);

        $session->refresh();
        $this->assertEquals('broadcast_verified', $session->current_state);
        $this->assertCount(2, $session->transitions);
        $this->assertEquals('monitoring', $session->transitions->last()->to_state);
    }
}
