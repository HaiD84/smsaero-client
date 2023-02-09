<?php

declare(strict_types=1);

namespace Feech\SmsAeroTest;

class StubData
{
    public static function authSuccessResponse(): string
    {
        return <<<JSON
{
    "success": true,
    "data": null,
    "message": "Successful authorization."
}
JSON;
    }

    public static function authErrorResponse(): string
    {
        return <<<JSON
{
    "success": false,
    "data": null,
    "message": "Your request was made with invalid credentials."
}
JSON;
    }

    public static function sendToSingleNumberSuccessResponse(): string
    {
        return <<<JSON
{
    "success": true,
    "data": {
        "id": 5,
        "from": "BIZNES",
        "number": "79990000000",
        "text": "Test text",
        "status": 0,
        "extendStatus": "queue",
        "channel": "DIRECT",
        "cost": 2.2,
        "dateCreate": 1532342510,
        "dateSend": 1532342510
    },
    "message": null
}
JSON;
    }

    public static function sendToSingleNumberErrorResponse(): string
    {
        return <<<JSON
{
    "success": false,
    "data": {
        "number": ["incorrect"]
    },
    "message": "Validation error."
}
JSON;
    }

    public static function sendToMultipleNumbersSuccessResponse(): string
    {
        return <<<JSON
{
    "success": true,
    "data": [
        {
            "id": 5,
            "from": "BIZNES",
            "number": "79990000000",
            "text": "Test text",
            "status": 0,
            "extendStatus": "queue",
            "channel": "DIRECT",
            "cost": 2.2,
            "dateCreate": 1532342510,
            "dateSend": 1532342510
        }
    ],
    "message": null
}
JSON;
    }

    public static function balanceSuccessResponse(): string
    {
        return <<<JSON
{
    "success": true,
    "data": {
        "balance": 1389.26
    },
    "message": null
}
JSON;
    }

    public static function flashCallSendSuccessResponse(): string
    {
        return <<<JSON
{
    "success": true,
    "data": {
        "id": 1,
        "status": 0,
        "code": "1234",
        "phone": "79990000000",
        "cost": "0.59",
        "timeCreate": 1646926190,
        "timeUpdate": 1646926190
    },
    "message": null
}
JSON;
    }

    public static function voiceCallSendSuccessResponse(): string
    {
        return <<<JSON
{
    "success": true,
    "data": {
        "id": 1,
        "status": 0,
        "code": "1234",
        "phone": "79990000000",
        "cost": "0.59",
        "timeCreate": 1646926190,
        "timeUpdate": 1646926190
    },
    "message": null
}
JSON;
    }

    public static function viberSendSuccessResponse(): string
    {
        return <<<JSON
{
    "success": true,
    "data": {
        "id": 1,
        "dateCreate": 1511153253,
        "dateSend": 1511153253,
        "count": 3,
        "sign": "Hello!",
        "channel": "OFFICIAL",
        "text": "your text",
        "cost": 2.25,
        "status": 1,
        "extendStatus": "moderation",
        "countSend": 0,
        "countDelivered": 0,
        "countWrite": 0,
        "countUndelivered": 0,
        "countError": 0
    },
    "message": null
}
JSON;
    }

    public static function viberStatisticSuccessResponse(): string
    {
        return <<<JSON
{
    "success": true,
    "data": {
        "0": {
            "number": "79990000000",
            "status": 0,
            "extendStatus": "send",
            "dateSend": 1511153341
        },
        "1": {
            "number": "79990000001",
            "status": 2,
            "extendStatus": "write",
            "dateSend": 1511153341
        },
        "2": {
            "number": "79990000003",
            "status": 2,
            "extendStatus": "write",
            "dateSend": 1511153341
        },
        "links": {
            "self": "/v2/viber/statistic?sendingId=1&page=1"
        }
    },
    "message": null
}
JSON;
    }
}
