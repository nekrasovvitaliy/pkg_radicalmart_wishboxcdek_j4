<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Console\WishboxRadicalMartCdekOrderRegistrator\Console\Command;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Administrator\Model\OrdersModel;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\Orders\AfterRegisterAllEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\Orders\AfterRegisterEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\Orders\BeforeRegisterAllEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class RegisterOrdersCommand extends AbstractCommand
{
	use MVCFactoryAwareTrait;

	/**
	 * Имя команды по умолчанию
	 *
	 * @var   string
	 *
	 * @since 1.0.0
	 */
	protected static $defaultName = 'wishboxradicalmartcdek:register-orders';

	/**
	 * @var InputInterface
	 *
	 * @since 1.0.0
	 */
	private InputInterface $cliInput;

	/**
	 * SymfonyStyle Object
	 *
	 * @var SymfonyStyle
	 *
	 * @since 1.0.0
	 */
	private SymfonyStyle $ioStyle;

	/**
	 * Конфигурирует вход-выход
	 *
	 * @param   InputInterface   $input   Консольный ввод
	 * @param   OutputInterface  $output  Консольный вывод
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	private function configureIO(InputInterface $input, OutputInterface $output): void
	{
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
	}

	/**
	 * Инициализация команды.
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	protected function configure(): void
	{
		$help = "<info>%command.name%</info> Registers orders
                        \nUsage: <info>php %command.full_name%</info>";

		$this->setDescription('Register orders');
		$this->setHelp($help);
	}

	/**
	 * @param   InputInterface   $input   Input
	 * @param   OutputInterface  $output  Output
	 *
	 * @return  integer
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		/** @var ConsoleApplication $app */
		$app = $this->getApplication();

		$dispatcher = $app->getDispatcher();

		$dispatcher->addListener(
			'onWishboxRadicalMartCdekOrderBeforeRegisterAll',
			[$this, 'onWishboxRadicalMartCdekOrderBeforeRegisterAll']
		);

		$dispatcher->addListener(
			'onWishboxRadicalMartCdekOrderAfterRegister',
			[$this, 'onWishboxRadicalMartCdekOrderAfterRegister']
		);

		$dispatcher->addListener(
			'onWishboxRadicalMartCdekOrderAfterRegisterAll',
			[$this, 'onWishboxRadicalMartCdekOrderAfterRegisterAll']
		);

		$this->configureIO($input, $output);

		if (!ini_set('memory_limit', '256000000'))
		{
			throw new Exception('ini_set("memory_limit", "512MB") return false', 500);
		}

		try
		{
			/** @var OrdersModel $ordersModel */
			$ordersModel = $app->bootPlugin('wishboxcdekorderregistrator', 'radicalmart')
				->getMVCFactory()
				->createModel('Orders', 'Administrator');

			$ordersModel->registerAll();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(
				Text::_(
					'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATOR_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
				) . ': ' . $e->getMessage(),
				CMSApplicationInterface::MSG_ERROR
			);

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

	/**
	 * @param   BeforeRegisterAllEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onWishboxRadicalMartCdekOrderBeforeRegisterAll(BeforeRegisterAllEvent $event): void
	{
		$orderIds = $event->getOrderIds();

		$this->ioStyle->progressStart(count($orderIds));
	}

	/**
	 * @param   AfterRegisterEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onWishboxRadicalMartCdekOrderAfterRegister(AfterRegisterEvent $event): void
	{
		$this->ioStyle->progressAdvance();
	}

	/**
	 * @param   AfterRegisterAllEvent  $event  Event
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onWishboxRadicalMartCdekOrderAfterRegisterAll(AfterRegisterAllEvent $event): void
	{
		$this->ioStyle->progressFinish();
	}
}
