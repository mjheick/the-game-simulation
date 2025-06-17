# the-game-simulation

A card game with 97 sequential cards and 4 discards piles. Simulate a simple strategy so that no communication is possible between the players.

http://middys.nsv.de/wp-content/uploads/2018/01/the-game-english.pdf

# Strategy

3 Rules:
- If you can do the Backwards Trick, do it as your first move.
- Play a card from your hand with the least impact on the discards.
- After your second move (first on empty deck) if you can do the Backwards Trick repetitively do that.

# Execution

```php -f the-game-simulation [players]```

Defaults to 5 players where this simulation seems to get well into the endgame.
