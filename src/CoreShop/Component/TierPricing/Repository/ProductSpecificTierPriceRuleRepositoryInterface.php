<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2019 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Component\TierPricing\Repository;

use CoreShop\Component\Rule\Repository\RuleRepositoryInterface;
use CoreShop\Component\TierPricing\Model\ProductSpecificTierPriceRuleInterface;
use CoreShop\Component\TierPricing\Model\TierPriceAwareInterface;

interface ProductSpecificTierPriceRuleRepositoryInterface extends RuleRepositoryInterface
{
    /**
     * @param TierPriceAwareInterface $product
     *
     * @return ProductSpecificTierPriceRuleInterface[]
     */
    public function findForProduct(TierPriceAwareInterface $product);
}
