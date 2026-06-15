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

  {{-- Summary cards --}}
  @php
  $summary = $this->getRevenueSummary();
  $rows = $this->getChartData();
  @endphp
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
      ['label' => 'Total Revenue',    'value' => 'LKR ' . number_format($summary['total_revenue'],    2), 'icon' => '💰', 'color' => 'bg-emerald-50 border-emerald-200'],
      ['label' => 'Total Orders',     'value' => number_format($summary['total_orders']),               'icon' => '📦', 'color' => 'bg-blue-50 border-blue-200'],
      ['label' => 'Total Discounts',  'value' => 'LKR ' . number_format($summary['total_discounts'],   2), 'icon' => '🎟', 'color' => 'bg-amber-50 border-amber-200'],
      ['label' => 'Avg Order Value',  'value' => 'LKR ' . number_format($summary['avg_order'],         2), 'icon' => '📊', 'color' => 'bg-purple-50 border-purple-200'],
    ] as $card)
    <div class="p-5 rounded-xl border {{ $card['color'] }}">
      <div class="text-2xl mb-1">{{ $card['icon'] }}</div>
      <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ $card['label'] }}</div>
      <div class="text-xl font-bold text-gray-900">{{ $card['value'] }}</div>
    </div>
    @endforeach
  </div>

  {{-- Sales chart placeholder (replace with real Chart.js or Filament chart widget) --}}
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

  <div class="grid lg:grid-cols-2 gap-6">

    {{-- Top products --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
      <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Top Products by Revenue</h3>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100">
            <th class="text-left py-2 text-gray-500 font-medium">#</th>
            <th class="text-left py-2 text-gray-500 font-medium">Product</th>
            <th class="text-right py-2 text-gray-500 font-medium">Sold</th>
            <th class="text-right py-2 text-gray-500 font-medium">Revenue</th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->getTopProducts() as $i => $p)
          <tr class="border-b border-gray-50">
            <td class="py-2 text-gray-400">{{ $i + 1 }}</td>
            <td class="py-2 font-medium text-gray-800">{{ $p['product_name'] }}</td>
            <td class="py-2 text-right text-gray-600">{{ $p['total_sold'] }}</td>
            <td class="py-2 text-right font-semibold">LKR {{ number_format($p['total_revenue'], 2) }}</td>
          </tr>
          @empty
          <tr><td colspan="4" class="py-6 text-center text-gray-400">No sales data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Stock report --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
      <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Stock Levels</h3>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100">
            <th class="text-left py-2 text-gray-500 font-medium">Product</th>
            <th class="text-left py-2 text-gray-500 font-medium">SKU</th>
            <th class="text-right py-2 text-gray-500 font-medium">Stock</th>
            <th class="text-right py-2 text-gray-500 font-medium">Alert</th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->getStockReport() as $p)
          @php
            $isOut  = $p['stock'] === 0;
            $isLow  = !$isOut && $p['stock'] <= $p['low_stock_alert'];
            $color  = $isOut ? 'text-red-600 font-bold' : ($isLow ? 'text-amber-600 font-semibold' : 'text-green-600');
          @endphp
          <tr class="border-b border-gray-50">
            <td class="py-2 font-medium text-gray-800">{{ Str::limit($p['name'], 28) }}</td>
            <td class="py-2 text-gray-400 font-mono text-xs">{{ $p['sku'] }}</td>
            <td class="py-2 text-right {{ $color }}">
              {{ $p['stock'] }}
              @if($isOut) <span class="ml-1 text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">OUT</span>
              @elseif($isLow) <span class="ml-1 text-xs bg-amber-100 text-amber-600 px-1.5 py-0.5 rounded">LOW</span>
              @endif
            </td>
            <td class="py-2 text-right text-gray-400">{{ $p['low_stock_alert'] }}</td>
          </tr>
          @empty
          <tr><td colspan="4" class="py-6 text-center text-gray-400">No products</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>

</x-filament-panels::page>
