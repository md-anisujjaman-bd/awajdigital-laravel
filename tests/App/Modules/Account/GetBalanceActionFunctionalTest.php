<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Tests\App\Modules\Account;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use MdAnisujjamanBd\AwajdigitalLaravel\Client\AwajDigitalClient;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Account\Actions\GetBalanceAction;
use MdAnisujjamanBd\AwajdigitalLaravel\Tests\TestCase;

final class GetBalanceActionFunctionalTest extends TestCase
{
    public function test_it_gets_account_balance_successfully(): void
    {
        // Arrange
        Http::fake([
            '*/balance' => Http::response([
                'success' => true,
                'balance' => 1250.75,
            ], 200),
        ]);

        $client = new AwajDigitalClient(['token' => 'test-token']);
        $action = new GetBalanceAction($client);

        // Act
        $balanceData = $action->execute();

        // Assert
        $this->assertSame(1250.75, $balanceData->amount);
        $this->assertSame('BDT', $balanceData->currency);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.awajdigital.com/api/balance'
                && $request->method() === 'GET'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request->hasHeader('Accept', 'application/json');
        });
    }
}
