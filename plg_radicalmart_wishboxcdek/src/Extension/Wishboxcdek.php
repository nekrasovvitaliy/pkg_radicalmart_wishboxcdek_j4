<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\Radicalmart\Wishboxcdek\Extension;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\RadicalMart\Site\Model\CheckoutModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Wishbox\Plugin;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
final class Wishboxcdek extends Plugin implements SubscriberInterface
{
	/**
	 * Autoload the language file.
	 *
	 * @var boolean
	 *
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeRender' => 'onBeforeRender',
			'onRadicalMartGetOrderFormData' => 'onGetOrderFormData'
		];
	}

	/**
	 * Constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher  The dispatcher
	 * @param   array                $config      An optional associative array of configuration settings
	 *
	 * @since   1.0.0
	 */
	public function __construct(DispatcherInterface $dispatcher, array $config)
	{
		parent::__construct($dispatcher, $config);
	}

	/**
	 * @param   string  $context  Context
	 * @param   array   $data     Data
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onGetOrderFormData(string $context, array &$data): void
	{
		if ($context == 'com_radicalmart.checkout')
		{
			if (!isset($data['shipping']))
			{
				$db = Factory::getContainer()->get(DatabaseDriver::class);
				$query = $db->getQuery(true)
					->select('id')
					->from($db->qn('#__radicalmart_shipping_methods'))
					->where($db->qn('default') . ' = 1')
					->where($db->qn('language') . ' = "*"');
				$db->setQuery($query);
				$shippingId = (int) $db->loadResult();

				if ($shippingId)
				{
					$data['shipping'] = $this->getCustomerShippingData($shippingId);
				}
			}
		}
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
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function onBeforeRender(Event $event): void
	{
		$app = Factory::getApplication();

		if (!$app->isClient('administrator'))
		{
			return;
		}

		$option = $app->input->getCmd('option', '');
		$view = $app->input->getCmd('view', '');

		if ($option == 'com_radicalmart' && $view == 'orders')
		{
			ToolBarHelper::custom(
				'wishboxcdekorders.register',
				'copy',
				'copy_f2.png',
				Text::_('PLG_RADICALMART_WISHBOXCDEK_REGISTER_IN_CDEK')
			);
		}
	}

	/**
	 * Method to recursive merge customer data.
	 *
	 * @param   array  $source  Source customer data.
	 * @param   array  $new     New customer data.
	 *
	 * @return array Merging customer data.
	 *
	 * @since  1.1.0
	 */
	protected static function mergeCustomerData(array $source = [], array $new = []): array
	{
		$result = $source;

		foreach ($new as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}

			if (is_array($value))
			{
				$value = self::mergeCustomerData((!empty($source[$key])) ? $source[$key] : [], $value);
			}

			if (empty($source[$key]))
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * @param   integer  $shippingId  Shipping id
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getCustomerShippingData(int $shippingId): array
	{
		$app = Factory::getApplication();
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$user = $app->getIdentity();

		$query = $db->getQuery(true)
			->select(['c.id', 'c.contacts', 'c.shipping', 'c.payment', 'c.plugins'])
			->from($db->qn('#__radicalmart_customers', 'c'))
			->where($db->qn('c.id') . ' = :id')
			->bind(':id', $user->id, ParameterType::INTEGER);

		if ($data = $db->setQuery($query, 0, 1)->loadAssoc())
		{
			$shipping = (new Registry($data['shipping']))->toArray();

			if (isset($shipping['shipping_method_' . $shippingId]))
			{
				return $shipping['shipping_method_' . $shippingId];
			}
		}

		return [];
	}
}
