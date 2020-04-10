<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>NIM</title>
  <link rel="stylesheet" href="style/main.css" type="text/css">
</head>


<body>
<h1>NIM</h1>
<p>Player and computer take 1-3 matches in turns. Whoever takes the last match, looses.</p>


<!-- initial form (start a game) -->
<?php if ($this->state === 0): ?>
  <form action="?" method="GET">
    <table>
      <tr>
        <td><label>Matches:</label></td>
        <td class="center"><input type="number" min="2" max="50" value="20" name="initial"></td>
      </tr>
      <tr>
        <td colspan="2" class="center">
          <button type="submit">Start</button>
        </td>
      </tr>
    </table>
  </form>
<?php else: ?>

  <!-- game in progress -->

  <div class="center">
      <?php for ($i = 1; $i <= $this->matches; $i++): ?>
          <?php if ($i <= 3 && $i <= $this->matches): ?>
          <a href="?initial=<?= $this->initial ?>&matches=<?= $this->matches - $i ?>&seed=<?= $this->nextSeed ?>" class="match">
          <?php endif; ?>
        <img src="style/match.png" class="match">
          <?php if ($i <= 3 && $i <= $this->matches): ?>
          </a>
          <?php endif; ?>
      <?php endfor; ?>
      <?php for ($i = 1; $i <= $this->taken; $i++): ?>
        <img src="style/match.png" class="match taken">
      <?php endfor; ?>
  </div>
<?php endif; ?>
<?php if ($this->state > 2): ?>

  <!-- when the game is over -->

  <p>There are no matches left...</p>

  <p>
    Game over. The winner is
    <strong><?= ($this->state === 4) ? 'the computer' : 'the user'; ?></strong>!
    <br>
    <a href="?">Play Again</a>
  </p>

<?php endif; ?>

<!-- footer -->

</body>
</html>
