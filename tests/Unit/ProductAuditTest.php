<?php

namespace Tests\Unit;


use App\Contracts\AuditableInterface;
use App\Models\AuditEvent;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductAuditTest extends TestCase
{


    #[Test]
    public function implements_audit_interface(): void
    {

        //verificamos que  el objeto producto sea de AuditableInterface
        $producto = new Product();

        $this->assertInstanceOf(AuditableInterface::class, $producto);
    }

    #[Test]
    public function returns_correct_auditable_fields(): void
    {

        //verificamos que coincidan todos los campos son auditables del modelo producto.

        $product = new Product();

        $expectedFields = ['name', 'description', 'price', 'stock', 'is_active'];

        $this->assertEquals($expectedFields, $product->getAuditableFields());
    }

    #[Test]
    public function returns_correct_auditable_events(): void
    {

        //verificamos que coincidan todos los eventos que son auditables del modelo producto

        $product = new Product();
        $expectedEvents = ['created', 'updated', 'deleted', 'restored'];

        $this->assertEquals($expectedEvents, $product->getAuditableEvents());


    }


        #[Test]
        public function determines_if_event_should_be_audited(): void
        {

            //verificamos que tipos de eventos deben ser auditables al momento de ejecutarse
            $product = new Product();
           // protected array $auditableFields = ['name', 'description', 'price', 'stock', 'is_active'];
          //  $shouldAudit = ['created', 'updated', 'deleted', 'restored'];
          //  protected array $auditableFields = ['name', 'description', 'price', 'stock', 'is_active'];
            $this->assertTrue($product->shouldAudit('created'));
            $this->assertTrue($product->shouldAudit('updated'));
            $this->assertTrue($product->shouldAudit('deleted'));
            $this->assertTrue($product->shouldAudit('restored'));
            $this->assertFalse($product->shouldAudit('retrieved'));

        }


    #[Test]
    public function filters_auditable_data(): void
    {
        //verificamos si los inputs (fields) que son auditables.
        $product = new Product();
        $product->fill([
            'name' => 'Test Product',
            'description' => 'Producto de prueba',
            'price' => 1000,
            'stock' => 10,
            'is_active' => true

        ]);

        $auditableData = $product->getAuditableData();

        $this->assertArrayHasKey('name', $auditableData);
        $this->assertArrayHasKey('description', $auditableData);
        $this->assertArrayHasKey('price', $auditableData);
        $this->assertArrayHasKey('stock', $auditableData);
        $this->assertArrayHasKey('is_active', $auditableData);
    }


    #[Test]
    public function audit_event_changed_fields_attribute(): void
    {
        $auditEvent = new AuditEvent();
        $auditEvent->old_values = [
            'name' => 'Old nane',
            'price' => '50.00',
            'stock' => 50,
        ];
        $auditEvent->new_values = [
            'name' => 'New name',
            'price' => '100.00',
            'stock' => 50,

        ];
        $changedFields = $auditEvent->changed_fields;


        $this->assertContains('name', $changedFields);
        $this->assertContains('price', $changedFields);
        $this->assertNotContains('stock', $changedFields);


    }

    #[Test]
    public function audit_event_scopes_work(): void
    {

        $product = new Product();
        $product->id = 1;


        $query = AuditEvent::forModel($product);
        $sql = $query->toSql();
       // dump($query->getBindings());

        $this->assertStringContainsString('auditable_type', $sql);
        $this->assertStringContainsString('auditable_id', $sql);

        $query=AuditEvent::ofType('created');
        $sql = $query->toSql();
        $this->assertStringContainsString('event_type', $sql);


    }


}
