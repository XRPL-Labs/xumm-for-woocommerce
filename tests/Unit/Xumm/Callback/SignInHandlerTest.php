<?php
// declare(strict_types=1);
namespace Xrpl\XummForWoocommerce\Tests\Unit\Xumm\Callback;

use Xrpl\XummForWoocommerce\Tests\Helper\Mock\SignInPayloadMock;
use Xrpl\XummForWoocommerce\Tests\Helper\Mock\WordpressMock;
use Xrpl\XummForWoocommerce\Tests\Helper\Mock\XummPaymentGatewayMock;
use Xrpl\XummForWoocommerce\Xumm\Callback\SignInHandler;
use Xrpl\XummForWoocommerce\Xumm\Exception\SignInException;
use PHPUnit\Framework\TestCase;

final class SignInHandlerTest extends TestCase
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
        $this->expectException(SignInException::class);

        WordpressMock::create();

        $gateway = XummPaymentGatewayMock::create();

        $payload = SignInPayloadMock::create('');

        $handler = new SignInHandler($gateway, $payload);
        $handler->handle();
    }
}
