<?php

namespace App\IMMuleSoft\Handler\Processor;

use App\IMMuleSoft\Models\ImMulesoftRequest;

interface ProcessorInterface
{
    public function handle(ImMulesoftRequest $request);
}
