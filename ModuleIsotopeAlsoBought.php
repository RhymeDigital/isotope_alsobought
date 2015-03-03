<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Contao;

use Isotope\Model\Product;
use Isotope\Module\ProductList as Iso_ProductList;

/**
 * Class ModuleIsotopeAlsoBought
 * List products also bought to the current product reader.
 */
class ModuleIsotopeAlsoBought extends Iso_ProductList
{

	/**
	 * Do not cache related products cause the list is different depending on URL parameters
	 * @var boolean
	 */
	protected $blnCacheProducts = false;
	
	/**
	 * Template
	 * @var boolean
	 */
	protected $strTemplate = 'mod_iso_alsobought';	

	/**
	 * Generate the module
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ALSO BOUGHT ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

        // Return if no product has been specified
        if (\Haste\Input\Input::getAutoItem('product') == '') {
            return '';
        }

		return parent::generate();
	}


	/**
	 * Find all products we need to list.
	 * @return array
	 */
	protected function findProducts($arrCacheIds = null)
	{
		$strAlias = \Haste\Input\Input::getAutoItem('product');
		$arrIds = array(0);
		
		$objProduct = \Database::getInstance()->prepare("SELECT id FROM tl_iso_product WHERE alias=?")->execute($strAlias);
		$objOrders = \Database::getInstance()->prepare("SELECT pid FROM tl_iso_product_collection_item WHERE product_id=?")->execute($objProduct->id);
		
		$arrItems = array();
		while($objOrders->next()){
			$objAlsoBoughtItems = \Database::getInstance()->prepare("SELECT product_id FROM tl_iso_product_collection_item WHERE pid=? AND product_id != ?")->execute($objOrders->pid, $objProduct->id);
			while($objAlsoBoughtItems->next()){
				if(array_key_exists($objAlsoBoughtItems->product_id, $arrItems)){
					$arrItems[$objAlsoBoughtItems->product_id] = $arrItems[$objAlsoBoughtItems->product_id]+1;
				}
				else{
					$arrItems[$objAlsoBoughtItems->product_id] = 1;
				}
			}
		}
		
		if(sizeof($arrItems) > 0 ){
			arsort($arrItems);
			$i = 0;
			foreach($arrItems as $key=>$value){
				$i++;
				if(intval($this->numberOfItems) > 0 && $i > intval($this->numberOfItems)){
					break;
				}
				$arrIds[] = $key; 
			}
		}
		else{
			$arrIds = $this->findRelatedRroducts();
		}

        $arrColumns[] = Product::getTable().".id IN (" . implode(',', $arrIds) . ")";
        
        $objProducts = Product::findAvailableBy(
            $arrColumns,
            $arrValues,
            array(
                 'order'   => 'c.sorting',
                 'filters' => $arrFilters,
                 'sorting' => $arrSorting,
            )
        );

        return (null === $objProducts) ? array() : $objProducts->getModels();
	}
	
		/**
	 * Find all products we need to list.
	 * @return array
	 */
	protected function findRelatedRroducts()
	{
		$strAlias = \Haste\Input\Input::getAutoItem('product');
		$arrIds = array(0);
		
		$objCategories = \Database::getInstance()->prepare("SELECT * FROM tl_iso_related_product WHERE pid IN (SELECT id FROM tl_iso_product WHERE " . (is_numeric($strAlias) ? 'id' : 'alias') . "=?" . ($this->iso_list_where != '' ? ' AND '.$this->iso_list_where : '') . ") AND category IN (" . implode(',', deserialize($this->iso_related_categories)) . ") ORDER BY id=" . implode(' DESC, id=', deserialize($this->iso_related_categories)) . " DESC")->execute($strAlias);
		
		if ($objCategories->numRows)
		{
			$i = 0;
			
			while ($objCategories->next() && ($i < intval($this->numberOfItems)))
			{
				$ids = deserialize($objCategories->products, true);
	
				if (!empty($ids))
				{
					$arrIds = array_unique(array_merge($arrIds, $ids));
				}
				
				if ($this->numberOfItems)
				{
					$i++;
				}
			}
		}

		return $arrIds;
	}
	
}

