<?php

namespace App\Http\Service;

use Mpdf\Mpdf;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Exception;

class PDFService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function make($html, string $filename = null)
    {
        try {
            $mpdf = new Mpdf();

            $mpdf->WriteHTML($html);

            if (!$filename) {
                $filename = 'report_' . uniqid() . '.pdf';
            } else {
                $filename = Str::slug($filename, '-') . '.pdf';
            }

            $filePath = 'reports/' . $filename;

            if (!Storage::exists('reports')) {
                Storage::makeDirectory('reports');
            }

            Storage::put($filePath, $mpdf->Output('', 'S'));

            return $filePath;
        } catch (Exception $e) {
            throw new Exception('Error generating PDF: ' . $e->getMessage());
        }
    }
}
