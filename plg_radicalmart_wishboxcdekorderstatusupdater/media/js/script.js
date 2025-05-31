/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
/*!**********************************************************************!*\
  !*** ./plg_radicalmart_wishboxcdekorderstatusupdater/es6/script.es6 ***!
  \**********************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });


class RadicalMartWishboxCdekOrderStatusUpdater {
  constructor() {}
  initialization() {
    document.getElementById('toolbar-wishboxcdek-update-statuses').querySelector('button').addEventListener('click', () => {
      const adminForm = document.getElementById('adminForm');
      const targetForm = document.getElementById('wishboxradicalmartcdek-orders');

      // Получаем выбранные checkbox'ы
      const checkboxes = adminForm.querySelectorAll('input[name="cid[]"]:checked');
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
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (RadicalMartWishboxCdekOrderStatusUpdater);
window.RadicalMartWishboxCdekOrderStatusUpdaterClass = null;
window.RadicalMartWishboxCdekOrderStatusUpdater = () => {
  if (window.RadicalMartWishboxCdekOrderStatusUpdaterClass === null) {
    window.RadicalMartWishboxCdekOrderStatusUpdaterClass = new RadicalMartWishboxCdekOrderStatusUpdater();
  }
  return window.RadicalMartWishboxCdekOrderStatusUpdaterClass;
};
document.addEventListener('DOMContentLoaded', () => {
  window.RadicalMartWishboxCdekOrderStatusUpdater().initialization();
});
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoianMvc2NyaXB0LmpzIiwibWFwcGluZ3MiOiI7O1VBQUE7VUFDQTs7Ozs7V0NEQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLHlDQUF5Qyx3Q0FBd0M7V0FDakY7V0FDQTtXQUNBOzs7OztXQ1BBOzs7OztXQ0FBO1dBQ0E7V0FDQTtXQUNBLHVEQUF1RCxpQkFBaUI7V0FDeEU7V0FDQSxnREFBZ0QsYUFBYTtXQUM3RDs7Ozs7Ozs7Ozs7O0FDTmE7O0FBRWIsTUFBTUEsd0NBQXdDLENBQUM7RUFDOUNDLFdBQVdBLENBQUEsRUFBRyxDQUNkO0VBRUFDLGNBQWNBLENBQUEsRUFBRztJQUNmQyxRQUFRLENBQUNDLGNBQWMsQ0FBQyxzQ0FBc0MsQ0FBQyxDQUM3REMsYUFBYSxDQUFDLFFBQVEsQ0FBQyxDQUN2QkMsZ0JBQWdCLENBQ2hCLE9BQU8sRUFDUCxNQUFNO01BQ0wsTUFBTUMsU0FBUyxHQUFHSixRQUFRLENBQUNDLGNBQWMsQ0FBQyxXQUFXLENBQUM7TUFDdEQsTUFBTUksVUFBVSxHQUFHTCxRQUFRLENBQUNDLGNBQWMsQ0FBQywrQkFBK0IsQ0FBQzs7TUFFM0U7TUFDQSxNQUFNSyxVQUFVLEdBQUVGLFNBQVMsQ0FBQ0csZ0JBQWdCLENBQUMsNkJBQTZCLENBQUM7TUFDM0UsTUFBTUMsR0FBRyxHQUFHQyxLQUFLLENBQUNDLElBQUksQ0FBQ0osVUFBVSxDQUFDLENBQUNLLEdBQUcsQ0FBQ0MsRUFBRSxJQUFJQSxFQUFFLENBQUNDLEtBQUssQ0FBQzs7TUFFdEQ7TUFDQVIsVUFBVSxDQUFDRSxnQkFBZ0IsQ0FBQyxxQkFBcUIsQ0FBQyxDQUFDTyxPQUFPLENBQUNDLEVBQUUsSUFBSUEsRUFBRSxDQUFDQyxNQUFNLENBQUMsQ0FBQyxDQUFDOztNQUU3RTtNQUNBUixHQUFHLENBQUNNLE9BQU8sQ0FBQ0csRUFBRSxJQUFJO1FBQ2pCLE1BQU1DLEtBQUssR0FBR2xCLFFBQVEsQ0FBQ21CLGFBQWEsQ0FBQyxPQUFPLENBQUM7UUFDN0NELEtBQUssQ0FBQ0UsSUFBSSxHQUFHLFFBQVE7UUFDckJGLEtBQUssQ0FBQ0csSUFBSSxHQUFHLE9BQU87UUFDcEJILEtBQUssQ0FBQ0wsS0FBSyxHQUFHSSxFQUFFO1FBQ2hCWixVQUFVLENBQUNpQixXQUFXLENBQUNKLEtBQUssQ0FBQztNQUM5QixDQUFDLENBQUM7TUFFRmIsVUFBVSxDQUFDa0IsTUFBTSxDQUFDLENBQUM7SUFDcEIsQ0FBQyxDQUFDO0VBQ047QUFDRDtBQUVBLGlFQUFlMUIsd0NBQXdDLEVBQUM7QUFFeEQyQixNQUFNLENBQUNDLDZDQUE2QyxHQUFHLElBQUk7QUFDM0RELE1BQU0sQ0FBQzNCLHdDQUF3QyxHQUFHLE1BQU07RUFDdkQsSUFBSTJCLE1BQU0sQ0FBQ0MsNkNBQTZDLEtBQUssSUFBSSxFQUFFO0lBQ2xFRCxNQUFNLENBQUNDLDZDQUE2QyxHQUFHLElBQUk1Qix3Q0FBd0MsQ0FBQyxDQUFDO0VBQ3RHO0VBQ0EsT0FBTzJCLE1BQU0sQ0FBQ0MsNkNBQTZDO0FBQzVELENBQUM7QUFFRHpCLFFBQVEsQ0FBQ0csZ0JBQWdCLENBQUMsa0JBQWtCLEVBQUUsTUFBTTtFQUNuRHFCLE1BQU0sQ0FBQzNCLHdDQUF3QyxDQUFDLENBQUMsQ0FBQ0UsY0FBYyxDQUFDLENBQUM7QUFDbkUsQ0FBQyxDQUFDLEMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9wbGdfcmFkaWNhbG1hcnRfd2lzaGJveGNkZWtvcmRlcnN0YXR1c3VwZGF0ZXIvd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vcGxnX3JhZGljYWxtYXJ0X3dpc2hib3hjZGVrb3JkZXJzdGF0dXN1cGRhdGVyL3dlYnBhY2svcnVudGltZS9kZWZpbmUgcHJvcGVydHkgZ2V0dGVycyIsIndlYnBhY2s6Ly9wbGdfcmFkaWNhbG1hcnRfd2lzaGJveGNkZWtvcmRlcnN0YXR1c3VwZGF0ZXIvd2VicGFjay9ydW50aW1lL2hhc093blByb3BlcnR5IHNob3J0aGFuZCIsIndlYnBhY2s6Ly9wbGdfcmFkaWNhbG1hcnRfd2lzaGJveGNkZWtvcmRlcnN0YXR1c3VwZGF0ZXIvd2VicGFjay9ydW50aW1lL21ha2UgbmFtZXNwYWNlIG9iamVjdCIsIndlYnBhY2s6Ly9wbGdfcmFkaWNhbG1hcnRfd2lzaGJveGNkZWtvcmRlcnN0YXR1c3VwZGF0ZXIvLi9wbGdfcmFkaWNhbG1hcnRfd2lzaGJveGNkZWtvcmRlcnN0YXR1c3VwZGF0ZXIvZXM2L3NjcmlwdC5lczYiXSwic291cmNlc0NvbnRlbnQiOlsiLy8gVGhlIHJlcXVpcmUgc2NvcGVcbnZhciBfX3dlYnBhY2tfcmVxdWlyZV9fID0ge307XG5cbiIsIi8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb25zIGZvciBoYXJtb255IGV4cG9ydHNcbl9fd2VicGFja19yZXF1aXJlX18uZCA9IChleHBvcnRzLCBkZWZpbml0aW9uKSA9PiB7XG5cdGZvcih2YXIga2V5IGluIGRlZmluaXRpb24pIHtcblx0XHRpZihfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZGVmaW5pdGlvbiwga2V5KSAmJiAhX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIGtleSkpIHtcblx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBrZXksIHsgZW51bWVyYWJsZTogdHJ1ZSwgZ2V0OiBkZWZpbml0aW9uW2tleV0gfSk7XG5cdFx0fVxuXHR9XG59OyIsIl9fd2VicGFja19yZXF1aXJlX18ubyA9IChvYmosIHByb3ApID0+IChPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqLCBwcm9wKSkiLCIvLyBkZWZpbmUgX19lc01vZHVsZSBvbiBleHBvcnRzXG5fX3dlYnBhY2tfcmVxdWlyZV9fLnIgPSAoZXhwb3J0cykgPT4ge1xuXHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcblx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcblx0fVxuXHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xufTsiLCJcInVzZSBzdHJpY3RcIjtcblxuY2xhc3MgUmFkaWNhbE1hcnRXaXNoYm94Q2Rla09yZGVyU3RhdHVzVXBkYXRlciB7XG5cdGNvbnN0cnVjdG9yKCkge1xuXHR9XG5cblx0aW5pdGlhbGl6YXRpb24oKSB7XG5cdFx0XHRkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgndG9vbGJhci13aXNoYm94Y2Rlay11cGRhdGUtc3RhdHVzZXMxJylcblx0XHRcdFx0LnF1ZXJ5U2VsZWN0b3IoJ2J1dHRvbicpXG5cdFx0XHRcdC5hZGRFdmVudExpc3RlbmVyKFxuXHRcdFx0XHRcdCdjbGljaycsXG5cdFx0XHRcdFx0KCkgPT4ge1xuXHRcdFx0XHRcdFx0Y29uc3QgYWRtaW5Gb3JtID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2FkbWluRm9ybScpO1xuXHRcdFx0XHRcdFx0Y29uc3QgdGFyZ2V0Rm9ybSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd3aXNoYm94cmFkaWNhbG1hcnRjZGVrLW9yZGVycycpO1xuXG5cdFx0XHRcdFx0XHQvLyDQn9C+0LvRg9GH0LDQtdC8INCy0YvQsdGA0LDQvdC90YvQtSBjaGVja2JveCfRi1xuXHRcdFx0XHRcdFx0Y29uc3QgY2hlY2tib3hlcz0gYWRtaW5Gb3JtLnF1ZXJ5U2VsZWN0b3JBbGwoJ2lucHV0W25hbWU9XCJjaWRbXVwiXTpjaGVja2VkJyk7XG5cdFx0XHRcdFx0XHRjb25zdCBjaWQgPSBBcnJheS5mcm9tKGNoZWNrYm94ZXMpLm1hcChjYiA9PiBjYi52YWx1ZSk7XG5cblx0XHRcdFx0XHRcdC8vINCe0YfQuNGJ0LDQtdC8INC/0YDQtdC00YvQtNGD0YnQuNC1IGNpZCDQsiDRhtC10LvQtdCy0L7QuSDRhNC+0YDQvNC1XG5cdFx0XHRcdFx0XHR0YXJnZXRGb3JtLnF1ZXJ5U2VsZWN0b3JBbGwoJ2lucHV0W25hbWU9XCJjaWRbXVwiXScpLmZvckVhY2goZWwgPT4gZWwucmVtb3ZlKCkpO1xuXG5cdFx0XHRcdFx0XHQvLyDQlNC+0LHQsNCy0LvRj9C10Lwg0L3QvtCy0YvQtSBjaWQg0LIg0YbQtdC70LXQstGD0Y4g0YTQvtGA0LzRg1xuXHRcdFx0XHRcdFx0Y2lkLmZvckVhY2goaWQgPT4ge1xuXHRcdFx0XHRcdFx0XHRjb25zdCBpbnB1dCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2lucHV0Jyk7XG5cdFx0XHRcdFx0XHRcdGlucHV0LnR5cGUgPSAnaGlkZGVuJztcblx0XHRcdFx0XHRcdFx0aW5wdXQubmFtZSA9ICdjaWRbXSc7XG5cdFx0XHRcdFx0XHRcdGlucHV0LnZhbHVlID0gaWQ7XG5cdFx0XHRcdFx0XHRcdHRhcmdldEZvcm0uYXBwZW5kQ2hpbGQoaW5wdXQpO1xuXHRcdFx0XHRcdFx0fSk7XG5cblx0XHRcdFx0XHRcdHRhcmdldEZvcm0uc3VibWl0KCk7XG5cdFx0XHRcdFx0fSk7XG5cdH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgUmFkaWNhbE1hcnRXaXNoYm94Q2Rla09yZGVyU3RhdHVzVXBkYXRlcjtcblxud2luZG93LlJhZGljYWxNYXJ0V2lzaGJveENkZWtPcmRlclN0YXR1c1VwZGF0ZXJDbGFzcyA9IG51bGw7XG53aW5kb3cuUmFkaWNhbE1hcnRXaXNoYm94Q2Rla09yZGVyU3RhdHVzVXBkYXRlciA9ICgpID0+IHtcblx0aWYgKHdpbmRvdy5SYWRpY2FsTWFydFdpc2hib3hDZGVrT3JkZXJTdGF0dXNVcGRhdGVyQ2xhc3MgPT09IG51bGwpIHtcblx0XHR3aW5kb3cuUmFkaWNhbE1hcnRXaXNoYm94Q2Rla09yZGVyU3RhdHVzVXBkYXRlckNsYXNzID0gbmV3IFJhZGljYWxNYXJ0V2lzaGJveENkZWtPcmRlclN0YXR1c1VwZGF0ZXIoKTtcblx0fVxuXHRyZXR1cm4gd2luZG93LlJhZGljYWxNYXJ0V2lzaGJveENkZWtPcmRlclN0YXR1c1VwZGF0ZXJDbGFzcztcbn1cblxuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsICgpID0+IHtcblx0d2luZG93LlJhZGljYWxNYXJ0V2lzaGJveENkZWtPcmRlclN0YXR1c1VwZGF0ZXIoKS5pbml0aWFsaXphdGlvbigpO1xufSk7Il0sIm5hbWVzIjpbIlJhZGljYWxNYXJ0V2lzaGJveENkZWtPcmRlclN0YXR1c1VwZGF0ZXIiLCJjb25zdHJ1Y3RvciIsImluaXRpYWxpemF0aW9uIiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsInF1ZXJ5U2VsZWN0b3IiLCJhZGRFdmVudExpc3RlbmVyIiwiYWRtaW5Gb3JtIiwidGFyZ2V0Rm9ybSIsImNoZWNrYm94ZXMiLCJxdWVyeVNlbGVjdG9yQWxsIiwiY2lkIiwiQXJyYXkiLCJmcm9tIiwibWFwIiwiY2IiLCJ2YWx1ZSIsImZvckVhY2giLCJlbCIsInJlbW92ZSIsImlkIiwiaW5wdXQiLCJjcmVhdGVFbGVtZW50IiwidHlwZSIsIm5hbWUiLCJhcHBlbmRDaGlsZCIsInN1Ym1pdCIsIndpbmRvdyIsIlJhZGljYWxNYXJ0V2lzaGJveENkZWtPcmRlclN0YXR1c1VwZGF0ZXJDbGFzcyJdLCJzb3VyY2VSb290IjoiIn0=