<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\PurchaseOrder;

class PdfService
{
    protected function getPdfSettings(): array
    {
        return [
            'font_size' => Setting::get('pdf_font_size', '12'),
            'font_family' => Setting::get('pdf_font_family', 'Arial'),
            'accent_color' => Setting::get('pdf_accent_color', '#3b82f6'),
            'header' => Setting::get('pdf_header', ''),
            'footer' => Setting::get('pdf_footer', ''),
            'custom_notes' => Setting::get('pdf_custom_notes', ''),
            'company_name' => Setting::get('company_name', 'PrintItMat'),
            'company_address' => Setting::get('company_address', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_logo' => Setting::get('company_logo', ''),
            'bank_details' => Setting::get('bank_details', ''),
        ];
    }

    public function generateQuotationPdf(Quotation $quotation)
    {
        $settings = $this->getPdfSettings();
        
        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'settings' => $settings,
        ]);

        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    public function generateInvoicePdf(Invoice $invoice)
    {
        $settings = $this->getPdfSettings();
        
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'settings' => $settings,
        ]);

        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    public function generatePurchaseOrderPdf(PurchaseOrder $po)
    {
        $settings = $this->getPdfSettings();
        
        $pdf = Pdf::loadView('pdf.purchase-order', [
            'po' => $po,
            'settings' => $settings,
        ]);

        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    public function downloadQuotation(Quotation $quotation)
    {
        return $this->generateQuotationPdf($quotation)
            ->download($quotation->quotation_number . '.pdf');
    }

    public function downloadInvoice(Invoice $invoice)
    {
        return $this->generateInvoicePdf($invoice)
            ->download($invoice->invoice_number . '.pdf');
    }

    public function downloadPurchaseOrder(PurchaseOrder $po)
    {
        return $this->generatePurchaseOrderPdf($po)
            ->download($po->po_number . '.pdf');
    }

    public function streamQuotation(Quotation $quotation)
    {
        return $this->generateQuotationPdf($quotation)
            ->stream($quotation->quotation_number . '.pdf');
    }

    public function streamInvoice(Invoice $invoice)
    {
        return $this->generateInvoicePdf($invoice)
            ->stream($invoice->invoice_number . '.pdf');
    }

    public function streamPurchaseOrder(PurchaseOrder $po)
    {
        return $this->generatePurchaseOrderPdf($po)
            ->stream($po->po_number . '.pdf');
    }
}

