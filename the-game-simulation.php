<?php
/**
 * Attempting to simulate a way to make this game played upon simple rules
 * @see http://middys.nsv.de/wp-content/uploads/2018/01/the-game-english.pdf
 */

$players = $argv[1] ?? 5;
$players = intval($players);
$hand_size = 0;
if ($players == 1) { $hand_size = 8; }
if ($players == 2) { $hand_size = 7; }
if (($players >= 3) && ($players <= 5)) { $hand_size = 6; }
if ($hand_size == 0) { die('invalid count of players: ' . $players . "\n"); }
echo "players: $players\n";

/* The 4 discard piles, ascending x2, descending x2 */
$discard = [0, 0, 100, 100];
echo "Discard Pile [ax2,dx2]: " . implode(',', $discard) . "\n";

/* The deck */
$deck = [];
for ($c = 2; $c < 100; $c++) {
    $deck[] = $c;
}

/* Shuffling the deck */
$iterations = 1234;
echo "Shuffling deck with $iterations swaps\n";
while ($iterations > 0) {
    $l = mt_rand(0, count($deck) - 1);
    $r = $l;
    while ($r == $l) {
        $r = mt_rand(0, count($deck) - 1);
    }
    $tmp = $deck[$l];
    $deck[$l] = $deck[$r];
    $deck[$r] = $tmp;
    $iterations--;
}

/* Deal the players hands */
$player_hand = [];
$deck_ptr = 0;
for ($p = 1; $p <= $players; $p++) {
    $player_hand[$p] = [];
    for ($d = 0; $d < $hand_size; $d++) {
        $player_hand[$p][] = $deck[$deck_ptr];
        $deck_ptr++;
    }
}
for ($p = 1; $p <= $players; $p++) {
    sort($player_hand[$p]);
    echo "Player $p hand: " . implode(',' , $player_hand[$p]) . "\n";
}

/* Play the game! */
$game_in_session = true;
$player_turn = 1;
$cards_to_play = 2;
while ($game_in_session) {
    /* Player makes decisions. If player can't do what we need $game_in_session = false */
    
    /* First rule: Can we move 10 in any direction */
    $can_jump_10 = false;
    if (in_array(($discard[0] - 10), $player_hand[$player_turn])) { $can_jump_10 = true; }
    if (in_array(($discard[1] - 10), $player_hand[$player_turn])) { $can_jump_10 = true; }
    if (in_array(($discard[2] + 10), $player_hand[$player_turn])) { $can_jump_10 = true; }
    if (in_array(($discard[3] + 10), $player_hand[$player_turn])) { $can_jump_10 = true; }

    /* Second rule: Play the lowest distance card */
    $can_play_card = false;
    $card_distance = 100;
    $card_face = 100;
    $card_index = 0;
    $cnt = 0;
    $discard_affected = 10;
    foreach ($player_hand[$player_turn] as $card) {
        if ($discard[0] < $card) {
            $can_play_card = true;
            if ($card - $discard[0] < $card_distance) {
                $card_distance = $card - $discard[0];
                $card_face = $card;
                $discard_affected = 0;
                $card_index = $cnt;
            }
        }
        if ($discard[1] < $card) {
            $can_play_card = true;
            if ($card - $discard[1] < $card_distance) {
                $card_distance = $card - $discard[1];
                $card_face = $card;
                $discard_affected = 1;
                $card_index = $cnt;
            }
        }
        if ($discard[2] > $card) {
            $can_play_card = true;
            if ($discard[2] - $card < $card_distance) {
                $card_distance = $discard[2] - $card;
                $card_face = $card;
                $discard_affected = 2;
                $card_index = $cnt;
            }
        }
        if ($discard[3] > $card) {
            $can_play_card = true;
            if ($discard[3] - $card < $card_distance) {
                $card_distance = $discard[3] - $card;
                $card_face = $card;
                $discard_affected = 3;
                $card_index = $cnt;
            }
        }
        $cnt++;
    }

    if ($can_jump_10 || $can_play_card) {
        if ($can_jump_10) {
            $card_index = 0;
            if (in_array(($discard[0] - 10), $player_hand[$player_turn])) {
                $card_face = $discard[0] - 10;
                $discard_affected = 0;
            } elseif (in_array(($discard[1] - 10), $player_hand[$player_turn])) {
                $card_face = $discard[1] - 10;
                $discard_affected = 1;
            } elseif (in_array(($discard[2] + 10), $player_hand[$player_turn])) {
                $card_face = $discard[2] + 10;
                $discard_affected = 2;
            } elseif (in_array(($discard[3] + 10), $player_hand[$player_turn])) {
                $card_face = $discard[3] + 10;
                $discard_affected = 3;
            }
            echo "Player $player_turn: backwards trick with $card_face!\n";
            $idx = 0;
            foreach ($player_hand[$player_turn] as $card) {
                if ($card == $card_face) {
                    $card_index = $idx;
                }
                $idx++;
            }
        } elseif ($can_play_card) {
            echo "Player $player_turn is playing $card_face on $discard_affected\n";
        }

        /* remove $card_face from players deck and put it on $discard_affected */
        unset($player_hand[$player_turn][$card_index]);
        sort($player_hand[$player_turn]);
        $discard[$discard_affected] = $card_face;

        $cards_to_play--;
        /* Show deck as it's changed */
        echo "Discard Pile [ax2,dx2]: " . implode(',', $discard) . " [$deck_ptr]\n";
    } else {
        /* Only reason it's a double fail if the person has no cards in their hand */
        if (count($player_hand[$player_turn]) > 0) {
            $game_in_session = false;
        }
    }

    /* Third rule: If we're out of turns can we play a 10 in any for moves 3+ ? */
    if ($cards_to_play == 0) {
        if (in_array(($discard[0] - 10), $player_hand[$player_turn])) { $cards_to_play++; }
        if (in_array(($discard[1] - 10), $player_hand[$player_turn])) { $cards_to_play++; }
        if (in_array(($discard[2] + 10), $player_hand[$player_turn])) { $cards_to_play++; }
        if (in_array(($discard[3] + 10), $player_hand[$player_turn])) { $cards_to_play++; }
    }
    if ($cards_to_play == 0) {
        /* Draw until $hand_size */
        $cards_to_draw = $hand_size - count($player_hand[$player_turn]);
        if ($cards_to_draw > (98 - $deck_ptr)) {
            $cards_to_play = 1;
        } else {
            $cards_to_play = 2;
        }

        while (count($player_hand[$player_turn]) < $hand_size) {
            if ($deck_ptr >= 98) {
                break;
            }
            echo "- Player $player_turn drew " . $deck[$deck_ptr] . "\n";
            $player_hand[$player_turn][] = $deck[$deck_ptr];
            $deck_ptr++;
        }
        sort($player_hand[$player_turn]);

        /* Next player */
        $player_turn++;
        if ($player_turn > $players) {
            $player_turn = 1;
        }
    }
    /* Check if all players have no cards to finally end game */
    if ($game_in_session) {
        $game_in_session = false;
        for ($p = 1; $p <= $players; $p++) {
            if (count($player_hand[$p]) > 0) {
                $game_in_session = true;
            }
        }
    }
}

echo "\n=== GAME OVER ===\n";
/* Endgame */
for ($p = 1; $p <= $players; $p++) {
    echo "Player $p hand: " . implode(',' , $player_hand[$p]) . "\n";
}
echo "Deck position is $deck_ptr / 98\n";
if ($deck_ptr == 98) {
    echo ">> was in endgame\n";
}
