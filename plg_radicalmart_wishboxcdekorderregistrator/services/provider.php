<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\WishboxCdek\Administrator\Extension\Service\Provider\MVCFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Extension\WishboxCdekOrderRegistrator;

defined('_JEXEC') or die;

return new class implements ServiceProviderInterface {

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return void
	 *
	 * @since   1.0.0
	 */
	public function register(Container $container): void
	{
		$container->set(
			PluginInterface::class,
			function (Container $container)
			{
				$container->registerServiceProvider(
					new MVCFactory('Joomla\\Plugin\\RadicalMart\\WishboxCdekOrderRegistrator')
				);

				$dispatcher = $container->get(DispatcherInterface::class);
				$config = (array) PluginHelper::getPlugin('radicalmart', 'wishboxcdekorderregistrator');

				$plugin = new WishboxCdekOrderRegistrator($dispatcher, $config);
				$plugin->setApplication(Factory::getApplication());
				$plugin->setMVCFactory($container->get(MVCFactoryInterface::class));

				return $plugin;
			}
		);
	}
};
