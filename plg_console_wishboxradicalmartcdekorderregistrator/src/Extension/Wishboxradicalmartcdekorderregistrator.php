<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Console\Wishboxradicalmartcdekorderregistrator\Extension;

use Exception;
use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Console\Loader\WritableLoaderInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Console\Wishboxradicalmartcdekorderregistrator\Console\RegisterOrdersCommand;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class Wishboxradicalmartcdekorderregistrator extends CMSPlugin implements SubscriberInterface
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;

	/**
	 * @var SymfonyStyle|null
	 *
	 * @since 1.0.0
	 */
	protected ?SymfonyStyle $symfonyStyle = null;

	/**
	 * @param   DispatcherInterface  $subject    The object to observe
	 * @param   array                $config     An optional associative array of configuration settings.
	 *                                           Recognized key values include 'name', 'group', 'params', 'language'
	 *                                           (this list is not meant to be comprehensive).
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(&$subject, $config = [])
	{
		parent::__construct($subject, $config);

		/** @var ConsoleApplication $app */
		$app = Factory::getApplication();
		$consoleInput = $app->getConsoleInput();
		$consoleOutput = $app->getConsoleOutput();
		$this->symfonyStyle = new SymfonyStyle($consoleInput, $consoleOutput);
	}

	/**
	 * @return string[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			ApplicationEvents::BEFORE_EXECUTE                           => 'registerCommands',
			'onBeforeWishboxRadicalMarketCdekOrderServiceRegisterAll'   => 'onBeforeWishboxRadicalMarketCdekOrderServiceRegisterAll',
			'onAfterWishboxRadicalMarketCdekOrderServiceRegister'       => 'onAfterWishboxRadicalMarketCdekOrderServiceRegister',
			'onAfterWishboxRadicalMarketCdekOrderServiceRegisterAll'    => 'onAfterWishboxRadicalMarketCdekOrderServiceRegisterAll',
		];
	}

	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function registerCommands(Event  $event): void
	{
		Factory::getContainer()->share(
			'wishboxradicalmartcdek.registerOrders',
			function (ContainerInterface $container) {
				return new RegisterOrdersCommand;
			},
			true
		);

		Factory::getContainer()->get(WritableLoaderInterface::class)
			->add('wishboxradicalmartcdek:register-orders', 'wishboxradicalmartcdek.registerOrders');
	}

	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onBeforeWishboxRadicalMarketCdekOrderServiceRegisterAll(Event $event): void
	{
		/** @var integer[] $orderIds */
		$orderIds = $event->getArgument(0);

		/** @var ConsoleApplication $app */
		$app = Factory::getApplication();

		$this->symfonyStyle->progressStart(count($orderIds));

		$event->setArgument(0, $orderIds);
	}


	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onAfterWishboxRadicalMarketCdekOrderServiceRegister(Event $event): void
	{
		/** @var integer $k */
		$k = $event->getArgument(0);

		/** @var stdClass $order */
		$order = $event->getArgument(1);

		$this->symfonyStyle->progressAdvance();

		$event->setArgument(0, $k);
		$event->setArgument(1, $order);
	}

	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onAfterWishboxRadicalMarketCdekOrderServiceRegisterAll(Event $event): void
	{
		/** @var integer[] $orderIds */
		$orderIds = $event->getArgument(0);

		$this->symfonyStyle->progressFinish();

		$event->setArgument(0, $orderIds);
	}
}
