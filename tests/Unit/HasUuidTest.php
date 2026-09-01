<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class DummyUuidModel extends Model
{
    use HasUuid;

    protected $table = 'dummy_uuid_models';

    protected $guarded = [];
}

final class HasUuidTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('dummy_uuid_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_generates_uuid_v4_on_creation(): void
    {
        $model = DummyUuidModel::create(['name' => 'Sample Item']);

        $this->assertNotEmpty($model->id);
        $this->assertTrue(Str::isUuid($model->id));
        $this->assertFalse($model->getIncrementing());
        $this->assertSame('string', $model->getKeyType());
    }

    public function test_it_does_not_overwrite_existing_uuid(): void
    {
        $customUuid = (string) Str::uuid();
        $model = DummyUuidModel::create([
            'id' => $customUuid,
            'name' => 'Custom Item',
        ]);

        $this->assertSame($customUuid, $model->id);
    }
}
