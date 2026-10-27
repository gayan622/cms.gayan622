<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Section 1: Top Movies Leaderboard --}}
        <div class="p-6 bg-white rounded-xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800 shadow-sm">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <x-filament::icon icon="heroicon-m-film" class="w-5 h-5 text-primary-500" />
                Top 10 Most Watched Movies
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-gray-500 font-medium">
                            <th class="pb-3">TMDB NAME / ID</th>
                            <th class="pb-3 text-right">Total Streams</th>
                            <th class="pb-3 text-right">Unique Viewers</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($topMovies as $movie)
                            <tr>
                                <td class="py-3 font-mono font-medium text-primary-600 dark:text-primary-400">{{ $movie->title }} [#{{ $movie->tmdb_id }}]</td>
                                <td class="py-3 text-right font-semibold">{{ number_format($movie->total_views) }}</td>
                                <td class="py-3 text-right text-gray-500">{{ number_format($movie->unique_viewers) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-400">No streaming records logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 2: Live Activity View Log --}}
        <div class="p-6 bg-white rounded-xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800 shadow-sm">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <x-filament::icon icon="heroicon-m-clock" class="w-5 h-5 text-success-500" />
                Recent Live Stream Activities
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-gray-500 font-medium">
                            <th class="pb-3">Client IP Address</th>
                            <th class="pb-3">Movie Name / ID</th>
                            <th class="pb-3 text-right">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recentViews as $view)
                            <tr>
                                <td class="py-3 font-mono text-gray-600 dark:text-gray-400">{{ $view->ip_address ?? 'Anonymous' }}</td>
                                <td class="py-3 font-semibold">{{ $view->movie_title }} [#{{ $view->tmdb_id }}] </td>
                                <td class="py-3 text-right text-gray-400 text-xs">
                                    {{ \Carbon\Carbon::parse($view->created_at)->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-400">No recent visitor activity.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>
