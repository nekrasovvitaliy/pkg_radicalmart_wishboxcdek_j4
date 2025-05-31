<?php
/**
 * @copyright   (c) 2013-2025 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Joomla\Plugin\RadicalMart\WishboxCdekOrderStatusUpdater\Extension;

use Exception;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
final class WishboxCdekOrderStatusUpdater extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Enable on RadicalMart
	 *
	 * @var  boolean
	 *
	 * @since  1.0.0
	 */
	public bool $radicalmart = true;

	/**
	 * Enable on RadicalMartExpress
	 *
	 * @var  boolean
	 *
	 * @since  1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public bool $radicalmart_express = true; // phpcs:ignore

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeRender' => 'onBeforeRender',
		];
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
		$app = $this->getApplication();

		if (!$app->isClient('administrator'))
		{
			return;
		}

		/** @var Document $document */
		$document = $app->getDocument();

		$option = $app->getInput()->getCmd('option', '');
		$view = $app->getInput()->getCmd('view', '');

		if ($option == 'com_radicalmart' && $view == 'orders')
		{
			/** @var Toolbar $toolbar */
			$toolbar = $document->getToolbar();

			$html = LayoutHelper::render(
				'plugins.radicalmart.wishboxcdekorderstatusupdater.toolbar.update-statuses',
				[
					'redirectUrl' => 'index.php?option=com_radicalmart&view=orders'
				]
			);

			$factory = Factory::getContainer()->get(ToolbarFactoryInterface::class);

			$button = $factory->createButton($toolbar, 'Custom')
				->name('wishboxcdek-update-statuses')
				->text('')
				->task('')
				->html($html);

			$tempItems = $toolbar->getItems();
			$items = [];

			foreach ($tempItems as $k => $tempItem)
			{
				if ($k == 2)
				{
					$items[] = $button;
				}

				$items[] = $tempItem;
			}

			$toolbar->setItems($items);
		}
	}
}
