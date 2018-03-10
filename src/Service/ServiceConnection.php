<?php

namespace Swoft\Rpc\Client\Service;

use Swoft\App;
use Swoft\Rpc\Client\Exception\RpcClientException;
use Swoole\Coroutine\Client;

/**
 * Service connection
 */
class ServiceConnection extends AbstractServiceConnection
{
    /**
     * @var Client
     */
    protected $connection;

    /**
     * @throws RpcClientException
     */
    public function createConnection()
    {
        $client = new Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);

        $address = $this->pool->getConnectionAddress();
        $timeout = $this->pool->getTimeout();
        list($host, $port) = explode(':', $address);
        if (!$client->connect($host, $port, $timeout)) {
            $error = sprintf('Service connect fail errorCode=%s host=%s port=%s', $client->errCode, $host, $port);
            App::error($error);
            throw new RpcClientException($error);
        }
        $this->connection = $client;
    }

    /**
     * @return void
     */
    public function reconnect()
    {
        $this->createConnection();
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        return $this->connection->isConnected();
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    public function send(string $data): bool
    {
        return $this->connection->send($data);
    }

    /**
     * @return string
     */
    public function recv(): string
    {
        return $this->connection->recv();
    }
}
