<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
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
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\Order\AfterRegisterAllEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\Order\AfterRegisterEvent;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\Order\BeforeRegisterAllEvent;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Console\Wishboxradicalmartcdekorderregistrator\Console\RegisterOrdersCommand;
use Psr\Container\ContainerInterface;
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
	 * @param   DispatcherInterface  $dispatcher  The object to observe
	 * @param   array                $config      An optional associative array of configuration settings.
	 *                                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                                            (this list is not meant to be comprehensive).
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(DispatcherInterface &$dispatcher, array $config = [])
	{
		parent::__construct($dispatcher, $config);

		/** @var ConsoleApplication $app */
		$app = $this->getApplication();

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
			ApplicationEvents::BEFORE_EXECUTE                       => 'registerCommands',
			'onWishboxRadicalMartCdekOrderServiceBeforeRegisterAll' => 'onWishboxRadicalMartCdekOrderServiceBeforeRegisterAll',
			'onWishboxRadicalMartCdekOrderServiceAfterRegister'     => 'onWishboxRadicalMartCdekOrderServiceAfterRegister',
			'onWishboxRadicalMartCdekOrderServiceAfterRegisterAll'  => 'onWishboxRadicalMartCdekOrderServiceAfterRegisterAll',
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
			RegisterOrdersCommand::class,
			function (ContainerInterface $container)
			{
				return new RegisterOrdersCommand;
			},
			true
		);

		Factory::getContainer()->get(WritableLoaderInterface::class)
			->add(
				RegisterOrdersCommand::getDefaultName(),
				RegisterOrdersCommand::class
			);
	}

	/**
	 * @param   BeforeRegisterAllEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekOrderServiceBeforeRegisterAll(BeforeRegisterAllEvent $event): void
	{
		$orderIds = $event->getOrderIds();

		$this->symfonyStyle->progressStart(count($orderIds));
	}

	/**
	 * @param   AfterRegisterEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onWishboxRadicalMartCdekOrderServiceAfterRegister(AfterRegisterEvent $event): void
	{
		$this->symfonyStyle->progressAdvance();
	}

	/**
	 * @param   AfterRegisterAllEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onWishboxRadicalMartCdekOrderServiceAfterRegisterAll(AfterRegisterAllEvent $event): void
	{
		$this->symfonyStyle->progressFinish();
	}
}
