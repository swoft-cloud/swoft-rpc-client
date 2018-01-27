<?php

namespace Swoft\Rpc\Client\Service;

use Swoft\App;
use Swoole\Coroutine\Client;

/**
 * Class ServiceConnect
 *
 * @package Swoft\Rpc\Client\Service
 */
class ServiceConnect extends AbstractServiceConnect
{
    /**
     * @var Client
     */
    protected $connect;

    public function createConnect()
    {
        $client = new Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);

        $address = $this->connectPool->getConnectAddress();
        $timeout = $this->connectPool->getTimeout();
        list($host, $port) = explode(':', $address);
        if (! $client->connect($host, $port, $timeout)) {
            App::error('Service connect fail errorCode=' . $client->errCode . ' host=' . $host . ' port=' . $port);
            return null;
        }
        $this->connect = $client;
    }

    public function reConnect()
    {
    }

    /**
     * @param string $data
     * @return bool
     */
    public function send(string $data): bool
    {
        return $this->connect->send($data);
    }

    /**
     * @return string
     */
    public function recv(): string
    {
        return $this->connect->recv();
    }
}
