<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use App\Models\Email;
use App\Models\Customer;

class EmailService
{
    protected function configureSmtp()
    {
        $config = [
            'transport' => 'smtp',
            'host' => Setting::get('smtp_host'),
            'port' => Setting::get('smtp_port', 587),
            'encryption' => Setting::get('smtp_encryption', 'tls'),
            'username' => Setting::get('smtp_username'),
            'password' => Setting::get('smtp_password'),
        ];

        config(['mail.mailers.smtp' => $config]);
        config(['mail.from.address' => Setting::get('smtp_username')]);
        config(['mail.from.name' => Setting::get('company_name', 'PrintItMat')]);
    }

    public function sendEmail(string $to, string $subject, string $body, array $attachments = [], string $toName = null)
    {
        $this->configureSmtp();

        $signature = Setting::get('email_signature', '');
        $fullBody = $body . '<br><br>' . $signature;

        try {
            Mail::send([], [], function ($message) use ($to, $toName, $subject, $fullBody, $attachments) {
                $message->to($to, $toName)
                    ->subject($subject)
                    ->html($fullBody);

                foreach ($attachments as $attachment) {
                    if (is_string($attachment)) {
                        $message->attach($attachment);
                    } elseif (is_array($attachment)) {
                        $message->attach(
                            $attachment['path'], 
                            ['as' => $attachment['name'] ?? null, 'mime' => $attachment['mime'] ?? null]
                        );
                    }
                }
            });

            // Log sent email
            $customer = Customer::where('email', $to)->first();
            
            Email::create([
                'customer_id' => $customer?->id,
                'from_email' => Setting::get('smtp_username'),
                'from_name' => Setting::get('company_name'),
                'to_email' => $to,
                'subject' => $subject,
                'body_html' => $fullBody,
                'body_text' => strip_tags($fullBody),
                'is_sent' => true,
                'is_read' => true,
                'email_date' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendQuotationEmail($quotation, string $additionalMessage = '')
    {
        $pdfService = new PdfService();
        $pdf = $pdfService->generateQuotationPdf($quotation);
        
        $pdfPath = storage_path('app/temp/' . $quotation->quotation_number . '.pdf');
        $pdf->save($pdfPath);

        $subject = "Quotation {$quotation->quotation_number}";
        $body = "Dear {$quotation->customer_name},<br><br>";
        $body .= "Please find attached quotation {$quotation->quotation_number}.<br><br>";
        
        if ($additionalMessage) {
            $body .= $additionalMessage . "<br><br>";
        }
        
        $body .= "Thank you for your business.";

        $result = $this->sendEmail(
            $quotation->customer_email,
            $subject,
            $body,
            [$pdfPath],
            $quotation->customer_name
        );

        // Clean up temp file
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }

        if ($result) {
            $quotation->update(['sent_at' => now(), 'status' => 'sent']);
        }

        return $result;
    }

    public function sendInvoiceEmail($invoice, string $additionalMessage = '')
    {
        $pdfService = new PdfService();
        $pdf = $pdfService->generateInvoicePdf($invoice);
        
        $pdfPath = storage_path('app/temp/' . $invoice->invoice_number . '.pdf');
        $pdf->save($pdfPath);

        $subject = "Invoice {$invoice->invoice_number}";
        $body = "Dear {$invoice->customer_name},<br><br>";
        $body .= "Please find attached invoice {$invoice->invoice_number}.<br><br>";
        
        if ($additionalMessage) {
            $body .= $additionalMessage . "<br><br>";
        }
        
        $body .= "Thank you for your business.";

        $result = $this->sendEmail(
            $invoice->customer_email,
            $subject,
            $body,
            [$pdfPath],
            $invoice->customer_name
        );

        // Clean up temp file
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }

        if ($result) {
            $invoice->update(['sent_at' => now(), 'status' => 'sent']);
        }

        return $result;
    }

    public function sendPurchaseOrderEmail($po, string $additionalMessage = '')
    {
        $pdfService = new PdfService();
        $pdf = $pdfService->generatePurchaseOrderPdf($po);
        
        $pdfPath = storage_path('app/temp/' . $po->po_number . '.pdf');
        $pdf->save($pdfPath);

        $subject = "Purchase Order {$po->po_number}";
        $body = "Dear {$po->supplier_name},<br><br>";
        $body .= "Please find attached purchase order {$po->po_number}.<br><br>";
        
        if ($additionalMessage) {
            $body .= $additionalMessage . "<br><br>";
        }
        
        $body .= "Thank you.";

        $result = $this->sendEmail(
            $po->supplier_email,
            $subject,
            $body,
            [$pdfPath],
            $po->supplier_name
        );

        // Clean up temp file
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }

        if ($result) {
            $po->update(['sent_at' => now(), 'status' => 'sent']);
        }

        return $result;
    }
}

