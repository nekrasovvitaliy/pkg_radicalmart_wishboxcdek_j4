<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */
namespace Joomla\Component\Wishboxradicalmartcdek\Administrator\Event\Service\RegistratorDelegate;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Joomla\Component\Wishboxcdek\Site\Interface\RegistratorDelegateInterface;
use Joomla\Component\Wishboxradicalmartcdek\Administrator\Service\RegistratorDelegate;
use WishboxCdekSDK2\Model\Request\Orders\OrdersPost\PackageRequest;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class GetOrdersPostPackagesEvent extends AbstractEvent implements ResultAwareInterface
{
	use ResultAware;
	use ResultTypeObjectAware;

	/**
	 * @param   string  $eventName  Event name
	 * @param   array   $arguments  Arguments
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $eventName, array $arguments)
	{
		parent::__construct($eventName, $arguments);

		/** @noinspection PhpDeprecationInspection */
		$this->preventSetArgumentResult = true;

		$this->resultAcceptableClasses = [PackageRequest::class];
	}

	/**
	 * @param   RegistratorDelegate  $value  Subject
	 *
	 * @return RegistratorDelegate
	 *
	 * @since 1.0.0
	 */
	protected function onSetSubject(RegistratorDelegate $value): RegistratorDelegate
	{
		return $value;
	}

	/**
	 * @return RegistratorDelegateInterface
	 *
	 * @since 1.0.0
	 */
	public function getRegistratorDelegate(): RegistratorDelegateInterface
	{
		return $this->getArgument('subject');
	}

	/**
	 * @return PackageRequest[]
	 *
	 * @since 1.0.0
	 */
	public function getPackageRequests(): array
	{
		return $this->getArgument('result') ?? [];
	}
}
