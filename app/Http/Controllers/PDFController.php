<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Production;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function InvoicePdf($invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        $total_in_words = self::numToWords($invoice->total);
        $fop = $invoice->fop;
        //dd($order);
        //$pdf = Pdf::loadView('demopdf', compact('order'));
        $pdf = FacadePdf::loadView('PDF.new_invoice', compact('invoice','total_in_words', 'fop'));
        return $pdf->download($invoice->invoice_number.'.pdf');
       // return $pdf->download('order.pdf');
    }

    public function ProductionDetail($production_id){
        $production = Production::find($production_id);

        if (!$production) {
            return response()->json(['error' => 'Production not found'], 404);
        }

        $pdf = FacadePdf::loadView('PDF.production_detail', compact('production'));
        return $pdf->download('production_detail_' . $production->id . '.pdf');
    }

    public static function numToWords($number)
    {
        // Проста реалізація або використайте сторонній пакет
        return trim((new \NumberFormatter('uk', \NumberFormatter::SPELLOUT))->format($number)) . ' гривень, 00 коп.';
    }


}
