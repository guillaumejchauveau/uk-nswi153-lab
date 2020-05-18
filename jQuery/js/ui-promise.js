/**
 * Promise wrappers for win
 * Old-style lib
 */

/**
 * Shows simple window confirmation dialog, but wraps the result in a promise.
 * @param {string} msg Text to be cofirmed
 * @return {Promise}
 */
function confirmDialog(msg) {
	return new Promise(function (resolve, reject) {
		return window.confirm(msg) ? resolve(true) : reject(false);
	});
}

/**
 * Shows window prompt dialog, but wraps the result in a promise.
 * @param {string} msg Text being displayed in the prompt
 * @param {string} initValue Initial value shown in the prompt
 * @return {Promise}
 */
function promptDialog(msg, initValue = '') {
	return new Promise(function (resolve, reject) {
		const res = window.prompt(msg, initValue);
		return res !== null ? resolve(res) : reject(null);
	});
}
