<?php

namespace App\Models;

use App\Traits\HasCustomUuids;
use App\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    use HasCustomUuids, Searchable;

    public bool $timestamp = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'tags',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'json',
        ];
    }

    /**
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->toDateTimeString();
    }

    /**
     * @param $value
     * @return string
     */
    public function getUpdatedAtAttribute($value): string
    {
        return Carbon::parse($value)->toDateTimeString();
    }

    /**
     * @param $value
     * @return string
     */
    public function getDeletedAtAttribute($value): string
    {
        return Carbon::parse($value)->toDateTimeString();
    }

    /**
     * Converts the model into a format suitable for indexing in Elasticsearch.
     *
     * @return array
     */
    public function toElasticsearchDocumentArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'tags' => $this->tags,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

    /**
     * Get the index name for Elasticsearch.
     *
     * @return string
     */
    public static function getSearchIndex(): string
    {
        return (new Post)->getTable();
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
