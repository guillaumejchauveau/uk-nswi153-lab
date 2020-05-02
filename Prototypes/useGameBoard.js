const fs = require('fs')
const { gameBoard } = require('./gameBoard.js')

/*
 * This is an example, how the game board may have been used.
 */

// Board initialization data.
const data = {
  width: 10,
  height: 10,

  // Basic textures and their respective rectangles.
  textures: {
    '..': [0, 0, 10, 10],
    '~~': [2, 0, 2, 10],
    'oo': [8, 8, 2, 2],
    'XX': [0, 6, 3, 1],
  },

  // Positions of holes in the fabric of the universe.
  holes: [
    [2, 2],
    [8, 9],
    [5, 7],
  ]

}

/*
 * Perform the board initialization.
 */
gameBoard.init(data.width, data.height)

var lay1 = gameBoard.setFieldProperties(1, 1, 2, 3, { d: 'A' })
var lay2 = gameBoard.setFieldProperties(2, 2, 2, 2, { d: 'B' })
lay1({ d: 'a' })

for (let y = 0; y < data.height; ++y) {
  const row = []
  for (let x = 0; x < data.width; ++x) {
    row.push(gameBoard.getFieldProperties(x, y).d)
  }
  console.log('|' + row.join('|') + '|')
}
// Basic textures
for (const texture in data.textures) {
  let updateFnc = gameBoard.setFieldProperties(...data.textures[texture])
  if (updateFnc) {
    updateFnc({ texture })
  }
}

// Animated textures
const fire = gameBoard.setFieldProperties(2, 6, 7, 4, { texture: '*&' })

// Holes
data.holes.forEach(hole => {
  let updateFnc = gameBoard.setFieldProperties(...hole)
  if (updateFnc) {
    updateFnc({ texture: '  ' })
  }
})

/*
 * Let's animate the fire texture.
 */
const fireTextures = ['&#', '#^', '^+', '+*', '*&']

printBoard(gameBoard, data.width, data.height)
if (fire) {
  /*setInterval(function () {
    fire({ texture: fireTextures[Math.round(Math.random() * (fireTextures.length - 1))] })
    printBoard(gameBoard, data.width, data.height)
  }, 500)*/
}

/**
 * Helper function which prints the board to the console.
 */
function printBoard (gameBoard, width, height) {
  console.log('\033[2J')
  const sep = ':--'.repeat(width) + ':'
  console.log(sep)

  for (let y = 0; y < height; ++y) {
    const row1 = []
    const row2 = []
    for (let x = 0; x < width; ++x) {
      const field = gameBoard.getFieldProperties(x, y)
      let texture = field.texture.substr(0, 2)
      if (!texture || texture.length !== 2) {
        texture = '??'
      }
      row1.push(texture)
      row2.push(texture)
    }
    console.log('|' + row1.join('|') + '|')
    console.log('|' + row2.join('|') + '|')
    console.log(sep)
  }
}
