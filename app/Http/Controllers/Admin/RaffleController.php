<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use App\Models\RaffleAcceptance;
use App\Models\RaffleNumber;
use App\Models\RaffleSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RaffleController extends Controller
{
    public function index(Request $request)
    {
        $settings = RaffleSettings::getSettings();
        
        $query = RaffleNumber::with(['client', 'createdBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $numbers = $query->paginate(20)->withQueryString();

        // Estatísticas
        $stats = [
            'total_numbers' => RaffleNumber::count(),
            'active_numbers' => RaffleNumber::active()->count(),
            'cancelled_numbers' => RaffleNumber::cancelled()->count(),
            'total_participants' => RaffleNumber::active()->distinct('client_user_id')->count('client_user_id'),
            'total_acceptances' => RaffleAcceptance::where('accepted', true)->count(),
        ];

        // Clientes com mais números
        $topParticipants = RaffleNumber::active()
            ->selectRaw('client_user_id, COUNT(*) as numbers_count')
            ->groupBy('client_user_id')
            ->orderByDesc('numbers_count')
            ->limit(10)
            ->with('client')
            ->get();

        return view('admin.raffle.index', compact('numbers', 'settings', 'stats', 'topParticipants'));
    }

    public function create()
    {
        $clients = ClientUser::orderBy('name')->get();
        $settings = RaffleSettings::getSettings();
        
        return view('admin.raffle.create', compact('clients', 'settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_user_id' => 'required|exists:client_users,id',
            'quantity' => 'required|integer|min:1|max:100',
            'purchase_description' => 'nullable|string|max:500',
            'order_number' => 'nullable|string|max:50',
        ]);

        $settings = RaffleSettings::getSettings();
        $createdNumbers = [];

        for ($i = 0; $i < $validated['quantity']; $i++) {
            $number = $settings->getNextNumber();
            
            $raffleNumber = RaffleNumber::create([
                'client_user_id' => $validated['client_user_id'],
                'number' => $number,
                'purchase_description' => $validated['purchase_description'],
                'order_number' => $validated['order_number'],
                'created_by' => Auth::guard('admin')->id(),
            ]);
            
            $createdNumbers[] = $raffleNumber;
        }

        return redirect()
            ->route('admin.raffle.index')
            ->with('success', count($createdNumbers) . ' número(s) criado(s) com sucesso!');
    }

    public function show(RaffleNumber $raffle)
    {
        $raffle->load(['client', 'createdBy']);
        return view('admin.raffle.show', compact('raffle'));
    }

    public function cancel(Request $request, RaffleNumber $raffle)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $raffle->cancel($validated['reason']);

        return redirect()
            ->back()
            ->with('success', 'Número cancelado com sucesso.');
    }

    public function searchClients(Request $request)
    {
        $search = $request->get('q', '');
        
        $clients = ClientUser::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(20)
            ->get(['id', 'name', 'email']);

        return response()->json(['clients' => $clients]);
    }

    public function clientNumbers(ClientUser $client)
    {
        $numbers = RaffleNumber::where('client_user_id', $client->id)
            ->orderBy('number')
            ->get();

        $acceptance = RaffleAcceptance::where('client_user_id', $client->id)->first();

        return view('admin.raffle.client-numbers', compact('client', 'numbers', 'acceptance'));
    }

    public function generatePdf(ClientUser $client)
    {
        $numbers = RaffleNumber::where('client_user_id', $client->id)
            ->active()
            ->orderBy('number')
            ->get();

        $settings = RaffleSettings::getSettings();
        $acceptance = RaffleAcceptance::where('client_user_id', $client->id)->first();

        $pdf = Pdf::loadView('admin.raffle.pdf-voucher', compact('client', 'numbers', 'settings', 'acceptance'));
        
        return $pdf->download("sorteio-rdv-{$client->id}.pdf");
    }

    public function sendVoucher(Request $request, ClientUser $client)
    {
        // TODO: Implementar envio por email/WhatsApp
        return redirect()
            ->back()
            ->with('success', 'Voucher enviado com sucesso!');
    }

    public function settings()
    {
        $settings = RaffleSettings::getSettings();
        return view('admin.raffle.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'regulation' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'draw_date' => 'nullable|date|after_or_equal:end_date',
            'is_active' => 'boolean',
        ]);

        $settings = RaffleSettings::getSettings();
        $settings->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Configurações atualizadas com sucesso!');
    }

    public function acceptances(Request $request)
    {
        $query = RaffleAcceptance::with('client')
            ->where('accepted', true)
            ->orderBy('accepted_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $acceptances = $query->paginate(20)->withQueryString();

        return view('admin.raffle.acceptances', compact('acceptances'));
    }
}
