<?php

namespace SprykerFeature\Shared\Tax\Dependency\Transfer;

use Generated\Shared\Transfer\TaxRateTransfer;
use SprykerEngine\Shared\Transfer\TransferInterface;

interface TaxSetInterface extends TransferInterface
{
    /**
     * @param int $totalAmount
     *
     * @return $this
     */
    public function setTotalAmount($totalAmount);

    /**
     * @return int
     */
    public function getTotalAmount();

    /**
     * @param \ArrayObject $taxRates
     *
     * @return $this
     */
    public function setTaxRates(\ArrayObject $taxRates);

    /**
     * @return TaxRateInterface[]|\ArrayObject
     */
    public function getTaxRates();

    /**
     * @param TaxRateTransfer $taxRate
     *
     * @return $this
     */
    public function addTaxRate(TaxRateTransfer $taxRate);
}
