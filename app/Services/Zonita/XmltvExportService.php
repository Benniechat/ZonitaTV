<?php

namespace App\Services\Zonita;

use App\Models\Channel;
use App\Models\ScheduleEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class XmltvExportService
{
    /**
     * Generate an RFC-compliant XMLTV document.
     */
    public function generateXml(
        ?Collection $channels = null,
        ?Carbon $fromTime = null,
        ?Carbon $toTime = null,
        string $timezone = 'UTC'
    ): string {
        $from = $fromTime ?: now('UTC')->subHours(6);
        $to = $toTime ?: now('UTC')->addDays(7);

        if (! $channels || $channels->isEmpty()) {
            $channels = Channel::where('is_active', true)
                ->where('is_published', true)
                ->with(['categories', 'primaryStream'])
                ->get();
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><tv generator-info-name="Zonita TV XMLTV Engine" generator-info-url="'.htmlspecialchars(config('app.url'), ENT_XML1).'"></tv>');

        // 1. Channels metadata
        foreach ($channels as $channel) {
            $channelId = $channel->tvg_id ?: StrStudlyOrSlug($channel);
            $chanNode = $xml->addChild('channel');
            $chanNode->addAttribute('id', $channelId);

            $chanNode->addChild('display-name', htmlspecialchars($channel->name, ENT_XML1));
            $chanNode->addChild('display-name', 'CH '.$channel->display_number);

            if ($channel->logo_url) {
                $icon = $chanNode->addChild('icon');
                $icon->addAttribute('src', $channel->logo_url);
            }

            $chanNode->addChild('url', htmlspecialchars(url("/channel/{$channel->slug}"), ENT_XML1));
        }

        // 2. Programmes & Schedule Entries
        $channelIds = $channels->pluck('id');
        $entries = ScheduleEntry::whereIn('channel_id', $channelIds)
            ->where('end_time', '>=', $from)
            ->where('start_time', '<=', $to)
            ->orderBy('start_time')
            ->with(['channel.categories', 'video'])
            ->get();

        foreach ($entries as $entry) {
            $channel = $entry->channel;
            if (! $channel) {
                continue;
            }

            $channelTvgId = $channel->tvg_id ?: StrStudlyOrSlug($channel);
            $startFormatted = $this->formatXmltvTimestamp($entry->start_time);
            $stopFormatted = $this->formatXmltvTimestamp($entry->end_time);

            $progNode = $xml->addChild('programme');
            $progNode->addAttribute('start', $startFormatted);
            $progNode->addAttribute('stop', $stopFormatted);
            $progNode->addAttribute('channel', $channelTvgId);

            $titleNode = $progNode->addChild('title', htmlspecialchars($entry->title, ENT_XML1));
            $titleNode->addAttribute('lang', $channel->language_code ?: 'en');

            if ($entry->description) {
                $descNode = $progNode->addChild('desc', htmlspecialchars($entry->description, ENT_XML1));
                $descNode->addAttribute('lang', $channel->language_code ?: 'en');
            }

            $catName = $channel->categories->first()?->name ?? 'General';
            $catNode = $progNode->addChild('category', htmlspecialchars($catName, ENT_XML1));
            $catNode->addAttribute('lang', 'en');

            if ($entry->video && $entry->video->thumbnail_url) {
                $icon = $progNode->addChild('icon');
                $icon->addAttribute('src', $entry->video->thumbnail_url);
            }
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * Format Carbon timestamp into standard XMLTV format: YYYYMMDDHHMMSS +0000
     */
    public function formatXmltvTimestamp(Carbon|string $datetime): string
    {
        $dt = is_string($datetime) ? Carbon::parse($datetime, 'UTC') : $datetime->copy()->utc();

        return $dt->format('YmdHis').' +0000';
    }
}

function StrStudlyOrSlug(Channel $channel): string
{
    return Str::studly($channel->name).'.zonita';
}
