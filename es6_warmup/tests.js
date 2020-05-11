const process = require('process');
const solution = require('./warmup.js');

const createProtoChain = ([head, ...rest]) => head ? Object.assign(Object.create(createProtoChain(rest)), head) : {};

/**
 * Perfom deep cloning of an object/array.
 * @param {*} val Value to be cloned.
 */
const deepClone = val => {
	if (typeof(val) !== 'object') {
		return val;
	}

	const copy = { ...val };
	Object.keys(copy).forEach(key => {
		copy[key] = deepClone(copy[key]);
	})
	return copy;
}

/**
 * Compare two structures deeply using === on regular values and nesting in objects/arrays.
 * @return True if both values are the same.
 */
const deepCompare = (value1, value2) => {
	if (typeof(value1) !== typeof(value2)) {
		return false;
	}

	if (typeof(value1) === 'object') {
		//if (typeof(value2) !== 'object')
		const keys1 = Object.keys(value1).sort();
		const keys2 = Object.keys(value2).sort();
		return keys1.length === keys2.length
			&& keys1.reduce((res, key, idx) => res && key === keys2[idx] && deepCompare(value1[key], value2[key]), true);
	} else if (typeof(value1) === 'number' && isNaN(value1)) {
		return isNaN(value2);
	} else if (typeof(value1) !== 'function') {
		return value1 === value2;
	}
	return true;
}

// Internal counter so we can properly label tests
let testCounter = 0;

const _testInternal = (fncName, input, output, errorMessage = undefined) => {
	++testCounter;
	process.stdout.write(`#${testCounter} Function ${fncName} `);

	const fnc = solution[fncName];
	if (!fnc) {
		console.log(`not found in the solution.`);
		return false;
	}

	try {
		const res = fnc(...input);
		if (typeof(output) === 'function') {
			const evaluation = output(res);
			if (evaluation !== true) {
				console.log(`evaluation failed: ${evaluation}`);
				if (errorMessage) {
					console.log(errorMessage);
				}
				return false;
			}
		} else {
			if (!deepCompare(res, output)) {
				console.log(`evaluation failed: ${JSON.stringify(res)} != ${JSON.stringify(output)}`)
				if (errorMessage) {
					console.log(errorMessage);
				}
				return false;
			}
		}
	}
	catch (error) {
		console.log(`thows error: ${error}`);
		if (errorMessage) {
			console.log(errorMessage);
		}
		return false;
	}

	return true;
}

/**
 * Testing suite. Executes given function for given input and checks output.
 * @param {string} fncName Name of the warmup function to be executed.
 * @param {Array} input All invocation arguments
 * @param {*} output Correct result or function that verifies the correct result.
 * @param {*} errorMessage Additional error message (printed out if error occurs).
 */
const test = (fncName, input, output, errorMessage = undefined) => {
	if (_testInternal(fncName, input, output, errorMessage)) {
		console.log(`OK`);
	}
}

/**
 * Testing suite. Same as test() but also verifies that the input object is not violated.
 * @param {string} fncName Name of the warmup function to be executed.
 * @param {Array} input All invocation arguments
 * @param {*} output Correct result or function that verifies the correct result.
 * @param {*} errorMessage Additional error message (printed out if error occurs).
 */
const testImmutable = (fncName, input, output, errorMessage = undefined) => {
	const inputCopy = deepClone(input);
	if (!_testInternal(fncName, input, output, errorMessage)) return;
	if (!deepCompare(inputCopy, input)) {
		console.log("the input was modified!")
	} else {
		console.log(`OK`);
	}
}


/*
 * Actual tests being executed.
 */
testImmutable('rotate2D', [{ x: 0, y: 0 }], { x: 0, y: 0 });
testImmutable('rotate2D', [{ x: 0, y: 1 }], { x: 1, y: 0 });
testImmutable('rotate2D', [{ x: 1, y: 2 }], { x: 2, y: -1 });
testImmutable('rotate2D', [{ x: 1, y: 2, depth: 4, color: 'red' }], { x: 2, y: -1, depth: 4, color: 'red' });

testImmutable('flattenProtoChain', [{foo: 2, bar: 3}], {foo: 2, bar: 3});
testImmutable('flattenProtoChain',
	[createProtoChain([{foo: 1}, {}, {foo: 2, bar: 3}, {foo:42}, {bar: 4, spam: 5}])],
	{foo: 1, bar: 3, spam: 5});

testImmutable('objMap', [{ foo: 1, bar: 2, spam: 3 }, x => x], { foo: 1, bar: 2, spam: 3 });
testImmutable('objMap', [{ foo: 1, bar: 2, spam: 3 }, x => x+1], { foo: 2, bar: 3, spam: 4 });
testImmutable('objMap', [{ foo: 1, bar: 2, spam: 3 }, (x, key) => x+key.length], { foo: 4, bar: 5, spam: 7 });

testImmutable('unique', [['foo', 42, 'bar', 'spam', 'bar', 42, {}, () => {}, 'foo', 'foo' ]], res => {
	const correct = ['foo', 'bar', 'spam', '42'].sort();
	if (!deepCompare(res.sort(), correct)) {
		return `evaluation failed: ${JSON.stringify(res.sort())} != ${JSON.stringify(correct)}`;
	}
	return true;
});

const _array_reverse_save = Array.prototype.reverse;
Array.prototype.reverse = () => {
	throw new Error("Array reverse() method is not permitted!");
}
testImmutable('reverse', [[]], []);
testImmutable('reverse', [[1]], [1]);
testImmutable('reverse', [[1, 2]], [2, 1]);
testImmutable('reverse', [[1, 2, 3]], [3, 2, 1]);
testImmutable('reverse', [[1, 2, 3, 4]], [4, 3, 2, 1]);
Array.prototype.reverse = _array_reverse_save;


const _array_flatten_save_flat = Array.prototype.flat;
const _array_flatten_save_flatMap = Array.prototype.flatMap;
Array.prototype.flat = () => {
	throw new Error("Array flat() method is not permitted!");
}
Array.prototype.flatMap = () => {
	throw new Error("Array flatMap() method is not permitted!");
}
testImmutable('flatten', [[]], []);
testImmutable('flatten', [['a', 'b']], ['a', 'b']);
testImmutable('flatten', [[['a'], ['b']]], ['a', 'b']);
testImmutable('flatten', [[{foo:'a'}, [{goo:'b'}, [{zoo:'c'}], 'd']]], ['a', 'b', 'c', 'd']);
Array.prototype.flat = _array_flatten_save_flat;
Array.prototype.flatMap = _array_flatten_save_flatMap;

testImmutable('duckTypeTest', [ { x: 1, y: 2 }, { x: 'number', y: 'number' } ], true);
testImmutable('duckTypeTest', [ { x: 1, y: 2, z: 3 }, { x: 'number', y: 'number' } ], true);
testImmutable('duckTypeTest', [ { x: 1, y: 2 }, { x: 'number', y: 'number', z: 'number' } ], false);
testImmutable('duckTypeTest', [ { x: 1, y: 2, color: { r: 0.2, g: 1.0, b: 0.0 } },
	{ x: 'number', y: 'number', color: { r: 'number', g: 'number', b: 'number' } } ], true);
testImmutable('duckTypeTest', [ { name: 'foo', getName: () => this.name }, { name: 'string', getName: 'function' } ], true);
