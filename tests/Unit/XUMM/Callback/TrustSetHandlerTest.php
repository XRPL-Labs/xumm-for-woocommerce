<?php
declare(strict_types=1);
namespace Xrpl\XummForWoocommerce\Tests\Unit\XUMM\Facade;

use Xrpl\XummForWoocommerce\Tests\helper\Mock\TrustSetPayloadMock;
use Xrpl\XummForWoocommerce\Tests\helper\Mock\WordpressMock;
use Xrpl\XummForWoocommerce\Tests\helper\Mock\XummPaymentGatewayMock;
use Xrpl\XummForWoocommerce\XUMM\Callback\TrustSetHandler;
use Xrpl\XummForWoocommerce\XUMM\Exception\TrustSetException;

class TrustSetHandlerTest extends \PHPUnit\Framework\TestCase
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
    public function handleTest()
    {
        $this->expectException(TrustSetException::class);

        WordpressMock::create();

        $gateway = XummPaymentGatewayMock::create();

        $payload = TrustSetPayloadMock::create();

        $handler = new TrustSetHandler($gateway, $payload);

        $handler->handle();
    }
}
