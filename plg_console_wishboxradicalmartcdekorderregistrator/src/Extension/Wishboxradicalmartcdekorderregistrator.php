<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Plugin\Console\WishboxRadicalMartCdekOrderRegistrator\Extension;

use Exception;
use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Console\WishboxRadicalMartCdekOrderRegistrator\Console\Command\RegisterOrdersCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class WishboxRadicalMartCdekOrderRegistrator extends CMSPlugin implements SubscriberInterface
{
	use DatabaseAwareTrait;

	/**
	 * @var SymfonyStyle|null
	 *
	 * @since 1.0.0
	 */
	protected ?SymfonyStyle $symfonyStyle = null;

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

		$app->addCommand($command);
	}
}
