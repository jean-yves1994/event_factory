<?php

use App\Http\Controllers\ReportController;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/approved-events/{event}/report', function (Event $event) {
    $movements = \App\Models\StockMovement::whereHas('requisition', function ($query) use ($event) {
        $query->where('event_id', $event->id)->where('status', 'approved');
    })->with('item')->get();

    $pdf = Pdf::loadView('reports.approved-event', [
        'event' => $event,
        'movements' => $movements,
    ]);

    return $pdf->download("Approved_Event_{$event->id}_Report.pdf");
})->name('approved-events.report');

Route::get('/approved-events/{event}/report', [ReportController::class, 'download'])->name('approved-events.report');
