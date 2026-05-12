<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceSummaryService;
use Inertia\Inertia;
use Inertia\Response;

class FinanceDashboardController extends Controller
{
    public function __construct(
        private readonly FinanceSummaryService $summary,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Finance/Dashboard', $this->summary->getDashboardData());
    }
}
