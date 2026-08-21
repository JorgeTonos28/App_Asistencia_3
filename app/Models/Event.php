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
            'active' => ['label' => 'En Curso / Activo', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-300'],
            'completed' => ['label' => 'Finalizado', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300 border-blue-300'],
            'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border-rose-300'],
            default => ['label' => 'Borrador', 'class' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border-slate-300'],
        };
    }
}
