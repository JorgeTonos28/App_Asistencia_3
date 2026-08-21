<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_code',
        'title',
        'description',
        'instructor',
        'location',
        'event_date',
        'start_time',
        'end_time',
        'status',
        'allow_registration',
        'require_document',
        'department_mode',
        'theme_color',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'allow_registration' => 'boolean',
        'require_document' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($event) {
            if (empty($event->access_code)) {
                $event->access_code = strtoupper(Str::random(8));
            }
            if (empty($event->department_mode)) {
                $event->department_mode = 'hidden';
            }
        });
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class)->orderBy('check_in_at', 'desc');
    }

    public function participants(): HasManyThrough
    {
        return $this->hasManyThrough(Participant::class, Attendance::class, 'event_id', 'id', 'id', 'participant_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRegistrationUrlAttribute(): string
    {
        return route('attendance.form', ['code' => $this->access_code]);
    }

    public function getProjectionUrlAttribute(): string
    {
        return route('attendance.qr', ['code' => $this->access_code]);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'active' => [
                'label' => 'En Curso / Activo',
                'class' => 'bg-emerald-600 text-white font-black shadow-sm shadow-emerald-600/25',
            ],
            'completed' => [
                'label' => 'Finalizado',
                'class' => 'bg-slate-700 text-white font-black shadow-sm',
            ],
            'cancelled' => [
                'label' => 'Cancelado',
                'class' => 'bg-rose-600 text-white font-black shadow-sm shadow-rose-600/25',
            ],
            default => [
                'label' => 'Borrador',
                'class' => 'bg-slate-500 text-white font-bold',
            ],
        };
    }
}
