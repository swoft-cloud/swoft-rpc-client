<?php

namespace Swoft\Rpc\Client\Service;

use Swoft\App;
use Swoft\Core\AbstractResult;

/**
 * RPC Result
 */
class ServiceResult extends AbstractResult
{

    /**
     * @return null|mixed
     */
    public function getResult()
    {
        if ($this->sendResult === null || $this->sendResult === false) {
            return null;
        }
        $result = $this->recv();

        App::debug('RPC Result，Data=' . json_encode($result));
        $packer = service_packer();
        $result = $packer->unpack($result);
        $data = $packer->checkData($result);
        return $data;
    }
}
