<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Console\Wishboxradicalmartcdekorderregistrator\Console;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\OrderService;
use Joomla\Console\Command\AbstractCommand;
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
	 * @since 1.0.0
	 */
	public function __construct()
	{
		parent::__construct();
	}

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
		$help = "<info>%command.name%</info> Updates offices
                        \nUsage: <info>php %command.full_name%</info>";

		$this->setDescription('Called by cron to register orders.');
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
		$app = Factory::getApplication();
		$this->configureIO($input, $output);

		if (!ini_set('memory_limit', '256000000'))
		{
			throw new Exception('ini_set("memory_limit", "512MB") return false', 500);
		}

		try
		{
			$orderService = new OrderService;
			$orderService->registerAll();
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
}
