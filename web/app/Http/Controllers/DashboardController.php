<?php

namespace App\Http\Controllers;

use App\Services\DummyDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DummyDataService $data): View
    {
        return view('pages.dashboard', [
            ...$data->dashboard((string) $request->query('q', '')),
            'today' => Carbon::now()->locale('id')->translatedFormat('j F Y'),
            'search' => (string) $request->query('q', ''),
            'title' => 'Dashboard',
            'breadcrumb' => 'Dashboard',
        ]);
    }
}
