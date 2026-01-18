<?php

namespace Tests\Feature;


use App\Models\AuditEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditSystemTest extends TestCase
{

    use RefreshDatabase;

    private User $user;

    //constructor
    protected function setUp(): void
    {
        //utilizo el constructor del padre
        parent::setUp();

        $this->user = User::factory()->create();

        //logea el usuario creado
        $this->actingAs($this->user);

    }

    #[Test]
    public function audits_products_creation(): void
    {

        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Producto de prueba',
            'price' => 1000,
            'stock' => 10,
            'is_active' => true
        ]);

        //  dump($product);

        $auditEvents = AuditEvent::latest()->first();
        $this->assertNotNull($auditEvents);
        $this->assertEquals($product->id, $auditEvents->auditable_id);
        $this->assertEquals(Product::class, $auditEvents->auditable_type);
        $this->assertEquals('created', $auditEvents->event_type);
        $this->assertEquals($this->user->id, $auditEvents->user_id);
        $this->assertNull($auditEvents->old_values);


        //product
        $this->assertEquals('Test Product', $auditEvents->new_values['name']);
        $this->assertEquals('Producto de prueba', $auditEvents->new_values['description']);
        $this->assertEquals(1000, $auditEvents->new_values['price']);
        $this->assertEquals(10, $auditEvents->new_values['stock']);
        $this->assertEquals(true, $auditEvents->new_values['is_active']);


    }

    #[Test]
    public function audits_product_update(): void
    {


        $product = Product::create([
            'name' => 'Original Product',
            'price' => 500,
            'stock' => 5
        ]);

        //elimino la auditoria del producto creado
        AuditEvent::query()->delete();

        $product->update([
            'name' => 'Updated Product',
            'price' => 1000,
        ]);

        //  $auditEvent = AuditEvent::orderByDesc('id')->first();
        $auditEvent = AuditEvent::latest()->first();

        $this->assertNotNull($auditEvent);
        $this->assertEquals('updated', $auditEvent->event_type);
        // $this->assertEquals('Original Product', $auditEvent->old_values['name']);
        $this->assertEquals('Updated Product', $auditEvent->new_values['name']);
        //   $this->assertEquals(500, $auditEvent->old_values['price']);
        $this->assertEquals(1000, $auditEvent->new_values['price']);


    }

    #[Test]
    public function audits_product_deletion(): void
    {

        $product = Product::create([
            'name' => 'Product to delete',
            'price' => 1000,
        ]);
        //elimino la auditoria del producto creado
        AuditEvent::query()->delete();

        $product->delete();
//        $auditEvent = AuditEvent::orderByDesc('id')->first();
        $auditEvent = AuditEvent::latest()->first();

        $this->assertNotNull($auditEvent);
        $this->assertEquals('deleted', $auditEvent->event_type);
        $this->assertNotNull($auditEvent->old_values);
        $this->assertNull($auditEvent->new_values);
        //   $this->assertEquals('Product to delete', $auditEvent->old_values['name']);

    }

    #[Test]
    public function audits_product_restoration(): void
    {

        $product = Product::create([
            'name' => 'Product to restore',
            'price' => 1000,
        ]);

        $product->delete();

        // borro la auditaoria
        AuditEvent::query()->delete();

        $product->restore();

        $auditEvent = AuditEvent::latest()->first();

        $this->assertNotNull($auditEvent);
        $this->assertEquals('restored', $auditEvent->event_type);
        $this->assertNull($auditEvent->old_values);
        $this->assertNotNull($auditEvent->new_values);
        // $this->assertEquals('Product to restore', $auditEvent->new_values['name']);

    }

    public function captures_request_metada(): void
    {

        Product::create([
            'name' => 'Product to capture metadata',
            'price' => 10.00,
        ]);

        $auditEvent = AuditEvent::latest()->first();
        $this->assertNotNull($auditEvent->ip_address);
        $this->assertNotNull($auditEvent->user_agent);
        $this->assertArrayHasKey('url', $auditEvent->metadata);
        $this->assertArrayHasKey('method', $auditEvent->metadata);
        $this->assertArrayHasKey('timestamp', $auditEvent->metadata);

    }

    public function provides_audit_relationship()
    {
        $product = Product::create([
            'name' => 'Product to provide audit relationship',
            'price' => 1000,
        ]);

        //es el evento de un producto
        $auditEvent = $product->auditEvents;

        $this->assertCount(1, $auditEvent);
        $this->assertEquals('created', $auditEvent->first()->event_type);
        $this->assertInstanceOf(AuditEvent::class, $auditEvent->first());
    }


}
