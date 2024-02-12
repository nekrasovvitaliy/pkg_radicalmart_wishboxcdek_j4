<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  Radicalmart.Wishboxcdek
 * @copyright   2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\Radicalmart\Wishboxcdek\Service;

require_once JPATH_SITE . '/vendor/autoload.php';

use AntistressStore\CdekSDK2\Entity\Requests\Contact;
use AntistressStore\CdekSDK2\Entity\Requests\Item;
use AntistressStore\CdekSDK2\Entity\Requests\Package;
use AntistressStore\CdekSDK2\Entity\Requests\Seller;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class Order
{
	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private string $apiAccount;

	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private string $apiSecure;

	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private string $orderType;

	/**
	 * @param   string   $apiAccount  API account
	 * @param   string   $apiSecure   API secure
	 * @param   integer  $orderType   Order type
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $apiAccount, string $apiSecure, int $orderType)
	{
		$this->apiAccount = $apiAccount;
		$this->apiSecure = $apiSecure;
		$this->orderType = $orderType;
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	public function register($order): void
	{
		$apiClient = new CdekClientV2(
			$this->apiAccount,
			$this->apiSecure,
			60.0
		);

		// Создание объекта заказа
		$order = (new \AntistressStore\CdekSDK2\Entity\Requests\Order())
			->setNumber($order->number)             // Номер заказа
			->setType($this->orderType)                             // Тип заказа (ИМ)
			->setComment('Оплата по карте')       // Комментарий
			->setTariffCode(136)                 // Код тарифа
			->setDeliveryRecipientCost(150,55) // Стоимость доставки
			->setPrint('waybill'); // Запрос создать файл накладной вместе с заказом

		// Добавление информации о продавце
		$seller = (new Seller())
			->setName('Antistress.Store')
			->setInn(77777777777)
			->setPhone('88002017708')
			->setOwnershipForm(63);

		$order->setSeller($seller);

		// Добавление информации о получателе
		$recipient = (new Contact())
			->setName($order->contacts->last_name . ' '. $order->contacts->first_name)
			->setEmail($order->contacts->email)
			->setPhones($order->contacts->phone);

		$order->setRecipient($recipient);

		// Адрес отправителя только для тарифов "от двери"

		$order->setShipmentAddress('ул.Люка Скайоукера, д.1')
			->setShipmentCityCode(1204)
			->setRecipientAddress('ул.Джедаев, д.3')
			->setRecipientCityCode(44);

		// Создаем данные посылки. Место

		$packages =
			(new Package())->setNumber('1')
				->setWeight(500)
				->setHeight(10)
				->setWidth(10)
				->setLength(10);

		// Создаем товары

		$items = [];

		foreach ($order->products as $product)
		{

			$items[] = (new Item())
				->setName($product->title)
				->setWareKey($product->code) // Идентификатор/артикул товара/вложения
				->setPayment(1500.00, 0) // Оплата за товар при получении, без НДС (за единицу товара)
				->setCost(($product->price['final']) // Объявленная стоимость товара (за единицу товара)
				->setWeight($product->shipping->weight) // Вес в граммах
				->setAmount($product->shipping->in_stock)); // Количество
		}

		$packages->setItems($items);
		$order->setPackages($packages);

		// Добавление доп.услуг (бесплатных) частичная доставка
		if (count($items) < 1)
		{
			$order->addServices(['PART_DELIV']);
		}

		// Заказ подготовлен отправляем в ранее объявленный клиент
		$response = $apiClient->createOrder($order);
	}
}
