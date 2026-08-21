<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
        'parent_id',
        'session_number',
        'access_code',
        'title',
        'description',
        'instructor',
        'instructors',
        'location',
        'event_date',
        'start_time',
        'end_time',
        'status',
        'allow_registration',
        'override_closing',
        'require_document',
        'department_mode',
        'theme_color',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'session_number' => 'integer',
        'instructors' => 'array',
        'allow_registration' => 'boolean',
        'override_closing' => 'boolean',
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
            if (empty($event->session_number)) {
                $event->session_number = 1;
            }
        });
    }

    // --- Relaciones de Series y Sesiones Recurrentes ---

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_id')
            ->orderBy('session_number', 'asc')
            ->orderBy('event_date', 'asc');
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

    // --- Helpers de Series y Recurrencia ---

    public function getRootEvent(): Event
    {
        return $this->parent_id && $this->parent ? $this->parent : $this;
    }

    public function getAllSeriesEvents(): Collection
    {
        $root = $this->getRootEvent();
        $sessions = $root->sessions()->withCount('attendances')->get();
        return (new Collection([$root]))->merge($sessions)->sortBy('session_number');
    }

    public function isRecurring(): bool
    {
        return $this->parent_id !== null || $this->sessions()->exists();
    }

    public function totalSeriesSessions(): int
    {
        return $this->getRootEvent()->sessions()->count() + 1;
    }

    public function getSeriesSessionLabelAttribute(): string
    {
        if (!$this->isRecurring()) {
            return '';
        }
        $total = $this->totalSeriesSessions();
        return "Sesión {$this->session_number} de {$total}";
    }

    // --- Gestión de Múltiples Expositores / Facilitadores ---

    public function getFormattedInstructorsAttribute(): string
    {
        if (is_array($this->instructors) && count(array_filter($this->instructors)) > 0) {
            return implode(', ', array_filter(array_map('trim', $this->instructors)));
        }

        return $this->instructor ?: 'No asignado';
    }

    public function getInstructorsListAttribute(): array
    {
        if (is_array($this->instructors) && count($this->instructors) > 0) {
            return array_values(array_filter(array_map('trim', $this->instructors)));
        }

        if (!empty($this->instructor)) {
            return array_values(array_filter(array_map('trim', explode(',', $this->instructor))));
        }

        return [];
    }

    // --- Lógica de Apertura y Cierre Automático por Fecha y Hora ---

    public function getStartDateTimeAttribute(): ?Carbon
    {
        if (!$this->event_date) {
            return null;
        }

        $dateStr = $this->event_date->format('Y-m-d');
        $timeStr = $this->start_time ? substr($this->start_time, 0, 8) : '00:00:00';

        return Carbon::parse("{$dateStr} {$timeStr}");
    }

    public function getIsNotStartedAttribute(): bool
    {
        $startDateTime = $this->start_date_time;
        if (!$startDateTime) {
            return false;
        }

        return now()->lessThan($startDateTime);
    }

    public function getEndDateTimeAttribute(): ?Carbon
    {
        if (!$this->event_date) {
            return null;
        }

        $dateStr = $this->event_date->format('Y-m-d');
        $timeStr = $this->end_time ? substr($this->end_time, 0, 8) : '23:59:59';

        return Carbon::parse("{$dateStr} {$timeStr}");
    }

    public function getIsPastEndTimeAttribute(): bool
    {
        $endDateTime = $this->end_date_time;
        if (!$endDateTime) {
            return false;
        }

        return now()->greaterThan($endDateTime);
    }

    public function getIsRegistrationOpenAttribute(): bool
    {
        if ($this->status === 'cancelled' || $this->status === 'completed') {
            return false;
        }

        if (!$this->allow_registration) {
            return false;
        }

        // Si el administrador forzó la habilitación manual (override), se mantiene abierto sin importar el horario
        if ($this->override_closing) {
            return true;
        }

        // Si el evento aún no ha iniciado (está en el futuro), se mantiene cerrado hasta que empiece
        if ($this->is_not_started) {
            return false;
        }

        // Si la hora de finalización ya pasó, se cierra automáticamente
        if ($this->is_past_end_time) {
            return false;
        }

        return true;
    }

    public function getRegistrationStatusInfoAttribute(): array
    {
        if ($this->status === 'cancelled') {
            return [
                'open' => false,
                'reason' => 'cancelled',
                'message' => 'Evento Cancelado',
                'badge_class' => 'bg-rose-600 text-white',
            ];
        }

        if ($this->status === 'completed') {
            return [
                'open' => false,
                'reason' => 'completed',
                'message' => 'Evento Finalizado',
                'badge_class' => 'bg-slate-700 text-white',
            ];
        }

        if (!$this->allow_registration) {
            return [
                'open' => false,
                'reason' => 'manual_close',
                'message' => 'Registro Pausado / Cerrado',
                'badge_class' => 'bg-amber-500 text-white',
            ];
        }

        if ($this->override_closing) {
            return [
                'open' => true,
                'reason' => 'reopened_by_admin',
                'message' => 'Habilitado por Administrador',
                'badge_class' => 'bg-indigo-600 text-white animate-pulse',
            ];
        }

        if ($this->is_not_started) {
            $formattedStart = $this->event_date->format('d/m/Y') . ($this->start_time ? ' ' . Carbon::parse($this->start_time)->format('h:i A') : '');
            return [
                'open' => false,
                'reason' => 'not_started',
                'message' => "Apertura Programada ({$formattedStart})",
                'badge_class' => 'bg-sky-600 text-white',
            ];
        }

        if ($this->is_past_end_time) {
            return [
                'open' => false,
                'reason' => 'expired_schedule',
                'message' => 'Cerrado Automáticamente (Horario Finalizado)',
                'badge_class' => 'bg-slate-800 text-white',
            ];
        }

        return [
            'open' => true,
            'reason' => 'active',
            'message' => 'Registro Abierto y En Vivo',
            'badge_class' => 'bg-emerald-600 text-white',
        ];
    }

    // --- Matriz y Tracking de Retención vs Sesión 1 ---

    public function getRetentionMetrics(): array
    {
        $root = $this->getRootEvent();
        $isBase = ($this->id === $root->id);

        $baseParticipantIds = Attendance::where('event_id', $root->id)->pluck('participant_id')->toArray();
        $baseTotal = count($baseParticipantIds);

        $currentParticipantIds = Attendance::where('event_id', $this->id)->pluck('participant_id')->toArray();
        $currentTotal = count($currentParticipantIds);

        if ($isBase) {
            return [
                'is_base_session' => true,
                'base_total' => $baseTotal,
                'current_total' => $currentTotal,
                'retained_count' => $baseTotal,
                'retention_rate' => 100,
                'missing_count' => 0,
                'new_attendees_count' => 0,
            ];
        }

        $retainedIds = array_intersect($baseParticipantIds, $currentParticipantIds);
        $retainedCount = count($retainedIds);

        $missingIds = array_diff($baseParticipantIds, $currentParticipantIds);
        $missingCount = count($missingIds);

        $newIds = array_diff($currentParticipantIds, $baseParticipantIds);
        $newCount = count($newIds);

        $retentionRate = $baseTotal > 0 ? round(($retainedCount / $baseTotal) * 100, 1) : 0;

        return [
            'is_base_session' => false,
            'base_total' => $baseTotal,
            'current_total' => $currentTotal,
            'retained_count' => $retainedCount,
            'retention_rate' => $retentionRate,
            'missing_count' => $missingCount,
            'new_attendees_count' => $newCount,
        ];
    }

    public function getMissingAttendeesFromBase(): Collection
    {
        $root = $this->getRootEvent();
        if ($this->id === $root->id) {
            return new Collection();
        }

        $baseParticipantIds = Attendance::where('event_id', $root->id)->pluck('participant_id')->toArray();
        $currentParticipantIds = Attendance::where('event_id', $this->id)->pluck('participant_id')->toArray();

        $missingIds = array_diff($baseParticipantIds, $currentParticipantIds);

        return Participant::whereIn('id', $missingIds)->orderBy('first_name')->get();
    }

    // --- URLs y Badges ---

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
