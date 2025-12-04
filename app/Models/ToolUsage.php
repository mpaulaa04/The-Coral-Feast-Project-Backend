<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tool_slug',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public const SUPPORTED_TOOLS = [
        'ph' => [
            'label' => 'Regulador de pH',
        ],
        'oxygen' => [
            'label' => 'Regulador de oxígeno',
        ],
        'temperature' => [
            'label' => 'Control de temperatura',
        ],
        'water_quality' => [
            'label' => 'Tratamiento de suciedad',
        ],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
