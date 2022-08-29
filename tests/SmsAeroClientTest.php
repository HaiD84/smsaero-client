<?php

declare(strict_types=1);

namespace Feech\SmsAeroTest;

use Feech\SmsAero\Auth\Auth;
use Feech\SmsAero\Client\ClientGuzzle;
use Feech\SmsAero\Dto;
use Feech\SmsAero\Exception\BadResponseException;
use Feech\SmsAero\Exception\TransportException;
use Feech\SmsAero\Sms\Sms;
use Feech\SmsAero\SmsAeroClient;
use Feech\SmsAeroTest\StubData;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;

final class SmsAeroClientTest extends TestCase
{
    /** @var MockHandler */
    private $mockHandler;

    /** @var SmsAeroClient */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);

        $httpClient = new ClientGuzzle(
            new Auth('user@test.mail', 'password'),
            new Client(['handler' => $handlerStack])
        );

        $serializer = SerializerBuilder::create()->build();

        $this->client = new SmsAeroClient($httpClient, $serializer);
    }

    public function testAuthWhenSuccess(): void
    {
        $this->mockHandler->append(function (Request $request, array $options) {
            $this->assertStringContainsString(
                '/v2/auth',
                (string) $request->getUri()
            );

            return new Response(200, [], StubData::authSuccessResponse());
        });

        $result = $this->client->auth();

        $this->assertTrue($result->success);
        $this->assertSame('Successful authorization.', $result->message);
    }

    public function testAuthWhenErrorShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(401, [], StubData::authErrorResponse())
        );

        $this->expectException(TransportException::class);

        $this->client->auth();
    }

    public function testAuthWhenUnknownResponseShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $this->expectException(BadResponseException::class);

        $this->client->auth();
    }

    public function testTestSendWhenSuccess(): void
    {
        $this->mockHandler->append(function (Request $request, array $options) {
            $this->assertStringContainsString(
                '/v2/sms/testsend',
                (string) $request->getUri()
            );

            return new Response(200, [], StubData::sendToSingleNumberSuccessResponse());
        });

        $sms = Sms::toSingleNumber('79990000000', 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $result = $this->client->testSend($sms);

        $this->assertTrue($result->success);
        $this->assertNull($result->message);

        $this->assertInstanceOf(Dto\SmsMessageResult::class, $result->data);
        $this->assertSmsMessageResultToTestData($result->data);
    }

    private function assertSmsMessageResultToTestData(Dto\SmsMessageResult $result): void
    {
        $this->assertSame(5, $result->id);
        $this->assertSame('BIZNES', $result->from);
        $this->assertSame('79990000000', $result->number);
        $this->assertSame('Test text', $result->text);
        $this->assertSame(0, $result->status);
        $this->assertSame('queue', $result->extendStatus);
        $this->assertSame(Sms::CHANNEL_TYPE_DIRECT, $result->channel);
        $this->assertSame(2.2, $result->cost);
        $this->assertSame(1532342510, $result->dateCreate);
        $this->assertSame(1532342510, $result->dateSend);
    }

    public function testTestSendWhenUnknownResponseShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $sms = Sms::toSingleNumber('79990000000', 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $this->expectException(BadResponseException::class);

        $this->client->testSend($sms);
    }

    public function testTestSendWhenRequestValidationError(): void
    {
        $this->mockHandler->append(
            new Response(400, [], StubData::sendToSingleNumberErrorResponse())
        );

        $sms = Sms::toSingleNumber('00000000000', 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $this->expectException(TransportException::class);

        $this->client->testSend($sms);
    }

    public function testTestSendWhenSendToMultipleNumbersShouldThrowException(): void
    {
        $sms = Sms::toMultipleNumbers(['79990000000'], 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $this->expectException(\InvalidArgumentException::class);

        $this->client->testSend($sms);
    }

    public function testSendWhenSuccess(): void
    {
        $this->mockHandler->append(function (Request $request, array $options) {
            $this->assertStringContainsString(
                '/v2/sms/send',
                (string) $request->getUri()
            );

            return new Response(200, [], StubData::sendToSingleNumberSuccessResponse());
        });

        $sms = Sms::toSingleNumber('79990000000', 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $result = $this->client->send($sms);

        $this->assertTrue($result->success);
        $this->assertNull($result->message);

        $this->assertInstanceOf(Dto\SmsMessageResult::class, $result->data);
        $this->assertSmsMessageResultToTestData($result->data);
    }

    public function testSendWhenUnknownResponseShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $this->expectException(BadResponseException::class);

        $sms = Sms::toSingleNumber('79990000000', 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $this->client->send($sms);
    }

    public function testSendWhenSendToMultipleNumbersShouldThrowException(): void
    {
        $sms = Sms::toMultipleNumbers(['79990000000'], 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $this->expectException(\InvalidArgumentException::class);

        $this->client->send($sms);
    }

    public function testBulkSendWhenSuccess(): void
    {
        $this->mockHandler->append(function (Request $request, array $options) {
            $this->assertStringContainsString(
                '/v2/sms/send',
                (string) $request->getUri()
            );

            return new Response(200, [], StubData::sendToMultipleNumbersSuccessResponse());
        });

        $sms = Sms::toMultipleNumbers(['79990000000'], 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $result = $this->client->bulkSend($sms);

        $this->assertTrue($result->success);
        $this->assertNull($result->message);

        $this->assertIsArray($result->data);
        $this->assertCount(1, $result->data);
        $messageResult = reset($result->data);
        $this->assertInstanceOf(Dto\SmsMessageResult::class, $messageResult);
        $this->assertSmsMessageResultToTestData($messageResult);
    }

    public function testBulkSendWhenUnknownResponseShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $this->expectException(BadResponseException::class);

        $sms = Sms::toMultipleNumbers(['79990000000'], 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $this->client->bulkSend($sms);
    }

    public function testBulkSendWhenSendToSingleNumberShouldThrowException(): void
    {
        $sms = Sms::toSingleNumber('79990000000', 'Test text', Sms::CHANNEL_TYPE_DIRECT);

        $this->expectException(\InvalidArgumentException::class);

        $this->client->bulkSend($sms);
    }

    public function testBalanceWhenSuccess(): void
    {
        $this->mockHandler->append(function (Request $request, array $options) {
            $this->assertStringContainsString(
                '/v2/balance',
                (string) $request->getUri()
            );

            return new Response(200, [], StubData::balanceSuccessResponse());
        });

        $result = $this->client->balance();

        $this->assertTrue($result->success);
        $this->assertNull($result->message);

        $this->assertInstanceOf(Dto\BalanceResult::class, $result->data);
        $this->assertSame(1389.26, $result->data->balance);
    }

    public function testBalanceWhenUnknownResponseShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $this->expectException(BadResponseException::class);

        $this->client->balance();
    }

    public function testFlashCallWhenSuccess(): void
    {
        $this->mockHandler->append(function (Request $request, array $options) {
            $this->assertStringContainsString(
                '/v2/flashcall/send',
                (string) $request->getUri()
            );

            return new Response(200, [], StubData::flashCallSendSuccessResponse());
        });

        $result = $this->client->flashCall('79990000000', '1234');

        $this->assertTrue($result->success);
        $this->assertNull($result->message);

        $this->assertInstanceOf(Dto\FlashCallStatus::class, $result->data);
        $this->assertFlashCallStatusToTestData($result->data);
    }

    private function assertFlashCallStatusToTestData(Dto\FlashCallStatus $result): void
    {
        $this->assertSame(1, $result->id);
        $this->assertSame(0, $result->status);
        $this->assertSame('1234', $result->code);
        $this->assertSame('79990000000', $result->phone);
        $this->assertSame(0.59, $result->cost);
        $this->assertSame(1646926190, $result->timeCreate);
        $this->assertSame(1646926190, $result->timeUpdate);
    }

    public function testFlashCallWhenUnknownResponseShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $this->expectException(BadResponseException::class);

        $this->client->flashCall('79990000000', '1234');
    }

    public function testViberSendWhenSuccess(): void
    {
        $this->mockHandler->append(function (Request $request, array $options) {
            $this->assertStringContainsString(
                '/v2/viber/send',
                (string) $request->getUri()
            );

            $body = (string) $request->getBody();
            $this->assertNotEmpty($body);
            $params = [];
            parse_str($body, $params);
            $this->assertEquals(
                [
                    'number' => '79990000000',
                    'sign' => 'Hello!',
                    'channel' => 'OFFICIAL',
                    'text' => 'your text',
                ],
                $params
            );

            return new Response(200, [], StubData::viberSendSuccessResponse());
        });

        $request = Dto\ViberSendRequest::toSingleNumber(
            '79990000000',
            'Hello!',
            Dto\ViberSendRequest::CHANNEL_OFFICIAL,
            'your text'
        );
        $result = $this->client->viberSend($request);

        $this->assertTrue($result->success);
        $this->assertNull($result->message);

        $this->assertInstanceOf(Dto\ViberStatus::class, $result->data);
        $this->assertSame(1, $result->data->id);
        $this->assertSame(1511153253, $result->data->dateCreate);
        $this->assertSame(1511153253, $result->data->dateSend);
        $this->assertSame(3, $result->data->count);
        $this->assertSame('Hello!', $result->data->sign);
        $this->assertSame('OFFICIAL', $result->data->channel);
        $this->assertSame('your text', $result->data->text);
        $this->assertSame(2.25, $result->data->cost);
        $this->assertSame(1, $result->data->status);
        $this->assertSame('moderation', $result->data->extendStatus);
        $this->assertSame(0, $result->data->countSend);
        $this->assertSame(0, $result->data->countDelivered);
        $this->assertSame(0, $result->data->countWrite);
        $this->assertSame(0, $result->data->countUndelivered);
        $this->assertSame(0, $result->data->countError);
    }

    public function testViberSendWhenUnknownResponseShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $this->expectException(BadResponseException::class);

        $request = Dto\ViberSendRequest::toSingleNumber(
            '79990000000',
            'Hello!',
            Dto\ViberSendRequest::CHANNEL_OFFICIAL,
            'your text'
        );
        $this->client->viberSend($request);
    }

    public function testViberStatisticWhenSuccess(): void
    {
        $this->mockHandler->append(function (Request $request, array $options) {
            $this->assertStringContainsString(
                '/v2/viber/statistic',
                (string) $request->getUri()
            );

            return new Response(200, [], StubData::viberStatisticSuccessResponse());
        });

        $result = $this->client->viberStatistic(1);

        $this->assertTrue($result->success);
        $this->assertNull($result->message);

        $this->assertIsArray($result->data);
        $this->assertCount(3, $result->data);
        foreach ($result->data as $numberData) {
            $this->assertInstanceOf(Dto\ViberNumberStatus::class, $numberData);
        }

        $this->assertSame('79990000000', $result->data[0]->number);
        $this->assertSame(0, $result->data[0]->status);
        $this->assertSame('send', $result->data[0]->extendStatus);
        $this->assertSame(1511153341, $result->data[0]->dateSend);

        $this->assertSame('79990000001', $result->data[1]->number);
        $this->assertSame(2, $result->data[1]->status);
        $this->assertSame('write', $result->data[1]->extendStatus);
        $this->assertSame(1511153341, $result->data[1]->dateSend);
    }

    public function testViberStatisticWhenUnknownResponseShouldThrowException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $this->expectException(BadResponseException::class);

        $this->client->viberStatistic(1);
    }
}
