<?php


namespace App\Observers;

use App\Models\AuditEvent;
use App\Contracts\AuditableInterface;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{

    private function getMetada(): array
    {

        return [
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * @param Model&AuditableInterface $model
     *
     * $model => tiene el comportamiento de Model (metodos de eloquent) y el comportamiento de AuditableInterface ( sus metodos )
     * gracias a @param Model&AuditableInterface
     *
     *  y porque hay una interseccion con  Model&AuditableInterface ? Porque $model debe ser una instancia de un modelo y esa instancia debe implementar la clase AuditableInterface
     *  de esa manera se asegura que el $model tiene ambos comportamientos.
     */

    private function logEvent(Model $model, string $eventType, ?array $oldValues, ?array $newValues): void
    {
        AuditEvent::create([
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'event_type' => $eventType,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $this->getMetada(),
            'user_id' => auth()?->id(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }


    /**
     * @param Model&AuditableInterface $model
     */

    public function created(Model $model): void
    {

        if ($model instanceof AuditableInterface && $model->shouldAudit('created')) {
            $this->logEvent($model, 'created', null, $model->getAttributes());
        }
    }

    /**
     * @param Model&AuditableInterface $model
     */

    public function updated(Model $model): void
    {
        if ($model instanceof AuditableInterface && $model->shouldAudit('updated')) {
            $this->logEvent(
                $model,
                'updated',
                $model->getOriginalAuditableData(),
                $model->getAuditableData()
            );
        }
    }

    /**
     * @param Model&AuditableInterface $model
     */
    public function deleted(Model $model): void
    {
        if ($model instanceof AuditableInterface && $model->shouldAudit('deleted')) {
            $this->logEvent($model, 'deleted', $model->getOriginalAuditableData(), null);
        }
    }

    /**
     * @param Model&AuditableInterface $model
     */
    public function restored(Model $model): void
    {
        if ($model instanceof AuditableInterface && $model->shouldAudit('restored')) {
            $this->logEvent($model, 'restored', null, $model->getOriginalAuditableData());
        }
    }
}
