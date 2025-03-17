<?php

namespace Iidev\ZohoCRM\Core;

interface ZohoAwareInterface
{
    public function getZohoModel();
    public function setZohoModel($zohoModel): self;
}