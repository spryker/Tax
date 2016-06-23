<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Tax\Business\Model;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Tax\Business\Model\ProductItemTaxRateCalculator;
use Spryker\Zed\Tax\Business\Model\TaxDefault;
use Spryker\Zed\Tax\Persistence\TaxQueryContainer;

/**
 * @group TaxRate
 * @group TaxCountry
 */
class TaxRateCalculationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testCalculateTaxRateForDefaultCountry()
    {
        $quoteTransfer = $this->createQuoteTransferWithoutShippingAddress();

        $taxAverage = $this->getEffectiveTaxRateByQuoteTransfer($quoteTransfer, $this->getMockDefaultTaxRates());
        $this->assertEquals(15, $taxAverage);
    }

    /**
     * @return void
     */
    public function testCalculateTaxRateForDifferentCountry()
    {
        $quoteTransfer = $this->createQuoteTransferWithShippingAddress();

        $taxAverage = $this->getEffectiveTaxRateByQuoteTransfer($quoteTransfer, $this->getMockCountryBasedTaxRates());
        $this->assertEquals(17, $taxAverage);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return float
     */
    protected function getEffectiveTaxRateByQuoteTransfer(QuoteTransfer $quoteTransfer, $mockData)
    {
        $productItemTaxRateCalculatorMock = $this->createProductItemTaxRateCalculator();
        $productItemTaxRateCalculatorMock->method('findTaxRatesByAllIdProductAbstractsAndCountry')->willReturn($mockData);

        $productItemTaxRateCalculatorMock->recalculate($quoteTransfer);
        $taxAverage = $this->getProductItemsTaxRateAverage($quoteTransfer);

        return $taxAverage;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Tax\Business\Model\ProductItemTaxRateCalculator
     */
    protected function createProductItemTaxRateCalculator()
    {
        return $productItemTaxRateCalculatorMock = $this->getMock(ProductItemTaxRateCalculator::class, ['findTaxRatesByAllIdProductAbstractsAndCountry'], [
            $this->createQueryContainerMock(),
            $this->createTaxDefault(),
        ]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Tax\Business\Model\TaxDefault
     */
    public function createTaxDefault()
    {
        $taxDefaultMock = $this->getMockBuilder(TaxDefault::class)
            ->disableOriginalConstructor()
            ->getMock();

        $taxDefaultMock
            ->expects($this->any())
            ->method('getDefaultCountry')
            ->willReturn('DE');

        $taxDefaultMock
            ->expects($this->any())
            ->method('getDefaultTaxRate')
            ->willReturn(19);

        return $taxDefaultMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Tax\Persistence\TaxQueryContainerInterface
     */
    protected function createQueryContainerMock()
    {
        return $this->getMockBuilder(TaxQueryContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return float
     */
    protected function getProductItemsTaxRateAverage(QuoteTransfer $quoteTransfer)
    {
        $taxSum = 0;
        foreach ($quoteTransfer->getItems() as $item) {
            $taxSum += $item->getTaxRate();
        }

        $taxAverage = $taxSum / count($quoteTransfer->getItems());

        return $taxAverage;
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransferWithoutShippingAddress()
    {
        $quoteTransfer = $this->createQuoteTransfer();

        $this->createItemTransfers($quoteTransfer);

        return $quoteTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransferWithShippingAddress()
    {
        $quoteTransfer = $this->createQuoteTransfer();

        $this->createItemTransfers($quoteTransfer);

        $addressTransfer = new AddressTransfer();
        $addressTransfer->setIso2Code('AT');

        $quoteTransfer->setShippingAddress($addressTransfer);

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    protected function createItemTransfers(QuoteTransfer $quoteTransfer)
    {
        $itemTransfer1 = $this->createProductItemTransfer(1);
        $quoteTransfer->addItem($itemTransfer1);

        $itemTransfer2 = $this->createProductItemTransfer(2);
        $quoteTransfer->addItem($itemTransfer2);
    }

    /**
     * @param $id
     *
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    protected function createProductItemTransfer($id)
    {
        $itemTransfer = $this->createItemTransfer();
        $itemTransfer->setIdProductAbstract($id);

        return $itemTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransfer()
    {
        return new QuoteTransfer();
    }

    /**
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    protected function createItemTransfer()
    {
        return new ItemTransfer();
    }

    /**
     * @return array
     */
    protected function getMockDefaultTaxRates()
    {
        return [
            [
                TaxQueryContainer::COL_ID_ABSTRACT_PRODUCT => 1,
                TaxQueryContainer::COL_SUM_TAX_RATE => 11,
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getMockCountryBasedTaxRates()
    {
        return [
            [
                TaxQueryContainer::COL_ID_ABSTRACT_PRODUCT => 1,
                TaxQueryContainer::COL_SUM_TAX_RATE => 20,
            ],
            [
                TaxQueryContainer::COL_ID_ABSTRACT_PRODUCT => 2,
                TaxQueryContainer::COL_SUM_TAX_RATE => 14,
            ],
        ];
    }

}
