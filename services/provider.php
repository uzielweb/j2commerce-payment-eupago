<?php
/**
 * @package     J2Commerce
 * @subpackage  plg_j2commerce_payment_eupago
 *
 * @copyright   (C)2026 J2Commerce
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use J2Commerce\Plugin\J2Commerce\PaymentEupago\Extension\PaymentEupago;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = PluginHelper::getPlugin('j2commerce', 'payment_eupago');

                $subject = new PaymentEupago(
                    $dispatcher,
                    (array) $plugin,
                    Factory::getApplication()->getLanguage(),
                    Factory::getContainer()->get('DatabaseDriver')
                );

                return $subject;
            }
        );
    }
};
