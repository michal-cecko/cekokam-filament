<?php

namespace App\Models\Other;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Log extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'content',
        'user_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function getLoggablesAttribute(): Collection
    {
        $logRelations = DB::table('log_relations')
            ->where('log_id', $this->id)
            ->get();

        $loggableIdsByType = [];

        foreach ($logRelations as $logRelation) {
            $loggableIdsByType[$logRelation->loggable_type][] = $logRelation->loggable_id;
        }

        $models = collect();

        foreach ($loggableIdsByType as $loggableType => $loggableIds) {
            $modelClass = Relation::getMorphedModel($loggableType);
            $arr = explode('\\', $loggableType);
            $className = end($arr);

            // Check if the class exists before using it
            if (! empty($modelClass) && class_exists($modelClass)) {
                $models[$className] = $modelClass::whereIn('id', $loggableIds)->get();
            }
        }

        return $models;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
