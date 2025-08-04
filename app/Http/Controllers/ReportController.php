<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    //
    public function download(Event $event)
    {
        $stockMovements = $event->requisition
            ? $event->requisition->stockMovements()->with('item')->get()
            : collect();

        // Use the correct view path for the approved-event view
        $pdf = Pdf::loadView('internalPages::approved-event', compact('event', 'stockMovements'));

        // Set the report name to the event name, replacing spaces with dashes and making it safe
        $reportName = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $event->event_name)) . '-Pass.pdf';

        return $pdf->download($reportName);
    }
}
