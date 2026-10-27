<x-filament-panels::page>

  {{-- Period selector --}}
  <div class="flex flex-wrap gap-4 items-end mb-6 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
    <div>
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Period</label>
        <x-filament::input.select wire:model.live="period">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </x-filament::input.select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">From</label>
        <x-filament::input wrapper class="w-40">
            <input type="date" wire:model.live="from" class="border-none bg-transparent focus:ring-0 text-sm">
        </x-filament::input>
    </div>
  </div>

  @php
  $summary = $this->getRevenueSummary();
  $movieSummary = $this->getMovieViewsSummary(); // ➕ Loaded movie variables
  $rows = $this->getChartData();
  @endphp

  {{-- ── SUMMARY CARDS SECTION ── --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
      ['label' => 'Total Revenue',    'value' => 'LKR ' . number_format($summary['total_revenue'], 2), 'icon' => '💰', 'color' => 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800'],
      ['label' => 'Total Orders',     'value' => number_format($summary['total_orders']),               'icon' => '📦', 'color' => 'bg-blue-50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800'],

      // ➕ Injected Real-Time Movie Metrics Here
      ['label' => 'Total Movie Views', 'value' => number_format($movieSummary['total_views']),          'icon' => '🎬', 'color' => 'bg-purple-50 dark:bg-purple-950/20 border-purple-200 dark:border-purple-800'],
      ['label' => 'Unique Viewers',    'value' => number_format($movieSummary['unique_viewers']),       'icon' => '👥', 'color' => 'bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800'],
    ] as $card)
    <div class="p-5 rounded-xl border {{ $card['color'] }}">
      <div class="text-2xl mb-1">{{ $card['icon'] }}</div>
      <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ $card['label'] }}</div>
      <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</div>
    </div>
    @endforeach
  </div>

  {{-- Sales chart section --}}
  <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 mb-6"
       x-data="{
        init() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @js(collect($rows)->pluck('label')),
                    datasets: [{
                        label: 'Revenue (LKR)',
                        data: @js(collect($rows)->pluck('revenue')),
                        backgroundColor: '#c9a84c',
                        borderRadius: 4
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
       }">
    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Sales Revenue</h3>
    <canvas id="salesChart" height="100"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </div>

  {{-- ── DATA REPORTING TABLES ── --}}
  <div class="grid lg:grid-cols-3 gap-6">

    {{-- Top products --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
      <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Top Products</h3>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 dark:border-gray-800 text-gray-500 dark:text-gray-400">
            <th class="text-left py-2 font-medium">Product</th>
            <th class="text-right py-2 font-medium">Revenue</th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->getTopProducts() as $p)
          <tr class="border-b border-gray-50 dark:border-gray-800/50">
            <td class="py-2 font-medium text-gray-800 dark:text-gray-200">{{ Str::limit($p['product_name'], 20) }}</td>
            <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">LKR {{ number_format($p['total_revenue'], 2) }}</td>
          </tr>
          @empty
          <tr><td colspan="2" class="py-6 text-center text-gray-400">No data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Stock report --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
      <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Stock Levels</h3>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 dark:border-gray-800 text-gray-500 dark:text-gray-400">
            <th class="text-left py-2 font-medium">Product</th>
            <th class="text-right py-2 font-medium">Stock</th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->getStockReport() as $p)
          @php
            $isOut  = $p['stock'] === 0;
            $isLow  = !$isOut && $p['stock'] <= $p['low_stock_alert'];
            $color  = $isOut ? 'text-red-600 font-bold' : ($isLow ? 'text-amber-600 font-semibold' : 'text-green-600');
          @endphp
          <tr class="border-b border-gray-50 dark:border-gray-800/50">
            <td class="py-2 font-medium text-gray-800 dark:text-gray-200">{{ Str::limit($p['name'], 18) }}</td>
            <td class="py-2 text-right {{ $color }}">{{ $p['stock'] }}</td>
          </tr>
          @empty
          <tr><td colspan="2" class="py-6 text-center text-gray-400">No data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ➕ NEW TABLE: Top Movies Report --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
      <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Most Viewed Movies</h3>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 dark:border-gray-800 text-gray-500 dark:text-gray-400">
            <th class="text-left py-2 font-medium">Movie</th>
            <th class="text-right py-2 font-medium">IPs</th>
            <th class="text-right py-2 font-medium">Views</th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->getTopMovies() as $movie)
          <tr class="border-b border-gray-50 dark:border-gray-800/50">
            <td class="py-2 font-medium text-gray-800 dark:text-gray-200">{{ Str::limit($movie['title'], 18) }}</td>
            <td class="py-2 text-right text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $movie['unique_ips'] }}</td>
            <td class="py-2 text-right font-semibold text-purple-600 dark:text-purple-400">{{ number_format($movie['total_views']) }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="py-6 text-center text-gray-400">No views tracked yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>


  {{-- Summary cards --}}
  @php
  $summary = $this->getRevenueSummary();
  $movieSummary = $this->getMovieViewsSummary();
  $rows = $this->getChartData();
  @endphp

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Card 1 --}}
    <div class="p-5 rounded-xl border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="text-2xl mb-1">💰</div>
      <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Revenue</div>
      <div class="text-xl font-bold text-gray-900 dark:text-white">LKR {{ number_format($summary['total_revenue'], 2) }}</div>
    </div>

    {{-- Card 2 --}}
    <div class="p-5 rounded-xl border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="text-2xl mb-1">📦</div>
      <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Orders</div>
      <div class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['total_orders']) }}</div>
    </div>

    {{-- Card 3: Injected Movie Views --}}
    <div class="p-5 rounded-xl border bg-purple-50 dark:bg-purple-950/20 border-purple-200 dark:border-purple-800 shadow-sm">
      <div class="text-2xl mb-1">🎬</div>
      <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider mb-1">Total Movie Views</div>
      <div class="text-xl font-bold text-purple-900 dark:text-purple-200">{{ number_format($movieSummary['total_views'] ?? 0) }}</div>
    </div>

    {{-- Card 4: Injected Unique Streamers --}}
    <div class="p-5 rounded-xl border bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800 shadow-sm">
      <div class="text-2xl mb-1">👥</div>
      <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1">Unique Viewers</div>
      <div class="text-xl font-bold text-amber-900 dark:text-amber-200">{{ number_format($movieSummary['unique_viewers'] ?? 0) }}</div>
    </div>
  </div>

</x-filament-panels::page>
