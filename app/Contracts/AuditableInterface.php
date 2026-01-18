<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface AuditableInterface
{
    // devuelve los campos que son auditable de un modelo determinado
    public function getAuditableFields(): array;


    // devuelve los eventos que son auditable de un modelo determinado
    public function getAuditableEvents(): array;


    //devuelve true/false si el modelo debe ser auditable para un evento en especifico
    // es decir,  se ejecuta un evento ( ejemplo: create ) de un modelo determinado, y con el metodo
    //shouldAudit determina si es necesario o no auditar ese evento.
    public function shouldAudit(string $event): bool;

    //devuelve los eventos que fueron auditable de un modelo determinado
    public function audiEvents(): MorphMany;

    //devuelve los datos actuales (antes de guardarse en la bd)
    // se edita el campo edad del modelo user:
    //devuelve la edad actualizada y no la anterior
    public function getAuditableData(): array;


    //devuelve los datos anteriores al actuales
    // se edita el campo edad del modelo user:
    //devuelve la edad anterior y no la actualizada
    public function getOriginalAuditableData(): array;
}
