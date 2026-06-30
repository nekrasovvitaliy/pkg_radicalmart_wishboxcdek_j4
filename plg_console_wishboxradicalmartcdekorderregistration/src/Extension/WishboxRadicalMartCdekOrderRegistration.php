<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Console\WishboxRadicalMartCdekOrderRegistration\Extension;

use Exception;
use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Console\WishboxRadicalMartCdekOrderRegistration\Console\Command\RegisterOrdersCommand;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\Order\AfterRegisterAllEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\Order\AfterRegisterEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\Order\BeforeRegisterAllEvent;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\RadicalMartOrderRegistrationServiceAwareInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\RadicalMartOrderRegistrationServiceAwareTrait;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class WishboxRadicalMartCdekOrderRegistration extends CMSPlugin implements
	SubscriberInterface,
	RadicalMartOrderRegistrationServiceAwareInterface
{
	use RadicalMartOrderRegistrationServiceAwareTrait;

	/**
	 * @var SymfonyStyle|null
	 *
	 * @since 1.0.0
	 */
	private ?SymfonyStyle $ioStyle = null;

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
			ApplicationEvents::BEFORE_EXECUTE                => 'registerCommands',
			'onWishboxRadicalMartCdekOrderBeforeRegisterAll' => 'onBeforeRegisterAll',
			'onWishboxRadicalMartCdekOrderAfterRegister'     => 'onAfterRegister',
			'onWishboxRadicalMartCdekOrderAfterRegisterAll'  => 'onAfterRegisterAll',
		];
	}

	/**
	 * @param   Event  $event  Event
	 *
	 * @return void
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function registerCommands(Event $event): void
	{
		/** @var ConsoleApplication $app */
		$app = $this->getApplication();

		$command = new RegisterOrdersCommand;
		$command->setProgressListener($this);
		$command->setRadicalMartOrderRegistrationService($this->getRadicalMartOrderRegistrationService());

		$app->addCommand($command);
	}

	/**
	 * @param   SymfonyStyle  $ioStyle  Symfony style
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setIoStyle(SymfonyStyle $ioStyle): void
	{
		$this->ioStyle = $ioStyle;
	}

	/**
	 * @param   BeforeRegisterAllEvent  $event  Event
	 *
	 * @return void
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onBeforeRegisterAll(BeforeRegisterAllEvent $event): void
	{
		$this->ioStyle?->progressStart(count($event->getOrderIds()));
	}

	/**
	 * @param   AfterRegisterEvent  $event  Event
	 *
	 * @return void
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onAfterRegister(AfterRegisterEvent $event): void
	{
		$this->ioStyle?->progressAdvance();
	}

	/**
	 * @param   AfterRegisterAllEvent  $event  Event
	 *
	 * @return void
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onAfterRegisterAll(AfterRegisterAllEvent $event): void
	{
		$this->ioStyle?->progressFinish();
	}
}
