<?php

namespace App\Core\Observers;

use App\Core\Exceptions\Mutators\FormatterNotDefinedException;
use App\Core\Exceptions\Mutators\MutatorException;
use App\Core\Exceptions\Mutators\ValidatorNotDefinedException;
use App\Core\Services\Mutators\TaxId\TaxIdMutatorFactory;
use App\Models\OrderAddress;

/**
 * Class OrderAddressObserver
 * @package App\Core
 */
class OrderAddressObserver
{
    /**
     * @var TaxIdMutatorFactory
     */
    private $taxIdMutatorFactory;

    /**
     * OrderAddressObserver constructor.
     * @param TaxIdMutatorFactory $taxIdMutatorFactory
     */
    public function __construct(TaxIdMutatorFactory $taxIdMutatorFactory)
    {
        $this->taxIdMutatorFactory = $taxIdMutatorFactory;
    }

    /**
     * @param OrderAddress $address
     * @throws FormatterNotDefinedException
     * @throws MutatorException
     * @throws ValidatorNotDefinedException
     */
    public function saving(OrderAddress $address): void
    {
        if (empty($address->tax_id)) {
            return;
        }

        $mutator = $this->taxIdMutatorFactory->create($address['country_code']);
        $address->tax_id = $mutator->mutate($address->tax_id);
    }
}
