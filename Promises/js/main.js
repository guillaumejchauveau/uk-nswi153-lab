/*
 * Our entire code is wrapped in document.ready handler provided by jQuery (so it is executed after whole page is loaded).
 */
$(function(){
	/*
	 * HTTP Requests API
	 */

	/**
	 * Returns a promise representing async fetch operation.
	 * The promise will yield parsed JSON structure from the load.
	 */
	function getFiles()
	{
		return fetch('ajax.php').then(
			response => response.json()
  		);
	}

	/**
	 * Performs file renaming. Returns promise representing the operation.
	 */
	function renameFile(oldName, newName)
	{
		var data = new FormData();
		data.append('old', oldName);
		data.append('new', newName);

		return fetch('ajax.php', {
			method: 'POST',
			body: data
		}).then(res => res.json());
	}


	/*
	 * UI Functions and Handlers
	 */

	/**
	 * Re-renders the files table. Expects array of file names as argument.
	 */
	function updateTable(files)
	{
		filesTable.empty();
		files.forEach(file => {
			const tr = trTemplate.clone();
			tr.find('a').text(file).attr('href', `files/${file}`);
			tr.find('input').data('old', file);
			filesTable.append(tr);
		});
	}


	// Save button click handler collects data from inputs and pass them to handleRenames().
	$('#saveBtn').click(ev => {
		ev.preventDefault();

		const renames = new Map();
		const oldNames = new Set();
		filesTable.find('input').each(function() {
			const oldName = $(this).data('old');
			const newName = $(this).val().trim();
			oldNames.add(oldName);
			if (newName !== '' && newName !== oldName) {
				renames.set(oldName, newName);
			}
		});

		if (renames.size > 0) {
			// Calling interface implemented by the student (passing renameFile function along)
			try {
				module.exports.handleRenames(renames, oldNames, renameFile).then(
					() => getFiles().then(
						data => updateTable(data.files),
						error => alert(error)
					),
					error => alert(error)
				);
			}
			catch (e) {
				//alert(e);
				throw e
			}
		}
	});


	/**
	 * Initialize the UI for the first time...
	 */

	const filesTable = $('#files tbody');
	const trTemplate = filesTable.find('tr');
	trTemplate.removeClass('template');
	trTemplate.remove();

	// Load the data for the first time ...
	getFiles().then(
		data => updateTable(data.files),
		error => alert(error));
});
