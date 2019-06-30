<?php

namespace AppBundle\Normalizer;

interface NormalizerInterface
{
    public function normalize(\Exception $exception);

    public function supports(\Exception $exception);
}