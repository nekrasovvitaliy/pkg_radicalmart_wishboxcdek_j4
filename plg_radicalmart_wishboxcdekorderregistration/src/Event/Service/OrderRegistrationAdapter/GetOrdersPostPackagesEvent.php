<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Event\Service\OrderRegistrationAdapter;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Joomla\Plugin\RadicalMart\WishboxCdekOrderRegistration\Service\OrderRegistration\Adapter\RadicalMartOrderRegistrationAdapter;
use WishboxCdekLibrary\Interface\OrderRegistrationAdapterInterface;
use WishboxCdek\Request\Order\PackageRequestDto;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
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

		$this->resultAcceptableClasses = [PackageRequestDto::class];
	}

	/**
	 * @param   RadicalMartOrderRegistrationAdapter  $value  Subject
	 *
	 * @return RadicalMartOrderRegistrationAdapter
	 *
	 * @since 1.0.0
	 */
	protected function onSetSubject(RadicalMartOrderRegistrationAdapter $value): RadicalMartOrderRegistrationAdapter
	{
		return $value;
	}

	/**
	 * @return OrderRegistrationAdapterInterface
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getOrderRegistrationAdapter(): OrderRegistrationAdapterInterface
	{
		return $this->getArgument('subject');
	}

	/**
	 * @return PackageRequestDto[]
	 *
	 * @since 1.0.0
	 */
	public function getPackageRequests(): array
	{
		return $this->getArgument('result') ?? [];
	}
}
