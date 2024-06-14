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

    public function make(string $url, string $filename = null)
    {
        try {
            $response = $this->client->get(env('PDF_SELF_URL').$url);

            dd($response);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Failed to load the page');
            }

            $html = $response->getBody()->getContents();

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
