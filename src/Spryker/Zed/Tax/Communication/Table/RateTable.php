<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Tax\Communication\Table;

use Orm\Zed\Country\Persistence\Map\SpyCountryTableMap;
use Orm\Zed\Tax\Persistence\Map\SpyTaxRateTableMap;
use Orm\Zed\Tax\Persistence\SpyTaxRate;
use Orm\Zed\Tax\Persistence\SpyTaxRateQuery;
use Spryker\Shared\Library\DateFormatterInterface;
use Spryker\Shared\Url\Url;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class RateTable extends AbstractTable
{
    const TABLE_COL_ACTIONS = 'Actions';
    const URL_PARAM_ID_TAX_RATE = 'id-tax-rate';

    /**
     * @var \Orm\Zed\Tax\Persistence\SpyTaxRateQuery
     */
    protected $taxRateQuery;

    /**
     * @var \Spryker\Shared\Library\DateFormatterInterface
     */
    protected $dateFormatter;

    /**
     * @param \Orm\Zed\Tax\Persistence\SpyTaxRateQuery $taxRateQuery
     * @param \Spryker\Shared\Library\DateFormatterInterface $dateFormatter
     */
    public function __construct(SpyTaxRateQuery $taxRateQuery, DateFormatterInterface $dateFormatter)
    {
        $this->taxRateQuery = $taxRateQuery;
        $this->dateFormatter = $dateFormatter;
    }


    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return mixed
     */
    protected function configure(TableConfiguration $config)
    {
        $url = Url::generate('listTable')->build();

        $config->setUrl($url);
        $config->setHeader([
            SpyTaxRateTableMap::COL_ID_TAX_RATE => 'ID',
            SpyTaxRateTableMap::COL_NAME => 'Name',
            SpyTaxRateTableMap::COL_CREATED_AT => 'Created At',
            SpyCountryTableMap::COL_NAME => 'Country',
            SpyTaxRateTableMap::COL_RATE => 'Percentage',
            self::TABLE_COL_ACTIONS => 'Actions'
        ]);

        $config->setSearchable([
            SpyTaxRateTableMap::COL_NAME,
            SpyCountryTableMap::COL_NAME,
        ]);

        $config->setSortable([
            SpyTaxRateTableMap::COL_ID_TAX_RATE,
            SpyCountryTableMap::COL_NAME,
            SpyTaxRateTableMap::COL_NAME,
            SpyTaxRateTableMap::COL_RATE,
            SpyTaxRateTableMap::COL_CREATED_AT,
        ]);

        $config->setDefaultSortColumnIndex(0);
        $config->setDefaultSortDirection(TableConfiguration::SORT_DESC);
        $config->addRawColumn(self::TABLE_COL_ACTIONS);

        return $config;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return mixed
     */
    protected function prepareData(TableConfiguration $config)
    {
        $result = [];
        $query = $this->taxRateQuery
            ->innerJoinCountry();

        $queryResult = $this->runQuery($query, $config, true);

        /** @var \Orm\Zed\Tax\Persistence\SpyTaxRate $taxRateEntity */
        foreach ($queryResult as $taxRateEntity) {
            $result[] = [
                SpyTaxRateTableMap::COL_ID_TAX_RATE => $taxRateEntity->getIdTaxRate(),
                SpyTaxRateTableMap::COL_CREATED_AT => $this->dateFormatter->dateTime($taxRateEntity->getCreatedAt()),
                SpyTaxRateTableMap::COL_NAME => $taxRateEntity->getName(),
                SpyCountryTableMap::COL_NAME => $this->getCountryName($taxRateEntity),
                SpyTaxRateTableMap::COL_RATE => $taxRateEntity->getRate(),
                self::TABLE_COL_ACTIONS => $this->getActionButtons($taxRateEntity),
            ];
        }
        return $result;
    }

    /**
     * @param \Orm\Zed\Tax\Persistence\SpyTaxRate $taxRateEntity
     *
     * @return string
     */
    protected function getActionButtons(SpyTaxRate $taxRateEntity)
    {
        $buttons = [];
        $buttons[] = $this->createViewButton($taxRateEntity);
        $buttons[] = $this->createEditButton($taxRateEntity);
        $buttons[] = $this->createDeleteButton($taxRateEntity);

        return implode(' ', $buttons);
    }

    /**
     * @param \Orm\Zed\Tax\Persistence\SpyTaxRate $taxRateEntity
     *
     * @return string
     */
    protected function createEditButton(SpyTaxRate $taxRateEntity)
    {
        $editTaxRateUrl = Url::generate(
            '/tax/rate/edit',
            [
                self::URL_PARAM_ID_TAX_RATE => $taxRateEntity->getIdTaxRate()
            ]
        );
        return $this->generateEditButton($editTaxRateUrl, 'Edit');
    }

    /**
     * @param \Orm\Zed\Tax\Persistence\SpyTaxRate $taxRateEntity
     *
     * @return string
     */
    protected function createViewButton(SpyTaxRate $taxRateEntity)
    {
        $viewTaxRateUrl = Url::generate(
            '/tax/rate/view',
            [
                self::URL_PARAM_ID_TAX_RATE => $taxRateEntity->getIdTaxRate()
            ]
        );
        return $this->generateViewButton($viewTaxRateUrl, 'view');
    }

    /**
     * @param \Orm\Zed\Tax\Persistence\SpyTaxRate $taxRateEntity
     *
     * @return string
     */
    protected function createDeleteButton(SpyTaxRate $taxRateEntity)
    {
        $deleteTaxRateUrl = Url::generate(
            '/tax/rate/delete',
            [
                self::URL_PARAM_ID_TAX_RATE => $taxRateEntity->getIdTaxRate()
            ]
        );

        return $this->generateRemoveButton($deleteTaxRateUrl, 'delete');
    }

    /**
     * @param SpyTaxRate $taxRateEntity
     *
     * @return string
     */
    protected function getCountryName(SpyTaxRate $taxRateEntity)
    {
        $countryName = '';
        if ($taxRateEntity->getCountry()) {
            $countryName = $taxRateEntity->getCountry()->getName();
        }

        return $countryName;
    }
}
