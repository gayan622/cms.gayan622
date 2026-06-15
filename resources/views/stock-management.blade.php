<x-filament-panels::page>

  {{-- Stock summary cards --}}
  @php $stats = $this->getStockStats(); @endphp
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="p-5 rounded-xl border border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-700">
      <div class="text-2xl mb-2">📦</div>
      <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Products</div>
      <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="p-5 rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20">
      <div class="text-2xl mb-2">✅</div>
      <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">In Stock</div>
      <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($stats['in_stock']) }}</div>
    </div>
    <div class="p-5 rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-900/20">
      <div class="text-2xl mb-2">⚠️</div>
      <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-1">Low Stock</div>
      <div class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ number_format($stats['low_stock']) }}</div>
    </div>
    <div class="p-5 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20">
      <div class="text-2xl mb-2">❌</div>
      <div class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Out of Stock</div>
      <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ number_format($stats['out_stock']) }}</div>
    </div>
  </div>

  {{-- Instructions --}}
  <div class="mb-4 p-4 rounded-xl bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800 text-sm text-blue-800 dark:text-blue-300">
    <strong>Stock Management:</strong> Use <em>Add Stock</em> for new inventory arrivals. Use <em>Remove Stock</em> for write-offs or corrections. The table auto-refreshes every 30 seconds.
  </div>

  {{ $this->table }}

</x-filament-panels::page>
