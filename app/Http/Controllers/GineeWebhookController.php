<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\GineeProductJob;
use Illuminate\Support\Facades\Log;

class GineeWebhookController extends Controller
{
    public function handler(Request $request)
    {
        // Log semua request yang masuk
        Log::info('Ginee Webhook Received:', $request->all());

        // Ambil data dari request
        $data = $request->all();
        dd($data);

        // Pastikan data yang diperlukan ada
        if (!isset($data['id']) || !isset($data['entity']) || !isset($data['action']) || !isset($data['payload'])) {
            Log::error('Invalid webhook data received:', $data);
            return response('Invalid data', 400);
        }

        // Dispatch job untuk menangani aksi
        GineeProductJob::dispatch($data)->onQueue('ginee-products');
        Log::info('Dispatched GineeProductJob for action: ' . $data['action']);

        return response('Webhook received', 200);
    }
}