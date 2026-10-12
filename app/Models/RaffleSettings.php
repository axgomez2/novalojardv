<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaffleSettings extends Model
{
    protected $fillable = [
        'name',
        'regulation',
        'start_date',
        'end_date',
        'draw_date',
        'is_active',
        'starting_number',
        'current_number',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'draw_date' => 'date',
        'is_active' => 'boolean',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'name' => 'Promoção RDV Discos 2026',
            'starting_number' => 1000,
            'current_number' => 999, // Começa em 999 para que o primeiro getNextNumber() retorne 1000
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-05',
            'draw_date' => '2026-12-15',
        ]);
    }

    public function getNextNumber(): int
    {
        $next = $this->current_number + 1;
        $this->update(['current_number' => $next]);
        return $next;
    }

    /**
     * Retorna o próximo número que será gerado (para exibição)
     */
    public function getNextAvailableNumber(): int
    {
        return $this->current_number + 1;
    }
}
