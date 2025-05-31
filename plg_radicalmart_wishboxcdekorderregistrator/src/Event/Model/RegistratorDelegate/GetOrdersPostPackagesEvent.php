<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Event\Model\RegistratorDelegate;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Joomla\Component\WishboxCdek\Site\Interface\RegistratorDelegateInterface;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistrator\Administrator\Model\RegistratorDelegateModel;
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
	 * @param   RegistratorDelegateModel  $value  Subject
	 *
	 * @return RegistratorDelegateModel
	 *
	 * @since 1.0.0
	 */
	protected function onSetSubject(RegistratorDelegateModel $value): RegistratorDelegateModel
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
