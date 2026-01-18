<?php

namespace App\Models;

use App\Concerns\Traits\AuditableTrait;
use App\Contracts\AuditableInterface;
use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(AuditObserver::class)]
class Product extends Model implements AuditableInterface
{
    //los metodos del contrato (o interfaz) de AuditableInterface estan implementados en la clase AuditableTrait
    use AuditableTrait, softDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public $fillable = ['name', 'description', 'price', 'stock', 'is_active'];


    //$auditableFields marcamos todas las columnas (o registros), que queremos auditar
    protected array $auditableFields = ['name', 'description', 'price', 'stock', 'is_active'];

    public array $auditableEvents = ['created', 'updated', 'deleted', 'restored'];
}
