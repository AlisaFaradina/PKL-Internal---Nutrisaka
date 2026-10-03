<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;

class AboutController extends Controller
{
    public function __construct(
        protected LicenseService $licenseService
    ) {}

    public function index()
    {
        $deviceDetails = $this->licenseService->getDeviceDetails();
        $license = \App\Models\License::first();
        $trialStatus = $this->licenseService->getTrialStatus();
        $currentMode = \App\Models\AppSetting::getApplicationMode();

        return view('about.index', compact('deviceDetails', 'license', 'trialStatus', 'currentMode'));
    }

    /**
     * Mengubah mode aplikasi antara Mode Asli dan Mode Demo.
     */
    public function switchMode(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'mode' => 'required|in:real,demo',
        ]);

        $mode = $request->input('mode');
        \App\Models\AppSetting::setApplicationMode($mode);

        $msg = $mode === 'demo'
            ? 'Beralih ke Mode Demo (Simulasi): Aplikasi kini menampilkan data contoh untuk demonstrasi.'
            : 'Beralih ke Mode Asli (Operasional): Aplikasi kini menggunakan data operasional nyata.';

        return redirect()->route('about.index')->with('success', $msg);
    }
}
