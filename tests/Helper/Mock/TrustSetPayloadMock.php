<?php

namespace Xrpl\XummForWoocommerce\Tests\Helper\Mock;

use Xrpl\XummSdkPhp\Response\GetPayload\XummPayload;
use Xrpl\XummSdkPhp\Response\GetPayload\Application;
use Xrpl\XummSdkPhp\Response\GetPayload\PayloadMeta;
use Xrpl\XummSdkPhp\Response\GetPayload\Payload;
use Xrpl\XummSdkPhp\Response\GetPayload\Response as PayloadResponse;

final class TrustSetPayloadMock
{
    public static function create(string $account = 'rAccountTest', string $uuid = 'some-uuid'): XummPayload
    {
        return new XummPayload(
            new Payload(
                'TrustSet',
                'me',
                [],
                new \DateTimeImmutable(),
                new \DateTimeImmutable(),
                300,
            ),
            new PayloadMeta(
                $uuid,
                true,
                false,
                'me',
                'me',
                false,
                false,
                false,
                true,
                true,
                true,
                false,
                false,
                false
            ),
            new Application(
                'cool app',
                'A very cool app.',
                'some-uuid',
                false,
                'http://example.org/icon.jpg'
            ),
            new PayloadResponse('some-txid', null, null, null, null, '', null, $account)
        );
    }
}
