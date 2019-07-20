<?php
namespace Puzzley\SMS;

use Puzzley\SMS\AbstractSMS;
use Puzzley\SMS\Enum;
use Puzzley\SMS\ServiceInterface;
use Puzzley\SMS\Exception\InvalidServiceException;

/**
 * class ServiceFactory
 */
class ServiceFactory
{
    /**
     * @var ServiceInterface
     */
    protected $service;

    /**
     * @param string $service
     * @return ServiceInterface|void
     */
    public function __construct($service = null)
    {
        if (!is_null($service)) {
            return $this->$service();
        }
    }

    /**
     * @return array
     */
    private function getSupportedServices()
    {
        return [
            Enum::PAYAM_RESAN,
            Enum::KAVE_NEGAR,
        ];
    }

    /**
     * Making SMS service object
     * @param string $service specify SMS service
     * @return AbstractSMS
     */
    public function make($service)
    {
        $FQCN = 'Puzzley\\SMS\\' . $service . '\\' . $service;
        return new $FQCN();
    }

    /**
     * @param string $name
     * @param array $arguments
     * 
     * @throws InvalidServiceException
     * 
     * @return ServiceInterface
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, $this->getSupportedServices())) {
            return $this->make($name);
        }
        throw new InvalidServiceException();
    }
}
