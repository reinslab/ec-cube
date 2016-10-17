<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Entity;
use Eccube\Util\EntityUtil;


/**
 * Information about Gmo module
 * (substitution for dtb_module of Eccube 2)
 */
class GmoMember extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;
    
    /**
     * @var integer
     */
    private $Customer;

    /**
     * @var integer
     */
    private $customer_id;
    
    /**
     * @var string
     */
    private $old_member_id;
    /**
     * @var string
     */
    private $new_member_id;
    /**
     * @var string
     */
    private $customer_create_date;
    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * Constructor
     */
    public function __construct()
    {
    }
    
    /**
     * Get customer_id
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Set customer_id
     *
     * @param  string customer_id
     * @return GmoMember.php
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;

        return $this;
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set old_member_id
     *
     * @param  string $old_member_id
     * @return Module
     */
    public function setOldMemberId($old_member_id)
    {
        $this->old_member_id = $old_member_id;

        return $this;
    }

    /**
     * Get old_member_id
     *
     * @return string
     */
    public function getOldMemberId()
    {
        return $this->old_member_id;
    }

    /**
     * Set new_member_id
     *
     * @param  string $new_member_id
     * @return Module
     */
    public function setNewMemberId($new_member_id)
    {
        $this->new_member_id = $new_member_id;

        return $this;
    }

    /**
     * Get new_member_id
     *
     * @return string
     */
    public function getNewMemberId()
    {
        return $this->new_member_id;
    }

    /**
     * Set create_date
     *
     * @param  \DateTime $createDate
     * @return Module
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set customer_create_date
     *
     * @param  \DateTime $createDate
     * @return Module
     */
    public function setCustomerCreateDate($createDate)
    {
        $this->customer_create_date = $createDate;

        return $this;
    }

    /**
     * Get customer_create_date
     *
     * @return \DateTime
     */
    public function getCustomerCreateDate()
    {
        return $this->customer_create_date;
    }

    /**
     * Set update_date
     *
     * @param  \DateTime $updateDate
     * @return Module
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * Set Customer
     *
     * @param  \Eccube\Entity\Customer $customer
     * @return Order
     */
    public function setCustomer(\Eccube\Entity\Customer $customer = null)
    {
        $this->Customer = $customer;

        return $this;
    }

    /**
     * Get Customer
     *
     * @return \Eccube\Entity\Customer
     */
    public function getCustomer()
    {
        if (EntityUtil::isEmpty($this->Customer)) {
            return null;
        }
        return $this->Customer;
    }
}
