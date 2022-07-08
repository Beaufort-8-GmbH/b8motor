<?php
declare(strict_types=1);

namespace B8\B8motor\Controller;

use B8\B8motor\Interfaces\AjaxInterface;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 - 2022 Feng Lu <lu@beaufort8.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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

/**
 * this is only the example, howto implement an ajax controller
 */
class EventAjaxController
{
    /**
     * @var AjaxInterface
     */
    protected $ajaxFunctionality;

    protected $queryArguments;

    /**
     * __construct
     *
     * @param   AjaxInterface  $ajaxFunctionality        interface
     * @param   string $queryArguments                   parameter from url query in json format
     *
     */
    public function __construct(AjaxInterface $ajaxFunctionality, string $queryArguments)
    {
        $this->ajaxFunctionality = $ajaxFunctionality;
        $this->queryArguments    = $queryArguments;
    }

    /**
     * get data
     *
     * @return  array  data
     */
    public function getData(): array
    {
        $this->queryArguments = $this->ajaxFunctionality->parseParamsFromUrlRequest($this->queryArguments);

        // var_dump($this->queryArguments);
        // ... here is the function to get data from repository

        return [];
    }
}