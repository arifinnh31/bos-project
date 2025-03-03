<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\GineeUpdateOrCreate;

class GineeWebhookController extends Controller
{
    public function handler(Request $request)
    {
        $data = $request->all(); // or extract specific data needed for the job
        GineeUpdateOrCreate::dispatch($data);
    }

}
