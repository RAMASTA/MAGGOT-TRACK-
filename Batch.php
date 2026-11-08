<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'persiapan'  => 'Persiapan',
        'telur'      => 'Telur',
        'penetasan'  => 'Penetasan',
        'larva'      => 'Larva',
        'pra_pupa'   => 'Pra-pupa',
        'siap_panen' => 'Siap panen',
        'selesai'    => 'Selesai',
    ];

    protected $fillable = [
        'user_id', 'batch_code', 'batch_name', 'egg_date', 'hatch_date', 'initial_population',
        'harvest_method', 'target_harvest_date', 'actual_harvest_date', 'status', 'notes',
    ];

    protected $casts = [
        'egg_date' => 'date',
        'hatch_date' => 'date',
        'target_harvest_date' => 'date',
        'actual_harvest_date' => 'date',
        'initial_population' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function feedings() { return $this->hasMany(Feeding::class); }
    public function growthLogs() { return $this->hasMany(GrowthLog::class); }
    public function environmentLogs() { return $this->hasMany(EnvironmentLog::class); }
    public function harvests() { return $this->hasMany(Harvest::class); }
    public function photos() { return $this->hasMany(BatchPhoto::class); }

    public static function generateCode(): string
    {
        $prefix = 'BSF-' . now()->format('Ym') . '-';
        $n = static::withTrashed()->where('batch_code', 'like', $prefix . '%')->count() + 1;
        do {
            $code = $prefix . str_pad((string) $n++, 3, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('batch_code', $code)->exists());

        return $code;
    }

    public static function statusColor(string $s): string
    {
        return match ($s) {
            'persiapan'  => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
            'telur'      => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
            'penetasan'  => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200',
            'larva'      => 'bg-lime-100 text-lime-800 dark:bg-lime-900/40 dark:text-lime-200',
            'pra_pupa'   => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-200',
            'siap_panen' => 'bg-green-800 text-white',
            default      => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Umur batch (hari) sejak tanggal telur, berhenti di tanggal panen aktual. */
    public function getAgeDaysAttribute(): int
    {
        $end = $this->actual_harvest_date ?? today();
        return max(0, (int) $this->egg_date->diffInDays($end, false));
    }

    /** Sisa hari menuju target panen (negatif = terlambat). Null jika tidak relevan. */
    public function getDaysLeftAttribute(): ?int
    {
        if (! $this->target_harvest_date || $this->status === 'selesai') {
            return null;
        }
        return (int) today()->diffInDays($this->target_harvest_date, false);
    }

    public function getReadinessAttribute(): array
    {
        $slate = 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200';
        if ($this->status === 'selesai') {
            return ['key' => 'done', 'label' => 'Selesai', 'class' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200'];
        }
        $d = $this->days_left;
        if ($d !== null && $d < 0) {
            return ['key' => 'overdue', 'label' => 'Terlambat ' . abs($d) . ' hari', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200'];
        }
        if ($this->status === 'siap_panen' || $d === 0) {
            return ['key' => 'ready', 'label' => 'Siap panen', 'class' => 'bg-green-800 text-white'];
        }
        if ($d === null) {
            return ['key' => 'unscheduled', 'label' => 'Belum dijadwalkan', 'class' => $slate];
        }
        if ($d <= (int) config('maggottrack.alert_days')) {
            return ['key' => 'soon', 'label' => 'Mendekati panen', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'];
        }
        return ['key' => 'growing', 'label' => 'Dalam pertumbuhan', 'class' => 'bg-lime-100 text-lime-800 dark:bg-lime-900/40 dark:text-lime-200'];
    }
}
