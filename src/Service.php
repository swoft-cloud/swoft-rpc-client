<?php

namespace Swoft\Rpc\Client;

use Swoft\App;
use Swoft\Pool\ConnectInterface;
use Swoft\Pool\ConnectPool;
use Swoft\Circuit\CircuitBreaker;
use Swoft\Rpc\Client\Service\AbstractServiceConnect;
use Swoft\Rpc\Client\Service\ServiceCoResult;
use Swoft\Rpc\Client\Service\ServiceDataResult;
use Swoft\Core\ResultInterface;

/**
 * The service class
 */
class Service
{
    /**
     * The name of service
     *
     * @var string
     */
    private $name;

    /**
     * The handler of fallback
     *
     * @var callable
     */
    private $callback;

    /**
     * The name of pool
     *
     * @var string
     */
    private $poolName;

    /**
     * The name of breaker
     *
     * @var string
     */
    private $breakName;

    /**
     * The name of packer
     *
     * @var string
     */
    private $packerName;

    /**
     * Service constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Do call service
     *
     * @param string $func
     * @param array  $params
     *
     * @return ResultInterface
     */
    public function call(string $func, array $params)
    {
        $profileKey = $this->name . '->' . $func;

        $connectPool    = $this->getPool();
        $circuitBreaker = $this->getBreaker();

        /* @var $client AbstractServiceConnect */
        $client = $connectPool->getConnect();
        $packer = service_packer();

        $data     = $packer->formatData($func, $params);
        $packData = $packer->pack($data);
        $result   = $circuitBreaker->call([$client, 'send'], [$packData], $this->callback);

        // 错误处理
        if ($result === null || $result === false) {
            return null;
        }

        return $this->getResult($client, $profileKey, $connectPool, $result);
    }

    /**
     * @param callable $callback
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param string $poolName
     */
    public function setPoolName(string $poolName)
    {
        $this->poolName = $poolName;
    }

    /**
     * @param string $breakName
     */
    public function setBreakName(string $breakName)
    {
        $this->breakName = $breakName;
    }

    /**
     * @param string $packerName
     */
    public function setPackerName(string $packerName)
    {
        $this->packerName = $packerName;
    }

    /**
     * @param int $retryTimes
     */
    public function setRetryTimes(int $retryTimes)
    {
        $this->retryTimes = $retryTimes;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
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
        if (App::isCorContext()) {
            return new ServiceCoResult($client, $profileKey, $connectPool);
        }

        return new ServiceDataResult($result);
    }

    /**
     * @return CircuitBreaker
     */
    private function getBreaker()
    {
        if ($this->breakName === null) {
            return App::getBreaker($this->name);
        }

        return App::getBreaker($this->breakName);
    }

    /**
     * @return ConnectPool
     */
    private function getPool()
    {
        if ($this->poolName === null) {
            return App::getPool($this->name);
        }

        return App::getPool($this->poolName);
    }
}