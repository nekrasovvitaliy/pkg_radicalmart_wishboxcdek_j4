<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Console\WishboxRadicalMartCdekOrderRegistration\Console\Command;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Language\Text;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Plugin\Console\WishboxRadicalMartCdekOrderRegistration\Extension\WishboxRadicalMartCdekOrderRegistration;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\RadicalMartOrderRegistrationServiceAwareInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\RadicalMartOrderRegistrationServiceAwareTrait;
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
class RegisterOrdersCommand extends AbstractCommand implements RadicalMartOrderRegistrationServiceAwareInterface
{
	use RadicalMartOrderRegistrationServiceAwareTrait;

	/**
	 * Имя команды по умолчанию
	 *
	 * @var   string
	 *
	 * @since 1.0.0
	 */
	protected static $defaultName = 'wishboxradicalmartcdek:register-orders';

	/**
	 * @var WishboxRadicalMartCdekOrderRegistration|null
	 *
	 * @since 1.0.0
	 */
	private ?WishboxRadicalMartCdekOrderRegistration $progressListener = null;

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

		$this->progressListener?->setIoStyle(new SymfonyStyle($input, $output));

		if (!ini_set('memory_limit', '256000000'))
		{
			throw new Exception('ini_set("memory_limit", "512MB") return false', 500);
		}

		try
		{
			$this->getRadicalMartOrderRegistrationService()->registerAll();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(
				Text::_(
					'PLG_RADICALMART_WISHBOXCDEKORDERREGISTRATION_DELIVERY_SERVICE_REGISTRATION_ERROR_MESSAGE'
				) . ': ' . $e->getMessage(),
				CMSApplicationInterface::MSG_ERROR
			);

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

	/**
	 * @param   WishboxRadicalMartCdekOrderRegistration  $progressListener  Progress listener
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setProgressListener(WishboxRadicalMartCdekOrderRegistration $progressListener): void
	{
		$this->progressListener = $progressListener;
	}
}
