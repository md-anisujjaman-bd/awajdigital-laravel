<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App;

use MdAnisujjamanBd\AwajdigitalLaravel\Facades\AwajDigital as AwajDigitalFacade;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects\SendOtpData;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class AwajDigitalManagerFunctionalTest extends TestCase
{
    public function test_fake_enables_default_stubs_for_all_endpoints(): void
    {
        // Arrange
        AwajDigitalFacade::fake();

        // Act & Assert Check Balance
        $balance = AwajDigitalFacade::checkBalance();
        $this->assertSame(1250.75, $balance->amount);

        // Act & Assert Send OTP
        $otp = AwajDigitalFacade::sendOtp(new SendOtpData(
            voice: 'test_voice',
            phoneNumber: '01712345678',
            otpCode: '1234',
            sender: '8809612000000',
        ));
        $this->assertSame(101, $otp->id);

        // Act & Assert List Voices
        $voices = AwajDigitalFacade::listVoices();
        $this->assertCount(1, $voices);

        // Act & Assert List Senders
        $senders = AwajDigitalFacade::listSenders();
        $this->assertCount(1, $senders);

        // Act & Assert List Agents
        $agents = AwajDigitalFacade::listAgents();
        $this->assertCount(1, $agents);

        // Act & Assert Mint SDK Token
        $token = AwajDigitalFacade::mintSdkToken(10);
        $this->assertSame('avt_test_token_123', $token->token);

        // Assert Sent
        AwajDigitalFacade::assertSent(fn ($request) => str_contains($request->url(), '/balance'));
    }
}
