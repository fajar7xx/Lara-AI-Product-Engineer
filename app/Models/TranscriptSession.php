<?php

namespace App\Models;

use Database\Factories\TranscriptSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $ulid
 * @property string $source_type
 * @property string $transcript_text
 * @property string $status
 * @property array<string, mixed>|null $extracted_context
 * @property array<int, string>|null $layout_recommendations
 * @property array<int, string>|null $design_system_recommendations
 * @property string|null $project_name
 * @property string|null $project_summary
 * @property string|null $target_users
 * @property array<int, string>|null $goals
 * @property array<int, string>|null $key_features
 * @property string|null $template_family
 * @property string|null $design_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, GenerationOutput> $generationOutputs
 */
#[Fillable([
    'user_id',
    'ulid',
    'source_type',
    'transcript_text',
    'status',
    'extracted_context',
    'layout_recommendations',
    'design_system_recommendations',
    'project_name',
    'project_summary',
    'target_users',
    'goals',
    'key_features',
    'template_family',
    'design_system',
])]
#[UseFactory(TranscriptSessionFactory::class)]
class TranscriptSession extends Model
{
    /** @use HasFactory<TranscriptSessionFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_EXTRACTING = 'extracting';

    public const STATUS_CLARIFYING = 'clarifying';

    public const STATUS_GENERATING = 'generating';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'source_type' => 'paste',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'extracted_context' => 'array',
            'layout_recommendations' => 'array',
            'design_system_recommendations' => 'array',
            'goals' => 'array',
            'key_features' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TranscriptSession $transcriptSession): void {
            if (blank($transcriptSession->ulid)) {
                $transcriptSession->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<GenerationOutput, $this>
     */
    public function generationOutputs(): HasMany
    {
        return $this->hasMany(GenerationOutput::class);
    }
}
