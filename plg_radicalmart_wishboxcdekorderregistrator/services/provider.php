<?php
/**
 * @version 1.0.0
 * @package Joomla.Plugins
 * @subpackage Radicalmart.Wishboxcdekorderregistrator
 * @author Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMart\Wishboxcdekorderregistrator\Extension\Wishboxcdekorderregistrator;

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
		$container->set(PluginInterface::class,
			function (Container $container) {
				$plugin = PluginHelper::getPlugin('radicalmart', 'wishboxcdekorderregistrator');
				$subject = $container->get(DispatcherInterface::class);

				$plugin = new Wishboxcdekorderregistrator($subject, (array) $plugin);
				$plugin->setApplication(Factory::getApplication());

				return $plugin;
			}
		);
	}
};
