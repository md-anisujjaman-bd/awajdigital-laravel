<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Modules\VoicesSenders;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Senders\Actions\ListSendersAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\Actions\ListVoicesAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\Actions\UploadVoiceAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Voices\DataTransferObjects\UploadVoiceData;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class VoicesSendersFunctionalTest extends TestCase
{
    private AwajDigitalClient $awajDigitalClient;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->awajDigitalClient = new AwajDigitalClient(['token' => 'test-token']);
    }

    public function test_list_voices_success(): void
    {
        // Arrange
        Http::fake([
            '*/voices' => Http::response([
                'success' => true,
                'voices' => [
                    [
                        'id' => 1,
                        'name' => 'Main Prompt',
                        'status' => 'approved',
                        'createdAt' => '2026-09-29T10:00:00Z',
                    ],
                ],
            ], 200),
        ]);

        $action = new ListVoicesAction($this->awajDigitalClient);

        // Act
        $voices = $action->execute();

        // Assert
        $this->assertCount(1, $voices);
        $this->assertSame(1, $voices[0]->id);
        $this->assertSame('Main Prompt', $voices[0]->name);
        $this->assertSame('approved', $voices[0]->status);
    }

    public function test_upload_voice_success(): void
    {
        // Arrange
        Http::fake([
            '*/voices/upload' => Http::response([
                'id' => 10,
                'name' => 'Greeting Audio',
                'status' => 'pending',
            ], 201),
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'voice_').'.wav';
        file_put_contents($tempFile, 'RIFFfake-wav-content');

        $uploadVoiceData = UploadVoiceData::fromPath($tempFile, 'Greeting Audio');
        $action = new UploadVoiceAction($this->awajDigitalClient);

        // Act
        $voiceData = $action->execute($uploadVoiceData);

        // Assert
        $this->assertSame(10, $voiceData->id);
        $this->assertSame('Greeting Audio', $voiceData->name);
        $this->assertSame('pending', $voiceData->status);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/voices/upload'
                && $request->isMultipart();
        });

        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function test_upload_voice_rejects_unsupported_file_extension_client_side(): void
    {
        // Arrange
        Http::fake();
        $tempFile = tempnam(sys_get_temp_dir(), 'voice_').'.txt';
        file_put_contents($tempFile, 'text content');

        // Act & Assert
        try {
            UploadVoiceData::fromPath($tempFile);
            $this->fail('Expected ClientValidationException was not thrown.');
        } catch (ClientValidationException $clientValidationException) {
            $this->assertStringContainsString('Unsupported audio format', $clientValidationException->getMessage());
            Http::assertNothingSent();
        }

        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function test_list_senders_success(): void
    {
        // Arrange
        Http::fake([
            '*/senders' => Http::response([
                'success' => true,
                'senders' => [
                    [
                        'id' => 1,
                        'callingNumber' => '8809612000000',
                        'status' => 'active',
                    ],
                ],
            ], 200),
        ]);

        $action = new ListSendersAction($this->awajDigitalClient);

        // Act
        $senders = $action->execute();

        // Assert
        $this->assertCount(1, $senders);
        $this->assertSame(1, $senders[0]->id);
        $this->assertSame('8809612000000', $senders[0]->callingNumber);
        $this->assertSame('active', $senders[0]->status);
    }
}
