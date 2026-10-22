@extends('admin.layout')

@section('title', 'Stream Health Diagnostics - Zoniva TV')

@section('admin_content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">Stream Diagnostics & Health Verification</h1>
        <p class="text-xs text-gray-400">Technical reachability log. (Note: Reachability checks do not substitute legal distribution rights).</p>
    </div>

    <div class="bg-[#12151c] border border-gray-800 rounded-2xl overflow-hidden shadow">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-900/60 text-gray-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                <tr>
                    <th class="py-3.5 px-4">Channel</th>
                    <th class="py-3.5 px-4">Stream Endpoint</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Latency</th>
                    <th class="py-3.5 px-4">Last Checked</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($recentChecks as $check)
                    <tr class="hover:bg-gray-900/40 transition">
                        <td class="py-3 px-4 font-bold text-white">
                            {{ $check->channel->name ?? 'Channel #' . $check->channel_id }}
                        </td>
                        <td class="py-3 px-4 font-mono text-[11px] text-gray-400 max-w-xs truncate">
                            {{ $check->stream->stream_url ?? ($check->channel->official_destination_url ?? 'N/A') }}
                        </td>
                        <td class="py-3 px-4">
                            @if($check->is_reachable)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    {{ $check->http_status_code }} OK
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/20">
                                    {{ $check->http_status_code ?: 'FAIL' }} Offline
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-gray-400">
                            {{ $check->response_time_ms ? $check->response_time_ms . ' ms' : 'N/A' }}
                        </td>
                        <td class="py-3 px-4 text-gray-500">
                            {{ $check->checked_at->diffForHumans() }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if($check->channel_stream_id)
                                <form action="{{ route('admin.diagnostics.check', $check->channel_stream_id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-300 font-semibold">
                                        Re-verify
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-600">N/A</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>
        {{ $recentChecks->links() }}
    </div>

</div>

@endsection
