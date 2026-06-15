<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceService
{
    public function download(Order $order): Response
    {
        $pdf = Pdf::loadView('pdf.invoice', ['order' => $order])
            ->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }

    public function stream(Order $order): Response
    {
        $pdf = Pdf::loadView('pdf.invoice', ['order' => $order])
            ->setPaper('a4', 'portrait');

        return $pdf->stream("invoice-{$order->order_number}.pdf");
    }
}
