<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Queue;

class QrCodeController extends Controller
{
    /**
     * Affiche la page de projection du QR code
     *
     * @param  string  $code
     * @return \Illuminate\View\View
     */
    public function show($code)
    {
        $queue = Queue::where('code', $code)->firstOrFail();
        return view('public.qrcode-display', compact('queue'));
    }
}
