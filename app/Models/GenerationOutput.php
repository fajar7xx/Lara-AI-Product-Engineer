<?php

namespace App\Models;

use Database\Factories\GenerationOutputFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $transcript_session_id
 * @property string $type
 * @property string $status
 * @property string|null $content
 * @property string|null $error_message
 * @property int $revision_number
 * @property array<string, mixed>|null $clarification_snapshot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TranscriptSession $transcriptSession
 */
#[Fillable([
    'transcript_session_id',
    'type',
    'status',
    'content',
    'error_message',
    'revision_number',
    'clarification_snapshot',
])]
#[UseFactory(GenerationOutputFactory::class)]
class GenerationOutput extends Model
{
    /** @use HasFactory<GenerationOutputFactory> */
    use HasFactory;

    public const TYPE_PRD = 'prd';

    public const TYPE_USER_STORIES = 'user_stories';

    public const TYPE_FUNCTIONAL_REQUIREMENTS = 'functional_requirements';

    public const TYPE_HTML_PAGE = 'html_page';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'revision_number' => 1,
    ];

    /**
     * @return list<string>
     */
    public static function supportedTypes(): array
    {
        return [
            self::TYPE_PRD,
            self::TYPE_USER_STORIES,
            self::TYPE_FUNCTIONAL_REQUIREMENTS,
            self::TYPE_HTML_PAGE,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'clarification_snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<TranscriptSession, $this>
     */
    public function transcriptSession(): BelongsTo
    {
        return $this->belongsTo(TranscriptSession::class);
    }
}
