<?php
declare(strict_types=1);
namespace Xrpl\XummForWoocommerce\Tests\Unit\XUMM\Facade;

use Xrpl\XummForWoocommerce\Tests\helper\Mock\SignInPayloadMock;
use Xrpl\XummForWoocommerce\Tests\helper\Mock\WordpressMock;
use Xrpl\XummForWoocommerce\Tests\helper\Mock\XummPaymentGatewayMock;
use Xrpl\XummForWoocommerce\XUMM\Callback\SignInHandler;
use Xrpl\XummForWoocommerce\XUMM\Exception\SignInException;

class SignInHandlerTest extends \PHPUnit\Framework\TestCase
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
        $this->expectException(SignInException::class);

        WordpressMock::create();

        $gateway = XummPaymentGatewayMock::create();

        $payload = SignInPayloadMock::create('');

        $handler = new SignInHandler($gateway, $payload);
        $handler->handle();
    }
}
