<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Modules\Payments;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\Actions\CreatePaymentAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\Actions\GetPaymentStatusAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Payments\DataTransferObjects\CreatePaymentData;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class PaymentsFunctionalTest extends TestCase
{
    private AwajDigitalClient $awajDigitalClient;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->awajDigitalClient = new AwajDigitalClient(['token' => 'test-token']);
    }

    public function test_create_payment_success(): void
    {
        // Arrange
        Http::fake([
            '*/payments/create' => Http::response([
                'success' => true,
                'payment_url' => 'https://checkout.awajdigital.com/pay/inv_123',
                'invoice_id' => 'inv_123',
            ], 200),
        ]);

        $action = new CreatePaymentAction($this->awajDigitalClient);
        $data = new CreatePaymentData(
            amount: 50.0,
            successUrl: 'https://example.com/checkout/success',
            cancelUrl: 'https://example.com/checkout/cancel',
        );

        // Act
        $paymentUrlData = $action->execute($data);

        // Assert
        $this->assertSame('https://checkout.awajdigital.com/pay/inv_123', $paymentUrlData->paymentUrl);
        $this->assertSame('inv_123', $paymentUrlData->invoiceId);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/payments/create'
                && $request->method() === 'POST'
                && $request->data()['amount'] === 50.0;
        });
    }

    public function test_create_payment_rejects_amount_below_minimum_client_side(): void
    {
        // Arrange
        Http::fake();

        // Act & Assert
        try {
            new CreatePaymentData(
                amount: 15.0, // Minimum is 20 BDT
                successUrl: 'https://example.com/success',
            );
            $this->fail('Expected ClientValidationException was not thrown.');
        } catch (ClientValidationException $clientValidationException) {
            $this->assertStringContainsString('Payment amount must be at least 20 BDT', $clientValidationException->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_create_payment_rejects_non_https_url_client_side(): void
    {
        // Arrange
        Http::fake();

        // Act & Assert
        try {
            new CreatePaymentData(
                amount: 25.0,
                successUrl: 'http://insecure-example.com/success',
            );
            $this->fail('Expected ClientValidationException was not thrown.');
        } catch (ClientValidationException $clientValidationException) {
            $this->assertStringContainsString('Success URL must use HTTPS protocol', $clientValidationException->getMessage());
            Http::assertNothingSent();
        }
    }

    public function test_get_payment_status_success(): void
    {
        // Arrange
        Http::fake([
            '*/payments/inv_123/status' => Http::response([
                'success' => true,
                'invoice_id' => 'inv_123',
                'status' => 'completed',
                'amount' => 500.0,
            ], 200),
        ]);

        $action = new GetPaymentStatusAction($this->awajDigitalClient);

        // Act
        $paymentStatusData = $action->execute('inv_123');

        // Assert
        $this->assertSame('inv_123', $paymentStatusData->invoiceId);
        $this->assertSame('completed', $paymentStatusData->status);
        $this->assertSame(500.0, $paymentStatusData->amount);
    }
}
