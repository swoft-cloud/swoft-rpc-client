<?php

namespace Swoft\Rpc\Client\Service;

use Swoft\App;
use Swoft\Core\AbstractCoResult;

/**
 * The cor result of server
 */
class ServiceCoResult extends AbstractCoResult
{
    /**
     * @param array ...$params
     *
     * @return mixed
     */
    public function getResult(...$params)
    {
        $result = $this->recv(true);
        App::debug('service result =' . json_encode($result));
        $packer = service_packer();
        $result = $packer->unpack($result);
        $data = $packer->checkData($result);
        return $data;
    }
}