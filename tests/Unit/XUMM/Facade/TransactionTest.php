<?php
declare(strict_types=1);
namespace XummForWoocomerce\Tests\Unit\XUMM\Facade;

use XummForWoocomerce\XUMM\Facade\Transaction;

class TransactionTest extends \PHPUnit\Framework\TestCase
{
    public function setUp() : void
    {
		parent::setUp();
		\WP_Mock::setUp();
	}

	public function tearDown() : void
    {
		\WP_Mock::tearDown();
		\Mockery::close();
		parent::tearDown();
	}

    /**
     * @test
     */
    public function getTransactionDetails(): void
    {
        \WP_Mock::userFunction( 'wp_remote_post', [
            'args' => [
            \WP_Mock\Functions::type('string'), \WP_Mock\Functions::type( 'array' )
        ], 'return' =>
            \XummForWoocomerceTests\Responses\Transaction\USD::getResponse()
        ]);

        \WP_Mock::userFunction( 'is_wp_error', [
            'args' => [\WP_Mock\Functions::type('array')]
        , 'return' =>
            false
        ]);

        $txid = '876619C5488EE0335D1B50E0348EB4397E1B6954CAB5AE083381776D29929A4B';

        $response = Transaction::getTransactionDetails($txid);

        $this->assertEquals($response['result']['status'], 'success');
    }

    /**
     * @test
     * @testdox Order paid and is less than order total in USD
     */
    public function checkDeliveredAmountUSD() : void
    {
        $order = \Mockery::mock('\WC_Order');
	    $order->shouldReceive('get_total')
            ->with()
            ->andReturn('0.000001');

        $order->shouldReceive('add_order_note');
        $order->shouldReceive('get_currency')
            ->andReturn('USD');

        $delivered_amount = json_decode('{
            "currency":"USD",
            "issuer":"rhub8VRN55s94qWKDv6jmDy1pUykJzF3wq",
            "value":"0.0000005"
         }', true);

        $issuers = 'rhub8VRN55s94qWKDv6jmDy1pUykJzF3wq';
        $txid = '876619C5488EE0335D1B50E0348EB4397E1B6954CAB5AE083381776D29929A4B';
        $explorer = 'https://bithomp.com/explorer/';

        try {
            Transaction::checkDeliveredAmount($delivered_amount, $order, $issuers, $txid, $explorer);
        } catch (\Exception $e)
        {
            $this->assertSame(
                'Your order is not paid and is less than order total, Please contact support<br>Paid: USD 5.0E-7<br>Open: USD 5.0E-7<br><a href="https://bithomp.com/explorer/876619C5488EE0335D1B50E0348EB4397E1B6954CAB5AE083381776D29929A4B">Transaction information</a>',
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     * @testdox Order paid and is less than order total in XRP
     */
    public function checkDeliveredAmountXRP() : void
    {
        $order = \Mockery::mock('\WC_Order');
	    $order->shouldReceive('get_total')
            ->with()
            ->andReturn(1610.547979);

        $order->shouldReceive('add_order_note');
        $order->shouldReceive('get_currency')
            ->andReturn('XRP');

        $delivered_amount = '1510547979';
        $issuers = 'rhub8VRN55s94qWKDv6jmDy1pUykJzF3wq';
        $txid = 'ABE156A5834134FAA6088C7295C82F9C8BD10672E8029333D6616636866D7530';
        $explorer = 'https://bithomp.com/explorer/';

        try {
            Transaction::checkDeliveredAmount($delivered_amount, $order, $issuers, $txid, $explorer);
        } catch (\Exception $e)
        {
            $this->assertSame(
                'Your order is not paid and is less than order total, Please contact support',
                $e->getMessage()
            );
        }
    }
}
