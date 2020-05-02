/*
 * In all cases, try to write short and elegant solution using ES6+ features.
 */

/**
 * Rotate object representing 2D point 90° clockwise (steering right).
 * Do not modify input object.
 * @param {Object} Object with x,y coordinates (and possibly other data)
 */
const rotate2D = ({ x, y, ...rest }) => ({ x: y, y: -x, ...rest }) // needs fixing

/**
 * Flatten properties of all objects in a prototype chain into one object.
 * In case of collisions, the values should respect the prototype chain reading rules
 * (i.e., properties in foremost objects hide properties in objects further in the chain).
 * @param {*} obj Object whose prototype chain is being flattened.
 * @return Newly constructed object containing all the properties.
 */
const flattenProtoChain = (obj) => Object.assign(obj ? flattenProtoChain(Object.getPrototypeOf(obj)) : {}, obj)

/**
 * Implement a reasonable equivalent of Array.map() function for objects.
 * It returns a shallow copy of input object where each value is transformed by given map function.
 * @param {Object} obj Object to be cloned and mapped.
 * @param {Function} map Transformation function applied on all values. Map fnc gets (value, key) as arguments.
 * @return {Object} Transformed cloned object.
 */
const objMap = (obj, map) => Object.entries(obj).reduce((acc, [key, value]) => {
  acc[key] = map(value, key)
  return acc
}, {})

/**
 * Make an array unique. Creates a copy of input array, where all scalar values (transformed to string) of the input
 * are present only once. Nonscalar values (objects, functions, ...) are pruned out.
 * @param {Array} arr Input array
 * @return {Array} Array with unique string values.
 */
const unique = arr => arr.filter(value => ['number', 'boolean', 'string'].includes(typeof value)).map(String).reduce((acc, cur) => {
  acc.includes(cur) ? acc : acc.push(cur)
  return acc
}, [])

/**
 * Create a copy of input array with reverted order of items.
 * In this function, you must not use the Array reverse() method.
 * @param {Array} arr
 * @return {Array}
 */
const reverse = arr => arr.reduceRight((acc, cur) => {
  acc.push(cur)
  return acc
}, [])

/**
 * Flatten array recursively. I.e., all nested scalar items will be listed in the result array.
 * Objects should be converted into arrays of values (keys are ignored).
 * Do not use Array flat() nor flatMap() methods.
 * @param {Array} arr
 * @return {Array}
 */
const flatten = arr => arr.map(value => typeof value === 'object' ? Object.values(value) : value).reduce((acc, cur) => {
  Array.isArray(cur) ? acc.push(...flatten(cur)) : acc.push(cur)
  return acc
}, [])

/**
 * Testing function that can be administred in duck-typing. It tests whether given object has given properties of given type.
 * The object must hold all properties as described in the descriptor to pass the duck type test.
 * It may also hold some other properties, which are not described.
 * Example: object { x: 1, y: 2, z: 3 } passes the duck-type test for descriptor { x: 'number', y: 'number' }.
 * @param {Object} obj Object being tested
 * @param {Object} duck Descriptor of the duck. Keys match the keys of tested object. Each value is either string
 *                      with a type name (result of typeof) or an object which implicates the obj value has to
 *                      be object as well and the nested object is its duck-type descriptor.
 * @return {Boolean}
 */
const duckTypeTest = (obj, duck) => Object.entries(duck).every(([key, type]) => obj.hasOwnProperty(key) && (typeof type === 'string' ? typeof obj[key] === type : duckTypeTest(obj[key], type)))

// In nodejs, this is the way how export is performed.
// In browser, module has to be a global varibale object.
module.exports = {
  rotate2D,
  flattenProtoChain,
  objMap,
  unique,
  reverse,
  flatten,
  duckTypeTest,
}
