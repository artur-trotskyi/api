<?php

namespace App\Models;

use App\Observers\PostObserver;
use App\Traits\HasCustomUuidsTrait;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PostObserver::class])]
class Post extends Model
{
    use HasFactory, SoftDeletes;
    use HasCustomUuidsTrait, Searchable;

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
     * Get the index name for Elasticsearch.
     */
    public static function getSearchIndex(): string
    {
        return (new Post())->getTable();
    }

    /**
     * Converts the model into a format suitable for indexing in Elasticsearch.
     */
    public function toElasticsearchDocumentArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'tags' => $this->tags,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'json',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'deleted_at' => 'datetime:Y-m-d H:i:s',
        ];
    }
}
