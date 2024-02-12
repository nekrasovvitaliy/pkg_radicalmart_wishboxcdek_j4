<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  Radicalmart.Wishboxcdek
 * @copyright   2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Component\RadicalMart\Administrator\Controller;

use AntistressStore\CdekSDK2\Exceptions\CdekV2RequestException;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Component\Wishboxcdek\Site\Service\Registrator;
use Joomla\Plugin\RadicalMartShipping\Wishboxcdek\Service\RegistratorDelegate;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class WishboxcdekorderController extends FormController
{
	/**
	 * @return void
	 *
	 * @throws CdekV2RequestException
	 * @since 1.0.0
	 */
	public function register(): void
	{
		$app = Factory::getApplication();

		$cid = $this->input->getVar('cid', [], 'array');

		$orderModel = $app->bootComponent('com_radicalmart')
			->getMVCFactory()
			->createModel('order', 'Administrator', ['ignore_request' => true]);

		foreach ($cid as $id)
		{
			$order = $orderModel->getItem($id);
			$registratorDelegate = new RegistratorDelegate($order);
			$registrator = new Registrator($registratorDelegate);
			$registrator->register();
		}

		die;
	}
}
