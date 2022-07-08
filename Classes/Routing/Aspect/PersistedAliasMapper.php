<?php
declare(strict_types = 1);

namespace B8\B8motor\Routing\Aspect;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Routing\Aspect\PersistenceDelegate;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 Feng Lu <lu@beaufort8.de>
*  All rights reserved
*
*  This file is part of the "B8 Motor" Extension for TYPO3 CMS.
*  The TYPO3 project is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License as published
*  by the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


class PersistedAliasMapper extends \TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper
{
    use \TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;


    /**
     * @return PersistenceDelegate
     * @deprecated since TYPO3 v9.5.14 will be removed
     */
    protected function getPersistenceDelegate(): PersistenceDelegate
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->tableName);

        $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $queryBuilder->from($this->tableName);

        $resolveModifier = function (QueryBuilder $queryBuilder, array $values) {
            return $queryBuilder->select(...$this->persistenceFieldNames)->where(
                ...$this->createFieldConstraints($queryBuilder, $values)
            );
        };
        $generateModifier = function (QueryBuilder $queryBuilder, array $values) {
            return $queryBuilder->select(...$this->persistenceFieldNames)->where(
                ...$this->createFieldConstraints($queryBuilder, $values)
            );
        };

        return $this->persistenceDelegate = new PersistenceDelegate(
            $queryBuilder,
            $resolveModifier,
            $generateModifier
        );
    }


    /**
     * @return array or null
     * Workes since Typo3 9.5.14
     */
    protected function findByIdentifier(string $value): ?array
    {

        $queryBuilder = $this->createQueryBuilder();

        $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $result = $queryBuilder
            ->select(...$this->persistenceFieldNames)
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($value, \PDO::PARAM_INT)
            ))
            ->execute()
            ->fetch();
        return $result !== false ? $result : null;
    }


    /**
     * @return array or null
     * Workes since Typo3 9.5.14
     */
    protected function findByRouteFieldValue(string $value): ?array
    {
        $queryBuilder = $this->createQueryBuilder();

        $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $result = $queryBuilder
            ->select(...$this->persistenceFieldNames)
            ->where($queryBuilder->expr()->eq(
                $this->routeFieldName,
                $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)
            ))
            ->execute()
            ->fetch();
        return $result !== false ? $result : null;
    }
}