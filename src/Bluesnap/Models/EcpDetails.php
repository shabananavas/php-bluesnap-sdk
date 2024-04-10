<?php

namespace Bluesnap\Models;

/**
 * Class EcpDetails
 */
class EcpDetails extends Model
{
    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    protected $children = ['ecp' => self::ITEM, 'billingContactInfo' => self::ITEM];

    /**
     * @var Ecp
     */
    public $ecp;

    /**
     * @var BillingContactInfo
     */
    public $billingContactInfo;

    /**
     * @var string
     */
    public $status;
}
