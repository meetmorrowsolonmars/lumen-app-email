<?php

namespace App\Domain;

use GuzzleHttp;
use Psr\Log\LoggerInterface;

/**
 * Class AntispamMailService
 * @package App\Domain
 */
class AntispamMailService
{
    /**
     * @type GuzzleHttp\Client
     */
    protected GuzzleHttp\Client $antispamServiceClient;

    /**
     * @type  LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(GuzzleHttp\Client $client, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->antispamServiceClient = $client;
    }


    /**
     * @param string $email
     * @return string|null transactionId
     */
    public function takePayment(string $email): ?string
    {
        try {
            $response = $this->antispamServiceClient->request("POST", "mails/pay", [
                "json" => [
                    "from" => $email
                ],
                "headers" => [
                    "CliAuth" => config("app.cli_auth")
                ]
            ]);
        } catch (GuzzleHttp\Exception\GuzzleException $exception) {
            $this->logger->info("It is not possible to charge for sending an email:", [
                "from" => $email,
                "message" => $exception->getMessage()
            ]);
            // skip if the mail is not paid
            return null;
        }

        $data = json_decode($response->getBody(), true);

        return $data["transaction_id"];
    }
}
