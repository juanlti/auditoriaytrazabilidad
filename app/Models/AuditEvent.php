<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditEvent extends Model
{
    public $fillable = [
        'auditable_type',
        'auditable_id',
        'event_type',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        //devuelve el usuario que creo este registro, utilizando la columna de user_id.
        return $this->belongsTo(User::class);
    }


    public function changedFields(): Attribute
    {
        return Attribute::make(
            get: fn (): array =>
            empty($this->old_values) || empty($this->new_values)
                ? []
                : array_keys(array_diff_assoc(
                $this->new_values,
                $this->old_values
            ))
        );
    }


    #[Scope]
    protected function forModel($query, Model $model)
    {
        //el metodo getKey() devuelve el  valor del primera key cuando no se usa el id.
        //ejemplo:
        // User --> id
        // Products --> product_id
        // Article --> article_id
        //devuelve [id,product_id,article_id] de manera dinamica.
        return self::query()->
        where('auditable_type', $model::class)
            ->where('auditable_id', $model->getKey());
    }

    #[Scope]
    protected function ofType($query, string $evenType)
    {
        //devuelve los registros (o auditorias) de un evento en especifico
        // ejempplo si $eventType es un evento de create =>devuelve todos los registros que sea del mismo tipo.

        return $query->where('event_type', $evenType);

    }

}


