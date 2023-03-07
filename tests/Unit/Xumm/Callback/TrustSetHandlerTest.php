<?php
// declare(strict_types=1);
namespace Xrpl\XummForWoocommerce\Tests\Unit\Xumm\Callback;

use Xrpl\XummForWoocommerce\Tests\Helper\Mock\TrustSetPayloadMock;
use Xrpl\XummForWoocommerce\Tests\Helper\Mock\WordpressMock;
use Xrpl\XummForWoocommerce\Tests\Helper\Mock\XummPaymentGatewayMock;
use Xrpl\XummForWoocommerce\Xumm\Callback\TrustSetHandler;
use Xrpl\XummForWoocommerce\Xumm\Exception\TrustSetException;
use PHPUnit\Framework\TestCase;

final class TrustSetHandlerTest extends TestCase
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
    public function handleTest() : void
    {
        $this->expectException(TrustSetException::class);

        WordpressMock::create();

        $gateway = XummPaymentGatewayMock::create();

        $payload = TrustSetPayloadMock::create();

        $handler = new TrustSetHandler($gateway, $payload);

        $handler->handle();
    }
}
