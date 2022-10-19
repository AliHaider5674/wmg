<?php
namespace App\Models\Validator;

interface ValidatorInterface
{
    public function isPassed($arg, $data);
}
