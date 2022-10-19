<?php declare(strict_types=1);

namespace App\Mdc\Service;

use App\Exceptions\ServiceException;
use SoapFault;

/**
 * Class ErrorParser
 * @package App\Mdc\Service
 */
class SoapFaultErrorParser
{
    /**
     * Regex match for hard error
     */
    private const HARD_ERROR_REGEX = '/^SOAP\-ERROR.*|^SQLSTATE.*/';

    /**
     * @param SoapFault   $error
     * @param string|null $message
     * @return ServiceException
     */
    public function convertToServiceException(
        SoapFault $error,
        string $message = null
    ): ServiceException {
        return new ServiceException(
            $message ?? $error->getMessage(),
            $this->getErrorType($error)
        );
    }

    /**
     * @param SoapFault $error
     * @return int
     */
    public function getErrorType(SoapFault $error): int
    {
        $code = strtolower($error->faultcode);
        if ($code === 'http') {
            return ServiceException::NETWORK_ERROR;
        }

        if ($code === 'client') {
            return ServiceException::ENDPOINT_ERROR;
        }

        if ($this->isMessageHardError($error->getMessage())) {
            return ServiceException::ENDPOINT_ERROR;
        }

        return ServiceException::ENDPOINT_SOFT_ERROR;
    }

    /**
     * Is the response message consider as hard error
     *
     * @param $message
     *
     * @return false|int
     */
    private function isMessageHardError($message)
    {
        return preg_match(
            self::HARD_ERROR_REGEX,
            $message
        );
    }
}
