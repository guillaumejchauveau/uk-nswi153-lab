/*
 * Your code ...
 */


/**
 * Handles the renaming operation. Gets Map with old names as keys and new names as values.
 * The oldNames arguments holds a Set with all original names (so collisions can be detected).
 * If the renaming cannot be performed (due to collisions) an exception must be thrown and
 * no individual renames must be issued.
 * @param {Map} renames A map (oldName -> newName) of requested renames to be performed
 * @param {Set} oldNames Entire set of old names (even those which are not being renamed)
 * @param {function} renameFile Function (oldName, newName) => Promise that invokes AJAX fetch operation.
 * 								Renames one file only and yields promise that resolves once the renaming is done.
 * @return {Promise} which must resolve once all renames have concluded or reject if any subseqent
 */
function handleRenames(renames, oldNames, renameFile)
{

}


// In nodejs, this is the way how export is performed.
// In browser, module has to be a global varibale object.
module.exports = { handleRenames };
