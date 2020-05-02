/**
 * This is old-school (ES5) way how to encapsulate implementations.
 *
 * The game board is a regular 2d grid of fields.
 * Each field may hold multiple properties (attributes).
 * Properties may be set in multiple `set` calls, each call setting the
 * properties to a rectangular area simultaneously.
 * The `set` call also returns a function, which could be used later to alter
 * the properties set in a particular *layer*.
 */
const gameBoard = (function () {

  /*
   * Variables hidden in the closure, but visible from the interface functions.
   */
  var _data = null
  var _width = 0
  var _height = 0

  /**
   * Initialize the game board using given size.
   * @param {number} width
   * @param {number} height
   */
  function init (width, height) {
    _data = []
    for (_width = 0; _width < width; _width++) {
      _data[_width] = []
      for (_height = 0; _height < height; _height++) {
        _data[_width][_height] = {
          x: _width,
          y: _height
        }
      }
    }
  }

  /**
   * Set properties to whole rectangle of fields.
   * @param {number} left
   * @param {number} top
   * @param {number} width
   * @param {number} height
   * @param {object} properties Properties to be set.
   * @return {function} An update function (with one argument -- properties object)
   *    which can later be used to update this particular set of field (in the original layer).
   */
  function setFieldProperties (left, top, width = 1, height = 1, properties = {}) {
    var layer = []
    for (var x = left; x < left + width; x++) {
      for (var y = top; y < top + height; y++) {
        var newCell = Object.create(_data[x][y])
        Object.assign(newCell, properties)
        _data[x][y] = newCell
        layer.push(newCell)
      }
    }
    return function (data) {
      for (var cell of layer) {
        Object.assign(cell, data)
      }
    }
  }

  /**
   * Get all the field properties.
   * @param {number} x
   * @param {number} y
   * @return {object} Object holding all the field properties.
   *    The properties may not be direct members of the object,
   *    but they may be accessible via prototype chain.
   */
  function getFieldProperties (x, y) {
    return _data[x][y]
  }

  // Only methods which should be exposed are returned in the interface object.
  return {
    init,
    getFieldProperties,
    setFieldProperties
  }
})()

// In nodejs, this is the way how export is performed.
// In browser, module has to be a global varibale object.
module.exports = { gameBoard }
