<?php
namespace App\Models\Service\Event;

use App\Models\Service;
use App\Models\Service\Model\Serialize;
use App\Models\Validator\RegexRuleValidator;

/**
 * Validator that validate service event rules
 * with a given request model
 *
 * Class ServiceRuleValidator
 * @category WMG
 * @package  App\Models\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ServiceRuleValidator
{
    private $metaDataExtractor;
    private $regexRuleValidator;
    public function __construct(
        MetaDataExtractor $metaDataExtractor,
        RegexRuleValidator $regexRuleValidator
    ) {
        $this->metaDataExtractor = $metaDataExtractor;
        $this->regexRuleValidator = $regexRuleValidator;
    }

    /**
     * Check if event rule is passed.
     *
     * @param \App\Models\Service $service
     * @param \App\Models\Service\Model\Serialize $serviceModel
     *
     * @return bool
     */
    public function isPassed(Service $service, Serialize $serviceModel)
    {
        $rules = $service->getEventRules();
        if (empty($rules)) {
            return true;
        }
        $data = $this->metaDataExtractor->getMetaData($serviceModel);
        return $this->regexRuleValidator->isPassed($rules, $data);
    }
}
