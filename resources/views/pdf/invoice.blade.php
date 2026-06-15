<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice {{ $order->order_number }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1a2535; background: #fff; }
    .page { padding: 48px 52px; }

    /* Header */
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #0f1b2d; padding-bottom: 28px; }
    .brand h1 { font-size: 26px; font-weight: 700; color: #0f1b2d; letter-spacing: -0.5px; }
    .brand p  { font-size: 12px; color: #8da3be; margin-top: 3px; }
    .inv-meta { text-align: right; }
    .inv-meta .inv-label { font-size: 11px; font-weight: 700; color: #c9a84c; text-transform: uppercase; letter-spacing: 0.06em; }
    .inv-meta .inv-number { font-size: 20px; font-weight: 700; color: #0f1b2d; margin: 3px 0; }
    .inv-meta .inv-date   { font-size: 12px; color: #8da3be; }

    /* Status badge */
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 6px; }
    .status-paid      { background: #d1fae5; color: #065f46; }
    .status-pending   { background: #fef3c7; color: #92400e; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* Parties */
    .parties { display: flex; gap: 40px; margin-bottom: 36px; }
    .party { flex: 1; }
    .party-label { font-size: 10px; font-weight: 700; color: #c9a84c; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
    .party-name  { font-size: 15px; font-weight: 700; color: #0f1b2d; margin-bottom: 4px; }
    .party-line  { font-size: 12px; color: #4a5e75; line-height: 1.6; }

    /* Items table */
    table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
    thead tr { background: #0f1b2d; }
    thead th { padding: 10px 14px; font-size: 11px; font-weight: 600; color: #c9a84c; text-align: left; text-transform: uppercase; letter-spacing: 0.05em; }
    thead th:last-child { text-align: right; }
    tbody tr { border-bottom: 1px solid #edf0f4; }
    tbody tr:last-child { border-bottom: 2px solid #0f1b2d; }
    tbody td { padding: 12px 14px; font-size: 13px; color: #1a2535; vertical-align: top; }
    tbody td:last-child { text-align: right; font-weight: 600; }
    .item-name { font-weight: 600; color: #0f1b2d; }
    .item-sku  { font-size: 11px; color: #8da3be; margin-top: 2px; }

    /* Totals */
    .totals { margin-left: auto; width: 260px; margin-top: 0; }
    .totals-row { display: flex; justify-content: space-between; padding: 7px 14px; font-size: 13px; color: #4a5e75; }
    .totals-row.discount { color: #059669; }
    .totals-row.grand    { background: #0f1b2d; color: #c9a84c; font-size: 15px; font-weight: 700; padding: 12px 14px; border-radius: 0 0 6px 6px; }

    /* Footer */
    .footer { margin-top: 48px; border-top: 1px solid #edf0f4; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; }
    .footer-note { font-size: 11px; color: #8da3be; line-height: 1.6; }
    .footer-brand { font-size: 13px; font-weight: 700; color: #0f1b2d; }
  </style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header">
    <div class="brand">
      <h1>Gayan622 Online</h1>
      <p>Web Developer &amp; Designer · Negombo, Sri Lanka</p>
      <p style="margin-top:2px; font-size:12px; color:#8da3be;">gayan622@email.com</p>
    </div>
    <div class="inv-meta">
      <div class="inv-label">Invoice</div>
      <div class="inv-number">{{ $order->order_number }}</div>
      <div class="inv-date">{{ $order->created_at->format('d M Y') }}</div>
      <div>
        <span class="status-badge status-{{ $order->payment_status === 'paid' ? 'paid' : ($order->status === 'cancelled' ? 'cancelled' : 'pending') }}">
          {{ ucfirst($order->payment_status) }}
        </span>
      </div>
    </div>
  </div>

  <!-- Parties -->
  <div class="parties">
    <div class="party">
      <div class="party-label">Bill To</div>
      <div class="party-name">{{ $order->customer_name }}</div>
      <div class="party-line">{{ $order->customer_email }}</div>
      @if($order->customer_phone)
        <div class="party-line">{{ $order->customer_phone }}</div>
      @endif
      @if($order->customer_address)
        <div class="party-line">{{ $order->customer_address }}</div>
      @endif
      @if($order->customer_city)
        <div class="party-line">{{ $order->customer_city }}, {{ $order->customer_country }}</div>
      @endif
    </div>
    <div class="party">
      <div class="party-label">Payment Info</div>
      @if($order->payhere_payment_id)
        <div class="party-line"><strong>Method:</strong> PayHere</div>
        <div class="party-line"><strong>Payment ID:</strong> {{ $order->payhere_payment_id }}</div>
      @endif
      <div class="party-line"><strong>Currency:</strong> {{ $order->currency }}</div>
      @if($order->coupon_code)
        <div class="party-line"><strong>Coupon:</strong> {{ $order->coupon_code }}</div>
      @endif
    </div>
  </div>

  <!-- Items table -->
  <table>
    <thead>
      <tr>
        <th style="width:44%">Item</th>
        <th style="width:14%; text-align:right">Unit Price</th>
        <th style="width:12%; text-align:center">Qty</th>
        <th style="width:15%; text-align:right">Original</th>
        <th style="width:15%">Total (LKR)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order->items as $item)
      <tr>
        <td>
          <div class="item-name">{{ $item->product_name }}</div>
          <div class="item-sku">SKU: {{ $item->product_sku }}</div>
        </td>
        <td style="text-align:right">{{ number_format($item->unit_price, 2) }}</td>
        <td style="text-align:center">{{ $item->qty }}</td>
        <td style="text-align:right; color:#8da3be; text-decoration:{{ $item->unit_price < $item->original_price ? 'line-through' : 'none' }}">
          {{ number_format($item->original_price * $item->qty, 2) }}
        </td>
        <td>{{ number_format($item->total, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Totals -->
  <div class="totals">
    <div class="totals-row">
      <span>Subtotal</span>
      <span>LKR {{ number_format($order->subtotal, 2) }}</span>
    </div>
    @if($order->discount_amount > 0)
    <div class="totals-row discount">
      <span>Discount {{ $order->coupon_code ? "({$order->coupon_code})" : '' }}</span>
      <span>− LKR {{ number_format($order->discount_amount, 2) }}</span>
    </div>
    @endif
    <div class="totals-row grand">
      <span>Total</span>
      <span>LKR {{ number_format($order->total, 2) }}</span>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="footer-note">
      Thank you for your purchase!<br>
      For support, contact <strong>gayan622@email.com</strong>
    </div>
    <div class="footer-brand">gayan622.online</div>
  </div>

</div>
</body>
</html>
