<?php

namespace Swoft\Rpc\Client;

use Swoft\App;
use Swoft\Core\ResultInterface;
use Swoft\Pool\ConnectInterface;
use Swoft\Rpc\Client\Exception\RpcClientException;
use Swoft\Rpc\Client\Service\AbstractServiceConnect;
use Swoft\Pool\ConnectPool;
use Swoft\Circuit\CircuitBreaker;
use Swoft\Rpc\Client\Service\ServiceCoResult;
use Swoft\Rpc\Client\Service\ServiceDataResult;

/**
 * The service trait
 */
class Service
{
    /**
     * The prefix of defer method
     *
     * @var string
     */
    const DEFER_PREFIX = 'defer';

    /**
     * The name of service
     *
     * @var string``
     */
    protected $name;

    /**
     * @var string
     */
    protected $version;

    /**
     * The name of pool
     *
     * @var string
     */
    protected $poolName;

    /**
     * The name of breaker
     *
     * @var string
     */
    protected $breakerName;

    /**
     * The name of packer
     *
     * @var string
     */
    protected $packerName;

    /**
     * @var string
     */
    protected $interface;

    /**
     * Do call service
     *
     * @param string $func
     * @param array  $params
     *
     * @return mixed
     */
    public function call(string $func, array $params)
    {
        $profileKey = $this->interface . '->' . $func;

        $connectPool    = $this->getPool();
        $circuitBreaker = $this->getBreaker();

        /* @var $client AbstractServiceConnect */
        $client = $connectPool->getConnect();

        $packer   = service_packer();
        $type     = $this->getPackerName();
        $data     = $packer->formatData($this->interface, $this->version, $func, $params);
        $packData = $packer->pack($data, $type);
        $result   = $circuitBreaker->call([$client, 'send'], [$packData]);

        if ($result === null || $result === false) {
            return null;
        }

        App::profileStart($profileKey);
        $result = $client->recv();
        App::profileEnd($profileKey);
        $connectPool->release($client);

        App::debug(sprintf('%s call %s success, data=%', $this->interface, $func, json_encode($data, JSON_UNESCAPED_UNICODE)));
        $result = $packer->unpack($result);
        $data   = $packer->checkData($result);

        return $data;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return ResultInterface
     * @throws RpcClientException
     */
    function __call(string $name, array $arguments)
    {
        $method = $name;
        $prefix = self::DEFER_PREFIX;
        if (strpos($name, $prefix) !== 0) {
            throw new RpcClientException(sprintf('the method of %s is not exist! ', $name));
        }

        if ($name == $prefix) {
            $method = array_shift($arguments);
        } elseif (strpos($name, $prefix) === 0) {
            $method = lcfirst(ltrim($name, $prefix));
        }

        return $this->deferCall($method, $arguments);
    }

    /**
     * Do call service
     *
     * @param string $func
     * @param array  $params
     *
     * @return ResultInterface
     */
    private function deferCall(string $func, array $params)
    {
        $profileKey = $this->interface . '->' . $func;

        $connectPool    = $this->getPool();
        $circuitBreaker = $this->getBreaker();

        /* @var $client AbstractServiceConnect */
        $client = $connectPool->getConnect();

        $packer   = service_packer();
        $type     = $this->getPackerName();
        $data     = $packer->formatData($this->interface, $this->version, $func, $params);
        $packData = $packer->pack($data, $type);
        $result   = $circuitBreaker->call([$client, 'send'], [$packData]);

        // 错误处理
        if ($result === null || $result === false) {
            return null;
        }

        return $this->getResult($client, $profileKey, $connectPool, $result);
    }

    /**
     * @param ConnectInterface $client
     * @param string           $profileKey
     * @param ConnectPool      $connectPool
     * @param mixed            $result
     *
     * @return ResultInterface
     */
    private function getResult(ConnectInterface $client, string $profileKey, ConnectPool $connectPool, $result)
    {
        if (App::isCoContext()) {
            return new ServiceCoResult($client, $profileKey, $connectPool);
        }

        return new ServiceDataResult($result);
    }

    /**
     * @return CircuitBreaker
     */
    private function getBreaker()
    {
        if (empty($this->breakerName)) {
            return App::getBreaker($this->name);
        }

        return App::getBreaker($this->breakerName);
    }

    /**
     * @return ConnectPool
     */
    private function getPool()
    {
        if (empty($this->poolName)) {
            return App::getPool($this->name);
        }

        return App::getPool($this->poolName);
    }

    /**
     * @return string
     */
    private function getPackerName()
    {
        if (empty($this->packerName)) {
            return "";
        }

        return $this->packerName;
    }
}