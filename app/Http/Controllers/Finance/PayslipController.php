<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Payslip;
use App\Services\Finance\GeminiPayslipParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PayslipController extends Controller
{
    public function __construct(
        private readonly GeminiPayslipParser $parser,
    ) {}

    public function index(): Response
    {
        $payslips = Payslip::orderByDesc('periodo')->get();

        return Inertia::render('Admin/Finance/Payslips/Index', [
            'payslips' => $payslips,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Finance/Payslips/Create');
    }

    public function parse(Request $request): JsonResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $path = $request->file('archivo')->store('finance/payslips/tmp', 'local');

        try {
            $data = $this->parser->parse($path);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);

            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'parsed'       => $data,
            'archivo_path' => $path,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periodo'          => ['required', 'string', 'size:7'],
            'empresa'          => ['required', 'string', 'max:255'],
            'fecha_pago'       => ['nullable', 'date'],
            'banco'            => ['nullable', 'string', 'max:255'],
            'sueldo_basico'    => ['required', 'numeric', 'min:0'],
            'total_bruto'      => ['required', 'numeric', 'min:0'],
            'total_sin_aporte' => ['required', 'numeric', 'min:0'],
            'total_descuentos' => ['required', 'numeric', 'min:0'],
            'total_neto'       => ['required', 'numeric', 'min:0'],
            'conceptos'        => ['nullable', 'array'],
            'archivo_path'     => ['nullable', 'string'],
        ]);

        // Move from tmp to permanent location
        if (! empty($data['archivo_path']) && str_contains($data['archivo_path'], '/tmp/')) {
            $newPath = str_replace('/tmp/', '/', $data['archivo_path']);
            Storage::disk('local')->move($data['archivo_path'], $newPath);
            $data['archivo_path'] = $newPath;
        }

        Payslip::create($data);

        return redirect()->route('finance.payslips.index')
            ->with('success', 'Recibo guardado correctamente.');
    }

    public function show(Payslip $payslip): Response
    {
        return Inertia::render('Admin/Finance/Payslips/Show', [
            'payslip' => $payslip,
        ]);
    }

    public function destroy(Payslip $payslip): RedirectResponse
    {
        if ($payslip->archivo_path) {
            Storage::disk('local')->delete($payslip->archivo_path);
        }

        $payslip->delete();

        return redirect()->route('finance.payslips.index')
            ->with('success', 'Recibo eliminado.');
    }
}
