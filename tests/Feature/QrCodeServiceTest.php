<?php

namespace Tests\Feature;

use App\Models\CallCard;
use App\Models\EsimCode;
use App\Models\EsimType;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_call_card_qr_codes_are_generated_as_svg(): void
    {
        Storage::fake('public');

        $card = CallCard::factory()->create([
            'uuid' => (string) Str::uuid(),
            'created_by' => User::factory(),
        ]);

        $path = app(QrCodeService::class)->generateForCallCard($card);
        $relativePath = 'qrcodes/'.$card->uuid.'.svg';

        $this->assertSame('storage/'.$relativePath, $path);
        $this->assertSame('storage/'.$relativePath, $card->qr_code_path);
        Storage::disk('public')->assertExists($relativePath);
        $this->assertStringContainsString('<svg', Storage::disk('public')->get($relativePath));
    }

    public function test_esim_qr_codes_are_generated_as_svg(): void
    {
        Storage::fake('public');

        $type = EsimType::create([
            'name' => 'Test eSIM',
            'product_id' => 'test-product',
            'status' => 'active',
        ]);

        $code = EsimCode::create([
            'uuid' => (string) Str::uuid(),
            'esim_type_id' => $type->id,
            'product_id' => $type->product_id,
            'status' => 'unused',
        ]);

        $path = app(QrCodeService::class)->generateForEsimCode($code, url('/esim/'.$code->uuid));
        $relativePath = 'esim-qrcodes/'.$code->uuid.'.svg';

        $this->assertSame('storage/'.$relativePath, $path);
        Storage::disk('public')->assertExists($relativePath);
        $this->assertStringContainsString('<svg', Storage::disk('public')->get($relativePath));
    }
}
