<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RaffleAcceptance;
use App\Models\RaffleNumber;
use App\Models\RaffleSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RaffleController extends Controller
{
    /**
     * Retorna os números do sorteio do cliente autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $settings = RaffleSettings::getSettings();

        $numbers = RaffleNumber::where('client_user_id', $user->id)
            ->orderBy('number')
            ->get()
            ->map(function ($number) {
                return [
                    'id' => $number->id,
                    'number' => $number->number,
                    'formatted_number' => $number->formatted_number,
                    'purchase_description' => $number->purchase_description,
                    'order_number' => $number->order_number,
                    'status' => $number->status,
                    'created_at' => $number->created_at->format('d/m/Y H:i'),
                ];
            });

        $acceptance = RaffleAcceptance::where('client_user_id', $user->id)->first();

        return response()->json([
            'data' => [
                'numbers' => $numbers,
                'active_count' => $numbers->where('status', 'active')->count(),
                'total_count' => $numbers->count(),
                'acceptance' => $acceptance ? [
                    'accepted' => $acceptance->accepted,
                    'accepted_at' => $acceptance->accepted_at?->format('d/m/Y H:i'),
                ] : null,
                'settings' => [
                    'name' => $settings->name,
                    'is_active' => $settings->is_active,
                    'start_date' => $settings->start_date?->format('d/m/Y'),
                    'end_date' => $settings->end_date?->format('d/m/Y'),
                    'draw_date' => $settings->draw_date?->format('d/m/Y'),
                ],
                'regulation' => $settings->regulation,
            ],
        ]);
    }

    /**
     * Retorna o regulamento da promoção
     */
    public function regulation()
    {
        $settings = RaffleSettings::getSettings();

        return response()->json([
            'data' => [
                'name' => $settings->name,
                'regulation' => $settings->regulation,
                'start_date' => $settings->start_date?->format('d/m/Y'),
                'end_date' => $settings->end_date?->format('d/m/Y'),
                'draw_date' => $settings->draw_date?->format('d/m/Y'),
            ],
        ]);
    }

    /**
     * Registra o aceite dos termos da promoção
     */
    public function accept(Request $request)
    {
        $user = $request->user();

        $acceptance = RaffleAcceptance::updateOrCreate(
            ['client_user_id' => $user->id],
            [
                'accepted' => true,
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'message' => 'Termos aceitos com sucesso!',
            'data' => [
                'accepted' => true,
                'accepted_at' => $acceptance->accepted_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Gera PDF com os números do sorteio
     */
    public function downloadPdf(Request $request)
    {
        $user = $request->user();
        
        $numbers = RaffleNumber::where('client_user_id', $user->id)
            ->active()
            ->orderBy('number')
            ->get();

        $settings = RaffleSettings::getSettings();
        $acceptance = RaffleAcceptance::where('client_user_id', $user->id)->first();

        $pdf = Pdf::loadView('pdf.raffle-voucher', [
            'client' => $user,
            'numbers' => $numbers,
            'settings' => $settings,
            'acceptance' => $acceptance,
        ]);

        return $pdf->download("meus-numeros-sorteio-rdv.pdf");
    }
}
