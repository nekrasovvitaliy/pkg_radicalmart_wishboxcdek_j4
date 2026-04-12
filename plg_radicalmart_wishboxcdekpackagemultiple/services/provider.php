<?php
/**
 * @copyright   (с) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekPackageMultiple\Extension\WishboxCdekPackageMultiple;
use WishboxCdekSDK2\Factory\CdekClientV2FactoryInterface;
use WishboxCdekSDK2\Service\Provider\CdekClientV2Factory;

defined('_JEXEC') or die;

return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function register(Container $container): void
	{
		$container->registerServiceProvider(new CdekClientV2Factory);

		$container->set(
			PluginInterface::class,
			function (Container $container)
			{
				$dispatcher = $container->get(DispatcherInterface::class);
				$config = (array) PluginHelper::getPlugin('radicalmart', 'wishboxcdekpackagemultiple');

				$plugin = new WishboxCdekPackageMultiple($dispatcher, $config);
				$plugin->setApplication(Factory::getApplication());
				$plugin->setCdekClientV2Factory($container->get(CdekClientV2FactoryInterface::class));

				return $plugin;
			}
		);
	}
};
