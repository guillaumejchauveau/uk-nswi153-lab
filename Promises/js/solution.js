/**
 * Chooses a number for a temporary name.
 * @param {Set<string>} oldNames
 * @param {Set<string>} newNames
 * @param {number} name
 * @return {number} The new name
 */
function generateTemporaryName (oldNames, newNames, name) {
  name++
  for (; oldNames.has(name.toString()) || newNames.has(name.toString()); name++) {
  }
  return name
}

/**
 * Handles the renaming operation. Gets Map with old names as keys and new names as values.
 * The oldNames arguments holds a Set with all original names (so collisions can be detected).
 * If the renaming cannot be performed (due to collisions) an exception must be thrown and
 * no individual renames must be issued.
 * @param {Map<string, string>} renames A map (oldName -> newName) of requested renames to be performed
 * @param {Set<string>} oldNames Entire set of old names (even those which are not being renamed)
 * @param {function} renameFile Function (oldName, newName) => Promise that invokes AJAX fetch operation.
 *                Renames one file only and yields promise that resolves once the renaming is done.
 * @return {Promise} which must resolve once all renames have concluded or reject if any subsequent
 */
function handleRenames (renames, oldNames, renameFile) {
  const remainingRenames = new Map(renames)
  const newNames = new Set(oldNames)
  // Map has only forEach() for the entries, would make the code harder to read.
  for (const oldName of renames.keys()) {
    newNames.delete(oldName)
  }
  for (const newName of renames.values()) {
    newNames.add(newName)
  }
  if (oldNames.size !== newNames.size) {
    throw new Error('Cannot rename multiple files to the same name')
  }

  /**
   * Groups of file names that needs to be process sequentially.
   * @type {Map<Set<string>, Array<[string, string]>>}
   */
  const groups = new Map()
  /**
   * The current operation being processed.
   * @type {?[string, string]}
   */
  let currentRename = null
  /**
   * The group of the current operation. The "names" field is a set of the
   * filenames involved. The "steps" field is the sequence of operations.
   * @type {{
   *   names: Set<string>,
   *   steps: Array<[string, string]>
   * }}
   */
  let currentGroup
  /**
   * The old name of the first operation of the current group. Used to trace
   * operations in reverse order.
   * @type {?string}
   */
  let groupStartName = null
  /**
   * The last number used for a temporary name.
   * @type {number}
   */
  let temporaryNameNumber = 0

  while (remainingRenames.size > 0 || currentRename !== null) {
    // Start new rename group processing.
    if (currentRename === null) {
      currentRename = remainingRenames.entries().next().value
      remainingRenames.delete(currentRename[0])
      currentGroup = {
        names: new Set(),
        steps: []
      }
    }
    const [oldName, newName] = currentRename

    // The group has just been created or we are tracing back with groupStartName.
    if (!currentGroup.names.has(oldName)) {
      currentGroup.names.add(oldName)
      // No operations depends on this one.
      currentGroup.steps.push(currentRename)
      groupStartName = oldName
      // Following down the names, but did not enter a renaming loop.
    } else if (!currentGroup.names.has(newName)) {
      // The current operation is a dependency.
      currentGroup.steps.unshift(currentRename)
      // Entered a renaming loop.
    } else {
      // newName is equal to groupStartName.
      groupStartName = null
      temporaryNameNumber = generateTemporaryName(newNames, oldNames, temporaryNameNumber)
      currentGroup.steps.unshift([oldName, temporaryNameNumber.toString()])
      currentGroup.steps.push([temporaryNameNumber.toString(), newName])
    }

    if (!currentGroup.names.has(newName)) {
      currentGroup.names.add(newName)
    }

    currentRename = remainingRenames.has(newName) ? [newName, remainingRenames.get(newName)] : null
    remainingRenames.delete(newName)

    if (currentRename === null && groupStartName !== null) {
      // Again, only forEach is available and a break is needed.
      for (const rename of remainingRenames) {
        if (rename[1] === groupStartName) {
          currentRename = rename
          remainingRenames.delete(rename[0])
          break
        }
      }
      groupStartName = null
    }
    // Finish rename group processing.
    if (currentRename === null) {
      groups.set(currentGroup.names, currentGroup.steps)
    }
  }

  const promises = []
  // Iterator does not have any other way of iteration.
  for (const group of groups.values()) {
    promises.push(group.reduce((groupPromise, rename) => {
      return groupPromise.then(() => renameFile(...rename))
    }, Promise.resolve()))
  }
  return Promise.all(promises)
}

// In nodejs, this is the way how export is performed.
// In browser, module has to be a global variable object.
module.exports = { handleRenames }
