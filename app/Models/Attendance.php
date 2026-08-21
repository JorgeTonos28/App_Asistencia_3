<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'participant_id',
        'signature_path',
        'check_in_at',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if ($this->signature_path && Storage::disk('public')->exists($this->signature_path)) {
            return asset('storage/' . $this->signature_path);
        }
        return null;
    }

    public function getSignatureBase64Attribute(): ?string
    {
        if ($this->signature_path && Storage::disk('public')->exists($this->signature_path)) {
            $data = Storage::disk('public')->get($this->signature_path);
            $mime = Storage::disk('public')->mimeType($this->signature_path) ?? 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode($data);
        }
        return null;
    }
}
