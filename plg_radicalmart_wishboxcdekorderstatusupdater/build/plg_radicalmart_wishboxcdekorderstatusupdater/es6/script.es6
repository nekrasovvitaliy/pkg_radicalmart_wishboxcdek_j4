"use strict";

class RadicalMartWishboxCdekOrderStatusUpdater {
	constructor() {
	}

	initialization() {
			document.getElementById('toolbar-wishboxcdek-update-statuses1')
				.querySelector('button')
				.addEventListener(
					'click',
					() => {
						const adminForm = document.getElementById('adminForm');
						const targetForm = document.getElementById('wishboxradicalmartcdek-orders');

						// Получаем выбранные checkbox'ы
						const checkboxes= adminForm.querySelectorAll('input[name="cid[]"]:checked');
						const cid = Array.from(checkboxes).map(cb => cb.value);

						// Очищаем предыдущие cid в целевой форме
						targetForm.querySelectorAll('input[name="cid[]"]').forEach(el => el.remove());

						// Добавляем новые cid в целевую форму
						cid.forEach(id => {
							const input = document.createElement('input');
							input.type = 'hidden';
							input.name = 'cid[]';
							input.value = id;
							targetForm.appendChild(input);
						});

						targetForm.submit();
					});
	}
}

export default RadicalMartWishboxCdekOrderStatusUpdater;

window.RadicalMartWishboxCdekOrderStatusUpdaterClass = null;
window.RadicalMartWishboxCdekOrderStatusUpdater = () => {
	if (window.RadicalMartWishboxCdekOrderStatusUpdaterClass === null) {
		window.RadicalMartWishboxCdekOrderStatusUpdaterClass = new RadicalMartWishboxCdekOrderStatusUpdater();
	}
	return window.RadicalMartWishboxCdekOrderStatusUpdaterClass;
}

document.addEventListener('DOMContentLoaded', () => {
	window.RadicalMartWishboxCdekOrderStatusUpdater().initialization();
});